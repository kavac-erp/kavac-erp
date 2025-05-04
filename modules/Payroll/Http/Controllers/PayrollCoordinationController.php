<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollCoordination;
use App\Models\Department;

/**
 * @class PayrollCoordinationController *
 * @brief Gestión de los datos registrados de las Coordinaciones.
 *
 * Clase que gestiona las Coordinaciones.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCoordinationController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de datos a mostrar en selectores
     *
     * @var array $data
     */
    protected $data = [];

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
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];

        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.coordinations.index', ['only' => 'index']);
        $this->middleware('permission:payroll.coordinations.store', ['only' => 'store']);
        $this->middleware('permission:payroll.coordinations.update', ['only' => 'update']);
        $this->middleware('permission:payroll.coordinations.destroy', ['only' => 'destroy']);
    }

    /**
     * Devuelve un listado de los registros almacenados.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos de las coordinaciones.
     */
    public function index()
    {
        return response()->json([
            'records' => PayrollCoordination::orderBy('id')->get()
        ], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * Valida y registra una nueva coordinación.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Solicitud con los datos a guardar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'max:200',
                    'unique:payroll_coordinations',
                    'regex:/^[a-zA-ZáéíóúÁÉÍÓÚüÜ\s]+$/u'
                ],
                'description' => [
                    'nullable',
                    'max:200'
                ],
                'department_id' => ['required'],
            ],
            [
                'department_id.required' => 'El campo departamento de adscripción es obligatorio.',
                'name.regex' => 'El campo nombre no debe contener caracteres especiales.',
            ]
        );

        $payrollCoordination = PayrollCoordination::create([
            'name' => $request->name,
            'description' => $request->description,
            'department_id' => $request->department_id,
        ]);

        return response()->json([
            'record' => $payrollCoordination,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Muestra la vista para detallar un registro.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('payroll::show');
    }

    /**
     * Muestra el formulario para actualizar un registro.
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('payroll::edit');
    }

    /**
     * Actualiza la información de la coordinación según su ID.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Solicitud con los datos a actualizar.
     * @param  integer $id Identificador de la coordinación a actualizar.
     *
     * @return \Illuminate\Http\JsonResponse Json con mensaje de confirmación de la operación.
     */
    public function update(Request $request, $id)
    {
        $payrollCoordination = PayrollCoordination::find($id);
        $this->validate(
            $request,
            [
                'name' => [
                    'required',
                    'max:200',
                    'unique:payroll_coordinations,name,' . $id,
                    'regex:/^[a-zA-Z\s]+$/',
                ],
                'description' => [
                    'nullable',
                    'max:200'
                ],
                'department_id' => ['required'],
            ],
            [
                'department_id.required' => 'El campo departamento de adscripción es obligatorio.',
                'name.regex' => 'El campo nombre no debe contener caracteres especiales.',
            ]
        );
        $payrollCoordination->name  = $request->name;
        $payrollCoordination->description = $request->description;
        $payrollCoordination->department_id = $request->department_id;
        $payrollCoordination->save();
        return response()->json([
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina una coordinación registrada.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  integer $id Identificador de la coordinación a eliminar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $payrollCoordination = PayrollCoordination::find($id);

        $payrollCoordination->delete();

        return response()->json([
            'record' => $payrollCoordination,
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene los datos de las Unidades y Dependencias.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con listado de los
     * de las Unidades y Dependencias.
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

    /**
     * Obtiene las coordinaciones registradas.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Json con los datos las coordinaciones
     */
    public function getPayrollCoordinations()
    {
        return response()->json(template_choices(
            'Modules\Payroll\Models\PayrollCoordination',
            'name',
            '',
            true
        ));
    }
}
