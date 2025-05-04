<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;

/**
 * @class ProjectTrackingActivityStatusController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author Pedro Buitrago pbuitrago@cenditel.gob.ve
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityStatusController extends Controller
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
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'color'               => ['required'],
            'name'                => ['required'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'color.required'      => 'El campo color es obligatorio.',
            'name.required'       => 'El campo nombre es obligatorio.',
        ];
    }
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        //return view('projecttracking::index');
        return response()->json(['records' => ProjectTrackingActivityStatus::all()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Retorna un json con todas las prioridades registradas para ser usado en un componente <select2>
     *
     * @method    getActivityStatuses
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function getActivityStatuses()
    {
        $activitystatusesList = ProjectTrackingActivityStatus::all();
        $activitystatuses = [];
        array_push($activitystatuses, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($activitystatusesList->all() as $activitystatus) {
            array_push($activitystatuses, [
                'id' => $activitystatus->id,
                'text' => $activitystatus->name . ' ' . $activitystatus->last_name
            ]);
        }
        return response()->json($activitystatuses);
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        //
        $activityStatus = ProjectTrackingActivityStatus::create([
            'color' => $request->input('color'),
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return response()->json(['record' => $activityStatus, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $activityStatus = ProjectTrackingActivityStatus::find($request->input('id'));
        $activityStatus->color = $request->input('color');
        $activityStatus->name = $request->input('name');
        $activityStatus->description = $request->input('description');
        $activityStatus->save();
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
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $activityStatus = ProjectTrackingActivityStatus::find($id);
        $activityStatus->delete();
        return response()->json(['record' => $activityStatus, 'message' => 'Success'], 200);
    }
}
