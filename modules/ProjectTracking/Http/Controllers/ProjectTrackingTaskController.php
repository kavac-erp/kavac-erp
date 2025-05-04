<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;
use Modules\ProjectTracking\Models\ProjectTrackingActivityStatus;
use Modules\ProjectTracking\Models\ProjectTrackingPersonalRegister;
use Modules\ProjectTracking\Models\ProjectTrackingTask;

/**
 * @class ProjectTrackingTaskController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskController extends Controller
{
    use ValidatesRequests;

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('projecttracking::tasks.index');
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::tasks.create-edit');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'project_name' => isset($request->project_name) ? ['required'] : ['nullable'],
                'subproject_name' => isset($request->subproject_name) ? ['required'] : ['nullable'],
                'product_name' => isset($request->product_name) ? ['required'] : ['nullable'],
                'activity_plan_id' => ['required'],
                'name' => ['required'],
                'description' => ['nullable', 'Max:250'],
                'employers_id' => ['required'],
                'priority_id' => ['required'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
                'activity_status_id' => ['required'],
                'weight' => ['nullable', 'integer', 'Min:1', 'Max:100'],
            ],
            [],
            [
                'project_name' => 'Proyecto',
                'subproject_name' => 'Subproyecto',
                'product_name' => 'Producto',
                'activity_plan_id' => 'Actividad',
                'employers_id' => 'Responsable',
                'priority_id' => 'Prioridad',
                'start_date' => 'Fecha de incio',
                'end_date' => 'Fecha de culminación',
                'activity_status_id' => 'Estatus de la Actividad',
                'weight' => 'Peso',
            ]
        );
        $task = ProjectTrackingTask::create([
            'project_name' => $request->input('project_name'),
            'subproject_name' => $request->input('subproject_name'),
            'product_name' => $request->input('product_name'),
            'activity_plan_id' => $request->input('activity_plan_id'),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'employers_id' => $request->input('employers_id'),
            'priority_id' => $request->input('priority_id'),
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'activity_status_id' => $request->input('activity_status_id'),
            'weight' => $request->input('weight')
        ]);
        return response()->json(['record' => $task, 'message' => 'Success'], 200);
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
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Solicita una tarea seleccionada a editar
     *
     * @method    edit
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        $projecttrackingTask = ProjectTrackingTask::find($id);
        return view('projecttracking::tasks.create-edit', compact('projecttrackingTask'));
    }

    /**
     * Carga la tarea seleccionada a editar
     *
     * @method    vueInfo
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function vueInfo($id)
    {
        $task = ProjectTrackingTask::where('id', $id)->with(['Project', 'Subproject', 'Product', 'ActivityPlan', 'Activity', 'Responsable', 'Priority', 'ActivityStatus'])->first();
        return response()->json(['records' => $task], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'project_name' => isset($request->project_name) ? ['required'] : ['nullable'],
                'subproject_name' => isset($request->subproject_name) ? ['required'] : ['nullable'],
                'product_name' => isset($request->product_name) ? ['required'] : ['nullable'],
                'activity_plan_id' => ['required'],
                'name' => ['required'],
                'description' => ['nullable', 'Max:250'],
                'employers_id' => ['required'],
                'priority_id' => ['required'],
                'start_date' => ['required', 'before_or_equal:end_date'],
                'end_date' => ['required', 'after_or_equal:start_date'],
                'activity_status_id' => ['required'],
                'weight' => ['nullable', 'integer', 'Min:1', 'Max:100'],
            ],
            [],
            [
                'project_name' => 'Proyecto',
                'subproject_name' => 'Subproyecto',
                'product_name' => 'Producto',
                'activity_plan_id' => 'Actividad',
                'employers_id' => 'Responsable',
                'priority_id' => 'Prioridad',
                'start_date' => 'Fecha de incio',
                'end_date' => 'Fecha de culminación',
                'activity_status_id' => 'Estatus de la Actividad',
                'weight' => 'Peso',
            ]
        );
        $tasks = ProjectTrackingTask::find($request->input('id'));
        $tasks->project_name->$request->input('project_name');
        $tasks->subproject_name->$request->input('subproject_name');
        $tasks->product_name->$request->input('product_name');
        $tasks->activity_plan_id->$request->input('activity_plan_id');
        $tasks->name->$request->input('name');
        $tasks->description->$request->input('description');
        $tasks->task_responsable_id->$request->input('task_responsable_id');
        $tasks->priority_id->$request->input('priority_id');
        $tasks->start_date->$request->input('start_date');
        $tasks->end_date->$request->input('end_date');
        $tasks->activity_status_id->$request->input('activity_status_id');
        $tasks->weight->$request->input('weight');
        return response()->json(['record' => $tasks, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la tarea seleccionada
     *
     * @method    recordInfo
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function recordInfo($id)
    {
        $records = ProjectTrackingTask::with(['Project', 'Subproject', 'Product', 'ActivityPlan', 'Activity', 'Responsable', 'Priority'])->find($id)->toArray();
        $activities = ProjectTrackingActivity::where('id', $records['activity_plan_id'])->get()->toArray();
        $records['activity_name'] = $activities[0]['name_activity'];
        $employers = ProjectTrackingPersonalRegister::where('id', $records['employers_id'])->get()->toArray();
        $records['employers_name'] = $employers[0]['name'] . ' ' . $employers[0]['last_name'];
        $activityStatuses = ProjectTrackingActivityStatus::where('id', $records['activity_status_id'])->get()->toArray();
        $records['activity_status_name'] = $activityStatuses[0]['name'];
        return response()->json(['records' => $records]);
    }

    /**
     * Elimina la tarea seleccionada
     *
     * @method    destroy
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $task = ProjectTrackingTask::find($id);
        $task->delete();
        // return response()->json(['record' => $task, 'message' => 'Success'], 200);
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Muestra las tareas registradas
     *
     * @method    VueList
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function vueList()
    {

        $records = ProjectTrackingTask::with([
            'Project', 'Subproject', 'Product', 'Activity', 'Responsable', 'Priority', 'ActivityStatus'
        ])->get()->toArray();
        foreach ($records as $key => $value) {
            $employers = ProjectTrackingPersonalRegister::where('id', $value['employers_id'])->get()->toArray();
            $records[$key]['employers_name'] = $employers[0]['name'] . ' ' . $employers[0]['last_name'];
        }
        return response()->json(['records' => $records], 200);
    }
}
