<?php

namespace Modules\Payroll\Http\Controllers;

use App\Rules\DateBeforeFiscalYear;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollArcResponsible;

/**
 * @class PayrollArcResponsibleController
 *
 * @brief Gestión de los datos registrados de los Responsables de ARC.
 *
 * Clase que gestiona los Responsables de ARC.
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollArcResponsibleController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct(
        protected array $validateRules = [],
        protected array $messages = [],
    ) {
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.arc.responsibles.index', ['only' => 'index']);
        $this->middleware('permission:payroll.arc.responsibles.store', ['only' => 'store']);
        $this->middleware('permission:payroll.arc.responsibles.update', ['only' => 'update']);
        $this->middleware('permission:payroll.arc.responsibles.destroy', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'payroll_staff_id' => ['required'],
            'start_date' => ['required', 'date', new DateBeforeFiscalYear('Desde')],
            'end_date' => ['nullable', 'date', 'after:start_date', new DateBeforeFiscalYear('Hasta')],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'start_date.required' => 'El campo Desde es requerido',
            'end_date.after' => 'La fecha Hasta debe ser mayor a la fecha Desde',
            'payroll_staff_id.required' => 'El campo Trabajador es requerido',
        ];
    }

    /**
     * Devuelve un listado de los registros almacenados.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de las responsabilidades.
     */
    public function index()
    {
        $data = PayrollArcResponsible::query()
            ->with(['payrollStaff' => function ($query) {
                $query->without([
                        'payrollNationality',
                        'payrollFinancial',
                        'payrollGender',
                        'payrollBloodType',
                        'payrollDisability',
                        'payrollLicenseDegree',
                        'payrollEmployment',
                        'payrollStaffUniformSize',
                        'payrollSocioeconomic',
                        'payrollProfessional',
                        'payrollResponsibility'
                    ]);
            }])
            ->orderBy('start_date')->get();

        return response()->json(['records' => $data], 200);
    }

    /**
     * Valida y registra una nueva responsabilidad.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Solicitud con los datos a guardar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $payrollLastArcResponsible = PayrollArcResponsible::query()
            ->orderBy('end_date')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastArcResponsible)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'start_date' => [
                        'required',
                        'date',
                        'after:' . (!empty($payrollLastArcResponsible?->end_date) ? $payrollLastArcResponsible->end_date : $payrollLastArcResponsible->start_date),
                        new DateBeforeFiscalYear('Desde'),
                        function ($attribute, $value, $fail) use ($request) {
                            $overlappingRecords = PayrollArcResponsible::query()
                                ->where('end_date', '>', $value)
                                ->when(!empty($request->end_date), function ($query) use ($request) {
                                    $query->where('start_date', '<', $request->end_date);
                                })
                                ->exists();

                            if ($overlappingRecords) {
                                $fail('La fecha Desde se encuentra dentro del período de un registro anterior.');
                            }
                        }
                    ],
                    'end_date' => [
                        'nullable',
                        'date',
                        'after:start_date',
                        new DateBeforeFiscalYear('Hasta'),
                        function ($attribute, $value, $fail) use ($request) {
                            $overlappingRecords = PayrollArcResponsible::query()
                                ->where('end_date', '>', $request->start_date)
                                ->where('start_date', '<', $value)
                                ->exists();

                            if ($overlappingRecords) {
                                $fail('La fecha Hasta se encuentra dentro del período de un registro anterior.');
                            }
                        }
                    ],
                ]
            );
            $messages = array_merge(
                $messages,
                [
                    'start_date.after' => 'La fecha Desde debe ser mayor a ' . Carbon::createFromFormat('Y-m-d', (!empty($payrollLastArcResponsible?->end_date) ? $payrollLastArcResponsible->end_date : $payrollLastArcResponsible->start_date))->format('d-m-Y'),
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        if (isset($payrollLastArcResponsible) && is_null($payrollLastArcResponsible->end_date)) {
            $payrollLastArcResponsible->end_date = $request->start_date;
            $payrollLastArcResponsible->save();
        }

        $payrollArcResponsible = PayrollArcResponsible::create([
            'payroll_staff_id' => $request->payroll_staff_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'record' => $payrollArcResponsible,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Actualiza la información del registro.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Solicitud con los datos a actualizar.     *
     * @param  PayrollArcResponsible $payrollArcResponsible Registro a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación.
     */
    public function update(Request $request, PayrollArcResponsible $payrollArcResponsible)
    {
        $payrollLastArcResponsible = PayrollArcResponsible::query()
            ->where('id', '<>', $payrollArcResponsible->id)
            ->orderBy('end_date')
            ?->get()
            ?->last();

        $validateRules  = $this->validateRules;
        $messages  = $this->messages;

        if (isset($payrollLastArcResponsible)) {
            $validateRules  = array_replace(
                $validateRules,
                [
                    'start_date' => [
                        'required',
                        'date',
                        new DateBeforeFiscalYear('Desde'),
                        function ($attribute, $value, $fail) use ($request, $payrollArcResponsible) {
                            $overlappingRecords = PayrollArcResponsible::query()
                                ->where('id', '<>', $payrollArcResponsible->id)
                                ->where('end_date', '>', $value)
                                ->when(!empty($request->end_date), function ($query) use ($request) {
                                    $query->where('start_date', '<', $request->end_date);
                                })
                                ->exists();

                            if ($overlappingRecords) {
                                $fail('La fecha Desde se encuentra dentro del período de un registro anterior.');
                            }
                        }
                    ],
                    'end_date' => [
                        'nullable',
                        'date',
                        'after:start_date',
                        new DateBeforeFiscalYear('Hasta'),
                        function ($attribute, $value, $fail) use ($request, $payrollArcResponsible) {
                            $overlappingRecords = PayrollArcResponsible::query()
                                ->where('id', '<>', $payrollArcResponsible->id)
                                ->where('end_date', '>', $request->start_date)
                                ->where('start_date', '<', $value)
                                ->exists();

                            if ($overlappingRecords) {
                                $fail('La fecha Hasta se encuentra dentro del período de un registro anterior.');
                            }
                        }
                    ],
                ]
            );
            $messages = array_merge(
                $messages,
                [
                    'end_date.after' => 'La fecha Hasta debe ser mayor a la fecha Desde',
                ]
            );
        }

        $this->validate($request, $validateRules, $messages);

        $payrollArcResponsible->update([
            'payroll_staff_id' => $request->payroll_staff_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una responsabilidad registrada.
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  PayrollArcResponsible $payrollArcResponsible Registro a eliminar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PayrollArcResponsible $payrollArcResponsible)
    {
        $payrollArcResponsible->forceDelete();

        return response()->json([
            'record' => $payrollArcResponsible,
            'message' => 'Success'
        ], 200);
    }
}
