<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Modules\ProjectTracking\Models\ProjectTrackingTask;

/**
 * @class ProjectTrackingTaskController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTaskController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra el listado de tareas
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('projecttracking::tasks.index');
    }

    /**
     * Muestra el formulario para crear una nueva tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::tasks.create-edit');
    }

    /**
     * Almacena la información de una nueva tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $rules = [
            'tasks' => ['nullable', function ($attribute, $value, $fail) {
                if (count($value) == 0) {
                    $fail('Debe agregar al menos una tarea.');
                }
            }],
            'tasks.*.project_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingProject::query());
                }],
            'tasks.*.subproject_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingSubProject::query());
                }],
            'tasks.*.product_name' => [
                'bail',
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $this->validateDateInRange($request, $attribute, $value, $fail, ProjectTrackingProduct::query());
                }],
            'tasks.*.activity_plan_id' => ['required'],
            'tasks.*.name' => ['required'],
            'tasks.*.description' => ['nullable', 'Max:250'],
            'tasks.*.employers_id' => ['required'],
            'tasks.*.priority_id' => ['required'],
            'tasks.*.start_date' => ['required', 'before_or_equal:tasks.*.end_date'],
            'tasks.*.end_date' => ['required', 'after_or_equal:tasks.*.start_date'],
            'tasks.*.activity_status_id' => ['required'],
            'tasks.*.weight' => ['nullable', 'integer', 'Min:1', 'Max:100'],
        ];
        $messages = [];
        foreach ($request->input('tasks', []) as $index => $task) {
            $messages["tasks.{$index}.project_name.required"] = "El campo Proyecto en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.subproject_name.required"] = "El campo Subproyecto en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.product_name.required"] = "El campo Producto en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.activity_plan_id.required"] = "El campo Actividad en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.name.required"] = "El campo Nombre en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.description.max"] = "El campo Descripción en la tarea " . ($index + 1) . " no debe superar los 250 caracteres";
            $messages["tasks.{$index}.employers_id.required"] = "El campo responsable de la tarea en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.priority_id.required"] = "El campo Prioridad en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.start_date.required"] = "El campo Fecha de inicio en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.end_date.required"] = "El campo Fecha de fin en la tarea " . ($index + 1) . " es obligatorio";
            $messages["tasks.{$index}.end_date.after_or_equal"] = "La fecha de inicio no puede ser posterior a la fecha de fin en la tarea " . ($index + 1);
            $messages["tasks.{$index}.start_date.before_or_equal"] = "La fecha de fin no puede ser anterior a la fecha de inicio en la tarea " . ($index + 1);
            $messages["tasks.{$index}.weight.integer"] = "El campo Peso en la tarea " . ($index + 1) . " debe ser un valor numerico";
            $messages["tasks.{$index}.weight.between"] = "El campo Peso en la tarea " . ($index + 1) . " debe estar entre 1 y 100";
            $messages["tasks.{$index}.activity_status_id.required"] = "El campo estatus de la actividad  en la tarea " . ($index + 1) . " es obligatorio";
        }

        $this->validate($request, $rules, $messages);

        foreach ($request->tasks as $task) {
            ProjectTrackingTask::create([
                'project_name' => $task['project_name'],
                'subproject_name' => $task['subproject_name'],
                'product_name' => $task['product_name'],
                'activity_plan_id' => $task['activity_plan_id'],
                'name' => $task['name'],
                'description' => $task['description'],
                'employers_id' => $task['employers_id'],
                'priority_id' => $task['priority_id'],
                'start_date' => $task['start_date'],
                'end_date' => $task['end_date'],
                'activity_status_id' => $task['activity_status_id'],
                'weight' => $task['weight'],
            ]);
        }

        return response()->json(['result' => true, 'redirect' => route('projecttracking.tasks.index')], 200);
    }

    /**
     * Muestra información de una tarea
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Solicita una tarea seleccionada a editar
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $projecttrackingTask = ProjectTrackingTask::find($id);
        return view('projecttracking::tasks.create-edit', compact('projecttrackingTask'));
    }

    /**
     * Carga la tarea seleccionada a editar
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id): JsonResponse
    {
        $task = ProjectTrackingTask::where('id', $id)
            ->with([
                'Project',
                'Subproject',
                'Product',
                'ActivityPlan' => function ($query): void {
                    $query->with([
                        'teams',
                    ]);
                },
                'Activity',
                'Responsable',
                'Priority',
                'ActivityStatus',
            ])->first();
        return response()->json(['records' => $task], 200);
    }

    /**
     * Actualiza la información de una tarea
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
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
        $task = ProjectTrackingTask::find($request->input('id'));
        if (isset($request->project_name)) {
            $task->project_name = $request->input('project_name');
        } elseif (isset($request->subproject_name)) {
            $task->subproject_name = $request->input('subproject_name');
        } else {
            $task->product_name = $request->input('product_name');
        }
        $task->activity_plan_id = $request->input('activity_plan_id');
        $task->name = $request->input('name');
        $task->description = $request->input('description');
        $task->employers_id = $request->input('employers_id');
        $task->priority_id = $request->input('priority_id');
        $task->start_date = $request->input('start_date');
        $task->end_date = $request->input('end_date');
        $task->activity_status_id = $request->input('activity_status_id');
        $task->weight = $request->input('weight');
        $task->save();
        return response()->json(['result' => true, 'redirect' => route('projecttracking.tasks.index')], 200);
    }

    /**
     * Muestra la tarea seleccionada
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function recordInfo($id): JsonResponse
    {
        $records = ProjectTrackingTask::where('id', $id)
            ->get()
            ->map(function (ProjectTrackingTask $record): array {
                return array_merge($record->toArray(), [
                    'project' => $record->project,
                    'subproject' => $record->subproject,
                    'product' => $record->product,
                    'priority' => $record->priority,
                    'activity_name' => $record->activity->name_activity,
                    'activity_status_name' => $record->activityStatus->name,
                    'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                ]);
            });
        return response()->json(['records' => $records[0]], 200);
    }

    /**
     * Elimina la tarea seleccionada
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $task = ProjectTrackingTask::find($id);
        $task->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Muestra las tareas registradas
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @author    Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return    JsonResponse JSON con los registros
     */
    public function vueList(): JsonResponse
    {
        $records = ProjectTrackingTask::all()
            ->map(function (ProjectTrackingTask $record): array {
                return array_merge($record->toArray(), [
                    'activity_status' => $record->activityStatus,
                    'priority' => $record->priority,
                    'project' => $record->project,
                    'subproject' => $record->subproject,
                    'product' => $record->product,
                    'employers_name' => $record->responsable->projectTrackingPersonalRegister->fullName,
                ]);
            });
        return response()->json(['records' => $records], 200);
    }

    /**
     * Valida un rango de fechas
     *
     * @param \Illuminate\Http\Request $request Datos e la petición
     * @param string $attribute Nombre del atributo
     * @param integer $value Identificador del registro
     * @param mixed $fail Registros fallidos
     * @param mixed $querySet Objeto con los datos de la consulta
     *
     * @return void
     */
    private function validateDateInRange(Request $request, $attribute, $value, $fail, $querySet)
    {
        $currentIndex = explode('.', $attribute)[1];
        if (isset($value)) {
            $entity = $querySet->find($value);
            $startDateEntity = Carbon::parse($entity->start_date);
            $endDateEntity = Carbon::parse($entity->end_date);
            $startDateTask = Carbon::parse($request->tasks[$currentIndex]['start_date']);
            $endDateTask = Carbon::parse($request->tasks[$currentIndex]['end_date']);
            if (!$startDateTask->between($startDateEntity, $endDateEntity) && !$endDateTask->between($startDateEntity, $endDateEntity)) {
                $fail("La fecha de inicio y fin de la tarea " . $currentIndex + 1 . " debe estar entre {$startDateEntity->format('d/m/Y')} y {$endDateEntity->format('d/m/Y')}.");
            }
        }
    }
}
