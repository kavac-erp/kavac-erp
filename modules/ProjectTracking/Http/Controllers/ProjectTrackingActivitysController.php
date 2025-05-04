<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;
use Modules\ProjectTracking\Models\ProjectTrackingProjectType;
use Modules\ProjectTracking\Models\ProjectTrackingTypeProducts;

/**
 * @class ProjectTrackingActivitysController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivitysController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     * @var Array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     * @var Array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Pedro Buitragp <pbuitrago@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'orden'                               => ['required'],
            'name_activity'                       => ['required'],
            'project_tracking_project_types_id'   => ['required'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                               => 'El campo nombre del proceso es obligatorio.',
            'orden.required'                              => 'El campo orden es obligatorio.',
            'project_tracking_project_types_id.required'  => 'El campo tipo de Proyecto es obligatorio.',
        ];
    }
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function index()
    {
        //return view('projecttracking::index');
        return response()->json(['records' => ProjectTrackingActivity::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    Pedro Buitrago <pbuitrago@cednditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $activity = ProjectTrackingActivity::create([
            'orden' => $request->input('orden'),
            'name_activity' => $request->input('name_activity'),
            'description' => $request->input('description'),
            'project_tracking_type_products_id'  => $request->input('project_tracking_type_products_id'),
            'project_tracking_project_types_id'  => $request->input('project_tracking_project_types_id')
        ]);

        return response()->json(['record' => $activity, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $product = ProjectTrackingActivity::find($request->input('id'));
        $product->orden = $request->input('orden');
        $product->name_activity = $request->input('name_activity');
        $product->description = $request->input('description');
        $product->project_tracking_type_products_id = $request->input('project_tracking_type_products_id');
        $product->project_tracking_project_types_id = $request->input('project_tracking_project_types_id');
        $product->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function destroy($id)
    {
        $product = ProjectTrackingActivity::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Retorna un json con todas las actividades para ser usado en un componente <select2>
     *
     * @method    getActivities
     *
     * @author    Pedro Contreras <pdrocont@gmail.com>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function getActivities()
    {
        $activitiesList = ProjectTrackingActivity::all();
        $activities = [];
        array_push($activities, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($activitiesList->all() as $activity) {
            array_push($activities, [
                'id' => $activity->id,
                'text' => $activity->name_activity
            ]);
        }
        return response()->json($activities);
    }
}
