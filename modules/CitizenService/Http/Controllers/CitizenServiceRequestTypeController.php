<?php

namespace Modules\CitizenService\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequestType;

/**
 * @class CitizenServiceRequestTypeController
 * @brief Controlador tipos de solicitudes de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador de tipos de solicitudes de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestTypeController extends Controller
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
        $this->middleware('permission:citizenservice.request.types.create', ['only' => ['index', 'create', 'store']]);
        $this->middleware('permission:citizenservice.request.types.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:citizenservice.request.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'        => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100'],
            'description' => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:200'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'         => 'El campo nombre es obligatorio.',
            'name.max'              => 'El campo nombre no debe contener más de 100 caracteres.',
            'name.regex'            => 'El campo nombre no debe permitir números ni símbolos.',
            'description.required'  => 'El campo descripción es obligatorio',
            'description.max'       => 'El campo descripción no debe contener más de 100 caracteres.',
            'description.regex'     => 'El campo descripción no debe permitir números ni símbolos.',
        ];
    }
    /**
     * Define la configuración de la clase
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => CitizenServiceRequestType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de solicitud
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('citizenservice::create');
    }

    /**
     * Valida y registra un nuevo tipo de solicitud
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        //Guardar los registros del formulario en  CitizenServiceRequestType
        $citizenserviceRequestType = CitizenServiceRequestType::create([

            'name'          => $request->input('name'),
            'description'   => $request->input('description'),
        ]);

        return response()->json(['record' => $citizenserviceRequestType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de un tipo de solicitud
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('citizenservice::show');
    }

    /**
     * Muestra el formulario para editar la información de un tipo de solicitud
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza la información del tipo de solicitud
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param  Request $request datos de la petición
     * @param  integer $id      identificador del tipo de solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenserviceRequestType = CitizenServiceRequestType::find($id);
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'regex:/^[\D][a-zA-ZÁ-ÿ0-9\s]*/u', 'max:100' . $citizenserviceRequestType->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        $citizenserviceRequestType->name          = $request->name;
        $citizenserviceRequestType->description   = $request->description;
        $citizenserviceRequestType->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de solicitud
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param  integer $id identificador del tipo de solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $citizenserviceRequestType = CitizenServiceRequestType::find($id);
        $citizenserviceRequestType->delete();
        return response()->json(['record' => $citizenserviceRequestType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de tipos de solicitud
     *
     * @return array
     */
    public function getRequestTypes()
    {
        return template_choices('Modules\CitizenService\Models\CitizenServiceRequestType', 'name', [], true, null);
    }
}
