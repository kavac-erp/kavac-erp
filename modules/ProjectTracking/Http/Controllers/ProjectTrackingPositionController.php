<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingPosition;

/**
 * @class PositionController
 * @brief Controlador de cargos
 *
 * Clase que gestiona los cargos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingPositionController extends Controller
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
            'description'                           => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                                  => 'El campo nombre es obligatorio.',
            'description.required'                           => 'El campo descripción es obligatorio.',
        ];

        /* Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:projecttracking.positions.list', ['only' => 'index']);
        $this->middleware('permission:projecttracking.positions.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:projecttracking.positions.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:projecttracking.positions.delete', ['only' => 'destroy']);
         */
    }

    /**
     * Muestra todos los registros de cargos
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function index()
    {
        $positionList = ProjectTrackingPosition::all();
        return response()->json(['records' => $positionList]);
    }

    /**
     * Retorna un json con todos los cargos para ser usado en un componente <select2>
     *
     * @author  Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function getPositions()
    {
        $positionList = ProjectTrackingPosition::all();
        $positions = [];
        array_push($positions, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($positionList->all() as $position) {
            array_push($positions, [
                'id' => $position->id,
                'text' => $position->name
            ]);
        }
        return response()->json($positions);
    }

    /**
     * Muestra el formulario para un nuevo registro de cargo en seguimiento de proyectos
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Valida y registra un nuevo cargo
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
            'name' => ['required', 'max:100', 'unique:project_tracking_positions,name'],
            'description' => ['required', 'max:500']
        ]);
        $projecttrackingPosition = ProjectTrackingPosition::create(['name' => $request->name, 'description' => $request->description]);
        return response()->json(['record' => $projecttrackingPosition, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un cargo en seguimiento de proyectos
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario para la actualización de un cargo en seguimiento de proyectos
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la información del cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador del cargo a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $projecttrackingPosition = ProjectTrackingPosition::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:project_tracking_positions,name,' . $projecttrackingPosition->id],
            'description' => ['nullable', 'max:200']
        ]);
        $projecttrackingPosition->name  = $request->name;
        $projecttrackingPosition->description = $request->description;
        $projecttrackingPosition->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del cargo a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $projecttrackingPosition = ProjectTrackingPosition::find($id);
        $projecttrackingPosition->delete();
        return response()->json(['record' => $projecttrackingPosition, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los cargo registrados
     *
     * @author  Pedro Contreras <pdrocont@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function getProjectTrackingPositions()
    {
        return response()->json(template_choices('Modules\ProjectTracking\Models\ProjectTrackingPosition', 'name', '', true));
    }
}
