<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceDepartment;
use Nwidart\Modules\Facades\Module;

/**
 * @class CitizenServiceDepartmentController
 * @brief Controlador para los departamentos de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para los departamentos de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceDepartmentController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:citizenservice.departaments.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.departaments.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.departaments.delete', ['only' => ['destroy']]);


        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'           => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description'    => ['nullable', 'max:200'],
            'director_id'    => [Module::has('Payroll') && Module::isEnabled('Payroll') ? 'required' : 'nullable'],
            'coordinator_id' => ['nullable'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.max'              => 'El campo nombre no debe contener más de 100 caracteres.',
            'name.regex'            => 'El campo nombre no debe permitir números ni símbolos.',
            'description.max'       => 'El campo descripción no debe contener más de 100 caracteres.',
            'director_id.required'  => 'El campo director es obligatorio.',
        ];
    }

    /**
     * Obtiene el listado de departamentos de la OAC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'records' => CitizenServiceDepartment::with('departmentDirector', 'departmentCoordinator')->get()
        ], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo departamento
     *
     * @return Renderable
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Almacena información del nuevo departamento de la OAC
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        //Guardar los registros del formulario en  CitizenServiceDepartment
        $citizenServiceDepartment = CitizenServiceDepartment::create([
            'name'             => $request->input('name'),
            'description'      => $request->input('description'),
            'director_id'      => $request->input('director_id'),
            'coordinator_id'   => $request->input('coordinator_id'),
        ]);

        return response()->json(['record' => $citizenServiceDepartment, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles del departamento de la OAC
     *
     * @return Renderable
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar el departamento de la OAC
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza la información del departamento de la OAC
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceDepartment = CitizenServiceDepartment::find($id);
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100' . $citizenServiceDepartment->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        $citizenServiceDepartment->name             = $request->name;
        $citizenServiceDepartment->description      = $request->description;
        $citizenServiceDepartment->director_id      = $request->director_id;
        $citizenServiceDepartment->coordinator_id   = $request->coordinator_id;

        $citizenServiceDepartment->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el departamento de la OAC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $citizenServiceDepartment = CitizenServiceDepartment::find($id);
        $citizenServiceDepartment->delete();
        return response()->json(['record' => $citizenServiceDepartment, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de departamentos de la OAC
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartments()
    {
        $departmentList = CitizenServiceDepartment::all();
        $departments = [];
        array_push($departments, [
            'id' => '',
            'text' => 'Seleccione...',
            'director_id' => ''
        ]);
        foreach ($departmentList->all() as $department) {
            array_push($departments, [
                'id' => $department->id,
                'text' => $department->name,
                'director_id' => $department->director_id
            ]);
        }
        return response()->json($departments);
    }
}
