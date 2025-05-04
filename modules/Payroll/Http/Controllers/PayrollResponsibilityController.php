<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollResponsibility;
use App\Models\Department;

/**
 * @class PayrollResponsibilityController *
 * @brief Gestión de los datos registrados de las Responsabilidades.
 *
 * Clase que gestiona las Responsabilidades.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollResponsibilityController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de opciones para selects
     *
     * @var array $data
     */
    protected $data;

    /**
     * Define la configuración de la clase.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Primer registro para los selects. */
        $this->data[] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];

        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.responsibilities.index', ['only' => 'index']);
        $this->middleware('permission:payroll.responsibilities.store', ['only' => 'store']);
        $this->middleware('permission:payroll.responsibilities.update', ['only' => 'update']);
        $this->middleware('permission:payroll.responsibilities.destroy', ['only' => 'destroy']);
    }

    /**
     * Devuelve un listado de los registros almacenados.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de las responsabilidades.
     */
    public function index()
    {
        return response()->json([
            'records' => PayrollResponsibility::orderBy('id')->get()
        ], 200);
    }

    /**
     * Valida y registra una nueva responsabilidad.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Solicitud con los datos a guardar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->type_responsibility) {
            $this->validate(
                $request,
                [
                    'payroll_coordination_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                    'payroll_staff_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                    'payroll_position_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                ],
                [
                    'payroll_coordination_id.required' => 'El campo coordinación es obligatorio.',
                    'payroll_coordination_id.unique' => 'El campo coordinación ya ha sido registrado.',
                    'payroll_staff_id.required' => 'El campo responsable es obligatorio.',
                    'payroll_staff_id.unique' => 'El campo responsable ya ha sido registrado.',
                    'payroll_position_id.required' => 'El campo cargo es obligatorio.',
                    'payroll_position_id.unique' => 'El campo cargo ya ha sido registrado.',
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'department_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                    'payroll_staff_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                    'payroll_position_id' => [
                        'required',
                        'unique:payroll_responsibilities'
                    ],
                ],
                [
                    'department_id.required' => 'El campo departamento es obligatorio.',
                    'department_id.unique' => 'El campo departamento ya ha sido registrado.',
                    'payroll_staff_id.required' => 'El campo responsable es obligatorio.',
                    'payroll_staff_id.unique' => 'El campo responsable ya ha sido registrado.',
                    'payroll_position_id.required' => 'El campo cargo es obligatorio.',
                    'payroll_position_id.unique' => 'El campo cargo ya ha sido registrado.',
                ]
            );
        }

        $payrollResponsibility = PayrollResponsibility::create([
            'department_id' => $request->department_id,
            'payroll_staff_id' => $request->payroll_staff_id,
            'payroll_position_id' => $request->payroll_position_id,
            'payroll_coordination_id' => $request->payroll_coordination_id,
            'type_responsibility' => $request->type_responsibility,
        ]);

        return response()->json([
            'record' => $payrollResponsibility,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Actualiza la información de la responsabilidad según su ID.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Solicitud con los datos a actualizar.     *
     * @param  integer $id Identificador de la responsabilidad a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación.
     */
    public function update(Request $request, $id)
    {
        $payrollResponsibility = PayrollResponsibility::find($id);

        if ($request->type_responsibility) {
            $this->validate(
                $request,
                [
                    'payroll_coordination_id' => [
                        'required',
                        'unique:payroll_responsibilities,payroll_coordination_id,' . $id
                    ],
                    'payroll_staff_id' => [
                        'required',
                        'unique:payroll_responsibilities,payroll_staff_id,' . $id
                    ],
                    'payroll_position_id' => [
                        'required',
                        'unique:payroll_responsibilities,payroll_position_id,' . $id
                    ],
                ],
                [
                    'payroll_coordination_id.required' => 'El campo coordinación es obligatorio.',
                    'payroll_coordination_id.unique' => 'El campo coordinación ya ha sido registrado.',
                    'payroll_staff_id.required' => 'El campo responsable es obligatorio.',
                    'payroll_staff_id.unique' => 'El campo responsable ya ha sido registrado.',
                    'payroll_position_id.required' => 'El campo cargo es obligatorio.',
                    'payroll_position_id.unique' => 'El campo cargo ya ha sido registrado.',
                ]
            );
        } else {
            $this->validate(
                $request,
                [
                    'department_id' => [
                        'required',
                        'unique:payroll_responsibilities,department_id,' . $id
                    ],
                    'payroll_staff_id' => [
                        'required',
                        'unique:payroll_responsibilities,payroll_staff_id,' . $id
                    ],
                    'payroll_position_id' => [
                        'required',
                        'unique:payroll_responsibilities,payroll_position_id,' . $id
                    ],
                ],
                [
                    'department_id.required' => 'El campo departamento es obligatorio.',
                    'department_id.unique' => 'El campo departamento ya ha sido registrado.',
                    'payroll_staff_id.required' => 'El campo responsable es obligatorio.',
                    'payroll_staff_id.unique' => 'El campo responsable ya ha sido registrado.',
                    'payroll_position_id.required' => 'El campo cargo es obligatorio.',
                    'payroll_position_id.unique' => 'El campo cargo ya ha sido registrado.',
                ]
            );
        }

        if ($request->type_responsibility) {
            $payrollResponsibility->department_id = null;
            $payrollResponsibility->payroll_staff_id = $request->payroll_staff_id;
            $payrollResponsibility->payroll_position_id = $request->payroll_position_id;
            $payrollResponsibility->payroll_coordination_id = $request->payroll_coordination_id;
            $payrollResponsibility->type_responsibility = $request->type_responsibility;
            $payrollResponsibility->save();
        } else {
            $payrollResponsibility->department_id = $request->department_id;
            $payrollResponsibility->payroll_staff_id = $request->payroll_staff_id;
            $payrollResponsibility->payroll_position_id = $request->payroll_position_id;
            $payrollResponsibility->payroll_coordination_id = null;
            $payrollResponsibility->type_responsibility = $request->type_responsibility;
            $payrollResponsibility->save();
        }

        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una responsabilidad registrada.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  integer $id Identificador de la responsabilidad a eliminar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollResponsibility = PayrollResponsibility::find($id);

        $payrollResponsibility->forceDelete();

        return response()->json([
            'record' => $payrollResponsibility,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene los datos de las Unidades y Dependencias.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de las Unidades y Dependencias.
     */
    public function getDepartments()
    {
        foreach (Department::all() as $x) {
            $this->data[] = [
                'id' => $x->id,
                'text' => $x->name
            ];
        }
        return response()->json($this->data);
    }
}
