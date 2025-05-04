<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollExceptionType;
use Modules\Payroll\Models\PayrollParameterTimeSheetParameter;
use Modules\Payroll\Models\PayrollPaymentTypeTimeSheetParameter;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollTimeSheetParameter;
use Modules\Payroll\Models\PayrollTimeSheetPending;

/**
 * @class PayrollTimeSheetParameterController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetParameterController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.timesheetparameter.index', ['only' => 'index']);
        $this->middleware('permission:payroll.timesheetparameter.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.timesheetparameter.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.timesheetparameter.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'code' => ['required', 'unique:payroll_time_sheet_parameters,code'],
            'name' => ['required'],
            'time_parameters' => ['required'],
            'time_parameters.*.id' => ['exists:parameters,id'],
            'payment_types' => ['required'],
            'payment_types.*.id' => ['exists:payroll_payment_types,id']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required'        => 'El campo código es obligatorio.',
            'code.unique'          => 'El campo código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio.',
            'time_parameters.required' => 'El campo parámetros es obligatorio.',
            'time_parameters.*.id.exists' => 'El campo parámetros no existe.',
            'payment_types.required' => 'El campo tipos de nómina es obligatorio.',
            'payment_types.*.id.exists' => 'El campo tipos de nómina no existe.'
        ];
    }

    /**
     * Muestra todos los registros de parámetros de tiempo
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PayrollTimeSheetParameter::query()
            ->whereHas('payrollParameterTimeSheetParameters.parameter')
            ->whereHas('payrollPaymentTypeTimeSheetParameters.payrollPaymentType')
            ->with([
                'payrollParameterTimeSheetParameters.parameter',
                'payrollPaymentTypeTimeSheetParameters.payrollPaymentType'
            ])
            ->get()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de parámetro de tiempo
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo parámetro de tiempo
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $payrollTimeSheetParameter = DB::transaction(function () use ($request) {
            $payrollTimeSheetParameter = PayrollTimeSheetParameter::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description
            ]);

            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }

            return $payrollTimeSheetParameter;
        });

        return response()->json(['record' => $payrollTimeSheetParameter, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para editar un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza los datos de un parámetro de tiempo
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);
        $this->validateRules['code'] = [
            'required',
            'unique:payroll_time_sheet_parameters,code,' . $payrollTimeSheetParameter->id
        ];
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheetParameter) {
            $payrollTimeSheetParameter->code = $request->code;
            $payrollTimeSheetParameter->name = $request->name;
            $payrollTimeSheetParameter->description = $request->description;
            $payrollTimeSheetParameter->save();

            PayrollParameterTimeSheetParameter::query()
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->delete();

            PayrollPaymentTypeTimeSheetParameter::query()
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->delete();

            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }
        });
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un parámetro de tiempo
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $timeSheet = PayrollTimeSheet::query()
            ->where('payroll_time_sheet_parameter_id', $id)
            ->first();

        $timeSheetPending = PayrollTimeSheetPending::query()
            ->where('payroll_time_sheet_parameter_id', $id)
            ->first();

        if ($timeSheet || $timeSheetPending) {
            return response()->json(['error' => true, 'message' => __('No se puede eliminar los parámetros de hoja' .
                ' de tiempo debido a que tiene una hoja de tiempo asociada')], 200);
        }

        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);

        $parameters = PayrollParameterTimeSheetParameter::query()
            ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
            ->get();

        foreach ($parameters as $parameter) {
            $parameter->delete();
        }

        $paymentTypes = PayrollPaymentTypeTimeSheetParameter::query()
            ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
            ->get();

        foreach ($paymentTypes as $paymentType) {
            $paymentType->delete();
        }

        $payrollTimeSheetParameter->delete();

        return response()->json(['record' => $payrollTimeSheetParameter, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los parámetros de la hoja de tiempo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayrollTimeSheetParameters()
    {
        $parameters = PayrollTimeSheetParameter::query()
            ->with('payrollParameterTimeSheetParameters.parameter')
            ->get();

        $records = [];

        foreach ($parameters as $key => $parameter) {
            $records[$key] = [
                'id' => $parameter->id,
                'text' => $parameter->code,
                'parameters' => []
            ];

            foreach ($parameter->payrollParameterTimeSheetParameters as $param) {
                $pValue = json_decode($param->parameter->p_value);
                $exceptionType = PayrollExceptionType::find($pValue->exception_type);

                $records[$key]['parameters'][$exceptionType->name][] = [
                    'id' => $param->parameter->id,
                    'group' => $exceptionType->name,
                    'max' => $exceptionType->value_max ?? null,
                    'affectGroup' => $exceptionType->affect?->name,
                    'text' => $pValue->acronym . ' - ' . $pValue->name,
                ];
            }
        }

        $data = array_merge(
            [
                [
                    'id' => '',
                    'text' => 'Seleccione...',
                ]
            ],
            $records
        );

        return response()->json(
            $data,
            200
        );
    }
}
