<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingPriority;

/**
 * @class PositionController
 * @brief Controlador de prioridad
 *
 * Clase que gestiona las prioridades en el seguimiento de proyectos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingPriorityController extends Controller
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
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required'],
            'description'                           => ['nullable', 'max:250'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                                  => 'El campo nombre es obligatorio.',
        ];
        /* Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:projecttracking.positions.list', ['only' => 'index']);
        $this->middleware('permission:projecttracking.positions.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:projecttracking.positions.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:projecttracking.positions.delete', ['only' => 'destroy']);
         */
    }

    /**
     * Muestra todos los registros de prioridades
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingPriority::all()], 200);
    }

    /**
     * Muestra el formulario para un nuevo registro de prioridad
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Retorna un json con todas las prioridades registradas para ser usado en un componente <select2>
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getPriorities()
    {
        $prioritiesList = ProjectTrackingPriority::all();
        $priorities = [];
        array_push($priorities, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($prioritiesList->all() as $priority) {
            array_push($priorities, [
                'id' => $priority->id,
                'text' => $priority->name
            ]);
        }
        return response()->json($priorities);
    }

    /**
     * Valida y registra una nueva prioridad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:project_tracking_priorities,name']
        ]);
        $projecttrackingPriority = ProjectTrackingPriority::create(['name' => $request->name, 'description' => $request->description]);
        return response()->json(['record' => $projecttrackingPriority, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de una prioridad
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario para la actualización de la prioridad
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la información de la prioridad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador de la prioridad a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $projecttrackingPriority = ProjectTrackingPriority::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:project_tracking_priorities,name,' . $projecttrackingPriority->id],
            'description' => ['nullable', 'max:200']
        ]);
        $projecttrackingPriority->name  = $request->name;
        $projecttrackingPriority->description = $request->description;
        $projecttrackingPriority->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la prioridad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador de la prioridad a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $projecttrackingPriority = ProjectTrackingPriority::find($id);
        $projecttrackingPriority->delete();
        return response()->json(['record' => $projecttrackingPriority, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las prioridades registradas
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de prioridades
     */
    public function getProjectTrackingPriorities()
    {
        return response()->json(template_choices('Modules\ProjectTracking\Models\ProjectTrackingPriority', 'name', '', true));
    }
}
