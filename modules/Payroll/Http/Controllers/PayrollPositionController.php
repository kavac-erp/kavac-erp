<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollPosition;
use Modules\Payroll\Models\PayrollEmployment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * @class PositionController *
 * @brief Controlador de cargos
 *
 * Clase que gestiona los cargos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPositionController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        /*$this->middleware('permission:payroll.positions.list', ['only' => 'index']);*/
        $this->middleware('permission:payroll.positions.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.positions.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.positions.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de cargos.
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de los cargos.
     */
    public function index()
    {
        $records = PayrollPosition::withCount(['payrollEmployments' => function ($query) {
            $query->where('payroll_employment_payroll_position.active', true);
        }])->withCount('payrollResponsibility')->orderBy('name', 'asc')->get();

        return response()->json([
            'records' => $records
        ], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo cargo
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra un nuevo cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        if ($request->responsible) {
            $this->validate(
                $request,
                [
                    'name' => [
                        'required',
                        'max:100',
                        'unique:payroll_positions'
                    ],
                    'description' => [
                        'nullable',
                        'max:200'
                    ],
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'name' => [
                        'required',
                        'max:100',
                        'unique:payroll_positions'
                    ],
                    'description' => [
                        'nullable',
                        'max:200'
                    ],
                    'number_positions_assigned' => [
                        'required',
                    ]
                ],
                [
                    'number_positions_assigned.required' => 'El campo cantidad de cargos asignados es obligatorio.',
                ]
            );
        }

        $payrollPosition = PayrollPosition::create([
            'name' => $request->name,
            'description' => $request->description,
            'number_positions_assigned' => $request->responsible ? 1 : $request->number_positions_assigned,
            'responsible' => $request->responsible
        ]);

        return response()->json([
            'record' => $payrollPosition,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Muestra información de un cargo
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar la información de un cargo
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información del cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar     *
     * @param  integer $id Identificador del cargo a actualizar
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $payrollPosition = PayrollPosition::find($id);

        /* Consultar la tabla intermedia para obtener el número de empleados
        asociados al cargo.
        */
        $currentEmployees = DB::table('payroll_employment_payroll_position')
            ->where('payroll_position_id', $payrollPosition->id)
            ->where('active', true)
            ->count();

        /* Se valida que la cantidad de cargos asignados no pueda ser
        menor que el número de empleados asociados al cargo. */
        if ($request->number_positions_assigned < $currentEmployees) {
            $positionAvailableValidation = true;
            $this->validate(
                $request,
                [
                    'name' => [
                        $positionAvailableValidation ? function ($attribute, $value, $fail) {
                            if ($value) {
                                $fail('
                                    La cantidad de cargos asignados no puede ser
                                    menor que el número de empleados asociados
                                    al cargo.
                                ');
                            }
                        } : [],
                    ],
                ],
            );
        }

        if ($request->responsible) {
            $this->validate(
                $request,
                [
                    'name' => [
                        'required',
                        'max:100',
                    ],
                    'description' => [
                        'nullable',
                        'max:200'
                    ],
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'name' => [
                        'required',
                        'max:100',
                    ],
                    'description' => [
                        'nullable',
                        'max:200'
                    ],
                    'number_positions_assigned' => [
                        'required',
                    ]
                ],
                [
                    'number_positions_assigned.required'
                    => 'El campo cantidad de cargos asignados es obligatorio.',
                ]
            );
        }

        $payrollPosition->name = $request->name;
        $payrollPosition->description = $request->description;
        $payrollPosition->number_positions_assigned = $request->responsible
            ? 1 : $request->number_positions_assigned;
        $payrollPosition->responsible = $request->responsible;
        $payrollPosition->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id Identificador del cargo a eliminar
     *
     * @return \Illuminate\Http\JsonResponse Json: objeto eliminado y mensaje
     * de confirmación de la operación.
     */
    public function destroy($id)
    {
        $payrollPosition = PayrollPosition::find($id);
        $payrollPosition->delete();
        return response()->json([
            'record' => $payrollPosition,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene los cargo registrados
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de cargos
     */
    public function getPayrollPositions()
    {
        return response()->json(template_choices(
            'Modules\Payroll\Models\PayrollPosition',
            'name',
            '',
            true
        ));
    }

    /**
     * Obtener el conteo de la cantidad de empleados asociados a un cargo.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de los cargos.
     */
    public function getPayrollEmploymentsPositionsCount()
    {
        /*
         | Obtener todos los cargos de la tabla payroll_positions y para cada
         | cargo contar la cantidad de empleados relacionados a cada cargo.
         */
        $payrollPositions = PayrollPosition::selectRaw('SUM(number_positions_assigned) as positions_count')
            ->value('positions_count');

        $totalEmploymentCount = PayrollPosition::withCount("payrollEmployments")->where("responsible", false)->get()->sum("payroll_employments_count");

        return response()->json([
            'totalPayrollPositions'     => $payrollPositions,
            'totalEmploymentCount'      => $totalEmploymentCount,
            'totalAvailablePositions'   => $payrollPositions - $totalEmploymentCount
        ], 200);
    }
}
