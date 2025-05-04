<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Modules\ProjectTracking\Models\ProjectTrackingActivityPlan;
use Modules\ProjectTracking\Models\ProjectTrackingPersonalRegister;
use Modules\ProjectTracking\Models\ProjectTrackingStaffClassification;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;
use Modules\ProjectTracking\Models\ProjectTrackingActivityPlanTeam;
use Modules\ProjectTracking\Models\ProjectTrackingActivityPlanActivity;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;
use Modules\ProjectTracking\Models\ProjectTrackingProject;
use Modules\ProjectTracking\Models\ProjectTrackingSubProject;
use Modules\Payroll\Models\PayrollStaff;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingActivityPlanController
 * @brief Gestiona los procesos del controlador
 *
 * Clase controlador del plan de actividades
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityPlanController extends Controller
{
    use ValidatesRequests;

    /**
     * Vista del plan de actividades
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('projecttracking::activity_plans.index');
    }

    /**
     * Crear formulario de plan de actividades
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $payrollStaff = 1;
        } else {
            $payrollStaff = 0;
        }
        return view('projecttracking::activity_plans.create-edit', compact('payrollStaff'));
    }

    /**
     * Método para crear y registrar el plan de actividades
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $codeSetting = CodeSetting::where('table', 'project_tracking_activity_plans')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar'
                ]
            );
            return response()->json(['result' => false, 'redirect' => route('projecttracking.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            ProjectTrackingActivityPlan::class,
            $codeSetting->field
        );

        DB::transaction(
            function () use ($request, $code) {
                $project = ProjectTrackingActivityPlan::create(
                    [
                        'code' => $code,
                        'project_name' => $request->project_name,
                        'subproject_name' => $request->subproject_name,
                        'product_name' => $request->product_name,
                        'institution_id' => $request->institution_id,
                        'execution_year' => $request->execution_year
                    ]
                );

                foreach ($request->team_members as $team_member) {
                    $team = ProjectTrackingActivityPlanTeam::create(
                        [
                            'employers_id' => $team_member['employers_id'],
                            'staff_classification_id' => $team_member['staff_classifications_id'],
                            'activity_plan_id' => $project->id
                        ]
                    );
                }

                foreach ($request->activity_plans as $activity) {
                    $created_team = ProjectTrackingActivityPlanTeam::where(
                        'employers_id',
                        $activity['responsable_activity_id']
                    )
                        ->where('activity_plan_id', $project->id)
                        ->first();

                    $addactivity = ProjectTrackingActivityPlanActivity::create(
                        [
                            'activity_id' => $activity['activity_id'],
                            'responsable_activity_id' => $created_team->id,
                            'start_date' => $activity['start_date_activity'],
                            'end_date' => $activity['end_date_activity'],
                            'activity_plan_id' => $project->id,
                            'percentage' => $activity['percentage']
                        ]
                    );
                }
            }
        );

        $activity_plans = ProjectTrackingActivityPlan::where('code', $code)->first();
        if (is_null($activity_plans)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación.'
                ]
            );
        } else {
            $request->session()->flash('message', ['type' => 'store']);
        }
        return response()->json(['result' => true, 'redirect' => route('projecttracking.activity_plans.index')], 200);
    }

    /**
     * Obtiene información de un plan de actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordInfo($id)
    {
        return response()->json(['records' => ProjectTrackingActivityPlan::with([
            'Responsable',
            'Project',
            'Product',
            'Dependency'
        ])->find($id)]);
    }

    /**
     * Obtiene información del equipo
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $employer            Identificador del registro del empleado
     * @param integer $staffClassification Identificador de la clasificación del equipo
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordTeamInfo($employer, $staffClassification)
    {
        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $employerRecord = payrollStaff::find($employer);
        } else {
            $employerRecord = ProjectTrackingPersonalRegister::with('Position')->find($employer);
        }
        $classificationRecord = ProjectTrackingStaffClassification::find($staffClassification);
        return response()->json(['employerRecord' => $employerRecord, 'classificationRecord' => $classificationRecord]);
    }

    /**
     * Obtiene información de la actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $activity Identificador del registro de actividad
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recordActivityInfo($activity)
    {
        $activityRecord = ProjectTrackingActivity::with([
            'projectTrackingProjectTypes',
            'projectTrackingTypeProducts'
        ])->find($activity);
        return response()->json(['activityRecord' => $activityRecord]);
    }

    /**
     * Muestra información de un plan de actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario de edición de un plan de actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $projecttrackingActivityPlan = ProjectTrackingActivityPlan::find($id);
        if (Module::has('Payroll')) {
            $payrollStaff = 1;
        } else {
            $payrollStaff = 0;
        }
        return view('projecttracking::activity_plans.create-edit', compact(
            'projecttrackingActivityPlan',
            'payrollStaff'
        ));
    }

    /**
     * Actualiza la información de un plan de actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param Request $request Datos de la petición
     * @param integer        $id      Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $activityplans = ProjectTrackingActivityPlan::find($id);
        $this->validate(
            $request,
            [
                'project_name' => isset($request->project_name) ? [
                    'required',
                    'unique:project_tracking_activity_plans,project_name,' . $activityplans->id
                ] : ['nullable'],
                'subproject_name' => isset($request->subproject_name) ? [
                    'required',
                    'unique:project_tracking_activity_plans,subproject_name,' . $activityplans->id
                ] : ['nullable'],
                'product_name' => isset($request->product_name) ? [
                    'required',
                    'unique:project_tracking_activity_plans,product_name,' . $activityplans->id
                ] : ['nullable'],
                'institution_id' => ['required'],
                'name' => ['required'],
                'responsable' => ['required'],
                'dependency' => [],
                'execution_year' => ['required'],
                'start_date' => ['required'],
                'end_date' => ['required'],
                'team_members.*.employers' => ['required'],
                'team_members.*.staff_classifications' => ['required'],
                'activity_plans.*.activity' => ['required'],
                'activity_plans.*.start_date_activity' => [
                    'required',
                    'before_or_equal:activity_plans.*.end_date_activity'
                ],
                'activity_plans.*.end_date_activity' => [
                    'required',
                    'after_or_equal:activity_plans.*.start_date_activity'
                ],
            ],
            [],
            [
                'project_name' => 'Proyecto',
                'subproject_name' => 'Subproyecto',
                'product_name' => 'Producto',
                'institution_id' => 'Nombre de la institución',
                'name_id' => 'Nombre',
                'responsable' => 'Responsable',
                'dependency' => 'Dependencia',
                'execution_year' => 'Año de ejecución',
                'start_date' => 'Fecha de inicio',
                'end_date' => 'Fecha fin',
                'team_members.*.employers' => 'Trabajador',
                'team_members.*.staff_classifications' => 'Rol',
                'activity_plans.*.activity' => 'Actividad',
                'activity_plans.*.start_date_activity' => 'Fecha de inicio',
                'activity_plans.*.end_date_activity' => 'Fecha fin',
            ]
        );

        $activityplans->project_name = $request->input('project_name');
        $activityplans->subproject_name = $request->input('subproject_name');
        $activityplans->product_name = $request->input('product_name');
        $activityplans->institution_id = $request->input('institution_id');
        $activityplans->save();

        $deletedTeam = ProjectTrackingActivityPlanTeam::where('activity_plan_id', $activityplans->id)->delete();
        $deletedActivity = ProjectTrackingActivityPlanActivity::where('activity_plan_id', $activityplans->id)->delete();

        foreach ($request->team_members as $team_member) {
            $team = ProjectTrackingActivityPlanTeam::create(
                [
                    'employers_id' => $team_member['employers_id'],
                    'staff_classification_id' => $team_member['staff_classifications_id'],
                    'activity_plan_id' => $activityplans->id
                ]
            );
        }

        foreach ($request->activity_plans as $activity) {
            $this->validate(
                $request,
                [
                    'start_date' => ['before_or_equal:' . $activity['end_date_activity']],
                    'end_date' => ['after_or_equal:' . $activity['start_date_activity']],
                ],
                [
                    'start_date.before_or_equal' => 'El campo fecha inicio de la ' .
                        'actividad tiene que mayor o igual de la fecha de fin del plan de actividades',
                    'end_date.after_or_equal' => 'El campo fecha fin de la actividad ' .
                        'tiene que ser menor o igual de la fecha de inicio del plan de actividades',
                ],
                [
                    'start_date' => 'Fecha de inicio del plan de actividad',
                    'end_date' => 'Fecha de fin del plan de actividad',
                ]
            );
            $created_team = ProjectTrackingActivityPlanTeam::query()
                ->where('activity_plan_id', $activityplans->id)
                ->where('employers_id', $activity['responsable_activity_id'])
                ->first();

            $addactivity = ProjectTrackingActivityPlanActivity::query()->create([
                'activity_id' => $activity['activity_id'],
                'responsable_activity_id' => $created_team->id,
                'start_date' => $activity['start_date_activity'],
                'end_date' => $activity['end_date_activity'],
                'activity_plan_id' => $activityplans->id,
                'percentage' => $activity['percentage']
            ]);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('projecttracking.activity_plans.index')], 200);
    }

    /**
     * Elimina un plan de actividad
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $activityplan_id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($activityplan_id)
    {
        ProjectTrackingActivityPlan::find($activityplan_id)->delete();
        ProjectTrackingActivityPlanActivity::where('activity_plan_id', $activityplan_id)->delete();
        ProjectTrackingActivityPlanTeam::where('activity_plan_id', $activityplan_id)->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Muestra los planes de actividades registrados
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json(
            [
                'records' => ProjectTrackingActivityPlan::with(
                    [
                        'project.Responsable',
                        'subProject.Responsable',
                        'product.Responsable',
                        'project.Dependency',
                        'product.Dependency',
                        'institution',
                        'activities.projectTrackingTeamMember.projectTrackingPersonalRegister',
                        'activities.projectTrackingActivities',
                        'teams.projectTrackingPersonalRegister',
                        'teams.projectTrackingStaffClassification'
                    ]
                )
                    ->get()
            ],
            200
        );
    }

    /**
     * Muestra la información de los planes de actividades registrados
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo($id)
    {
        $activityplans = ProjectTrackingActivityPlan::where('id', $id)->with([
            'project.Responsable',
            'subProject.Responsable',
            'product.Responsable',
            'institution',
            'activities.projectTrackingTeamMember',
            'teams'
        ])->first();
        return response()->json(['records' => $activityplans], 200);
    }

    /**
     * Retorna un json con todos los Proyectos que tengan un Plan de Actividad
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjectsByActivityPlan()
    {
        $activityPlansProjectsId = ProjectTrackingActivityPlan::select('project_name')
            ->whereNotNull('project_name')
            ->get()
            ->toArray();
        $ids = array_column($activityPlansProjectsId, 'project_name');
        $projectsList = ProjectTrackingProject::whereIn('id', $ids)->get();
        $projects = [];
        array_push(
            $projects,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );
        foreach ($projectsList->all() as $project) {
            array_push(
                $projects,
                [
                    'id' => $project->id,
                    'text' => $project->name,
                    'name' => $project->name,
                    'responsable_id' => $project->responsable,
                    'dependency_id' => $project->dependency,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                ]
            );
        }
        return response()->json($projects);
    }

    /**
     * Retorna un json con todas las actividades macro asociadas a un Proyecto para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivitiesByProject($id): JsonResponse
    {
        $ActivityByProject = ProjectTrackingProject::with('ActivityPlan')->where('id', $id)->first();
        $array = $ActivityByProject->toArray();
        $ActivitiesByActivityPlans = ProjectTrackingActivityPlanActivity::with('projectTrackingActivities')
            ->where('activity_plan_id', $array["activity_plan"]["id"])
            ->get();
        $array2 = $ActivitiesByActivityPlans->toArray();
        $ActivitiesByProject = [];
        array_push(
            $ActivitiesByProject,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($array2 as $Activity) {
            array_push(
                $ActivitiesByProject,
                [
                    'id' => $Activity['project_tracking_activities']['id'],
                    'text' => $Activity['project_tracking_activities']['name_activity']
                ]
            );
        }
        return response()->json(['activities_by_project' => $ActivitiesByProject], 200);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de
     * un proyecto para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPersonalByProject($id): JsonResponse
    {
        $project = ProjectTrackingProject::with('ActivityPlan')
            ->where('id', $id)
            ->first();

        $personalByProject = [];
        $personalByProject[] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
        foreach ($project->activityPlan->teams as $member) {
            $personalByProject[] = [
                'id' => $member->id,
                'text' => $member->projectTrackingPersonalRegister->fullName,
            ];
        }
        return response()->json(['personal_by_project' => $personalByProject], 200);
    }

    /**
     * Retorna un json con todas los subproyectos asociados a un Proyecto
     * para ser usado en un componente <select2>
     *
     * @author Fabian Palmera <fapalmera@cenditel.gob.ve>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubProjectsByProject($id): JsonResponse
    {
        $SubProjectsByProject = ProjectTrackingSubProject::where('project_id', $id)
            ->get();
        $subProjects = [];

        array_push(
            $subProjects,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );
        foreach ($SubProjectsByProject->all() as $subProject) {
            array_push(
                $subProjects,
                [
                    'id' => $subProject->id,
                    'text' => $subProject->name,
                    'product_types_ids' => $subProject->getProductTypeIds(),
                    'name' => $subProject->name,
                    'responsable_id' => $subProject->responsable,
                    'start_date' => $subProject->start_date,
                    'end_date' => $subProject->end_date,
                ]
            );
        }

        return response()->json($subProjects, 200);
    }

    /**
     * Retorna un json con todos los Proyectos que tengan un Plan de Actividad
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubProjectsByActivityPlan(): JsonResponse
    {
        $activityPlansSubProjectsId = ProjectTrackingActivityPlan::select('subproject_name')
            ->whereNotNull('subproject_name')
            ->get()
            ->toArray();
        $ids = array_column($activityPlansSubProjectsId, 'subproject_name');
        $subProjectsList = ProjectTrackingSubProject::whereIn('id', $ids)->get();
        $subProjects = [];
        array_push(
            $subProjects,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );
        foreach ($subProjectsList->all() as $subProject) {
            array_push(
                $subProjects,
                [
                    'id' => $subProject->id,
                    'text' => $subProject->name,
                    'name' => $subProject->name,
                    'responsable_id' => $subProject->responsable,
                    'dependency_id' => $subProject->dependency,
                    'start_date' => $subProject->start_date,
                    'end_date' => $subProject->end_date,
                ]
            );
        }
        return response()->json($subProjects, 200);
    }

    /**
     * Retorna un json con todas las actividades macro asociadas a un Subproyecto
     * para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivitiesBySubProject($id): JsonResponse
    {
        $ActivityBySubProject = ProjectTrackingSubProject::with('ActivityPlan')
            ->where('id', $id)
            ->first();
        $array = $ActivityBySubProject->toArray();
        $ActivitiesByActivityPlans = ProjectTrackingActivityPlanActivity::with('projectTrackingActivities')
            ->where('activity_plan_id', $array["activity_plan"]["id"])
            ->get();
        $array2 = $ActivitiesByActivityPlans->toArray();
        $ActivitiesBySubProject = [];
        array_push(
            $ActivitiesBySubProject,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($array2 as $Activity) {
            array_push(
                $ActivitiesBySubProject,
                [
                    'id' => $Activity['project_tracking_activities']['id'],
                    'text' => $Activity['project_tracking_activities']['name_activity']
                ]
            );
        }
        return response()->json(['activities_by_subproject' => $ActivitiesBySubProject], 200);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de
     * un subproyecto para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return JsonResponse    Arreglo con el personal por subproyecto
     */
    public function getPersonalBySubProject($id): JsonResponse
    {
        $subproject = ProjectTrackingSubProject::find($id);
        $personalBySubproject = [];
        $personalBySubproject[] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
        foreach ($subproject->activityPlan->teams as $member) {
            $personalBySubproject[] = [
                'id' => $member->id,
                'text' => $member->projectTrackingPersonalRegister->fullName,
            ];
        }
        return response()->json(['personal_by_subproject' => $personalBySubproject], 200);
    }

    /**
     * Retorna un json con todos los Proyectos que tengan un Plan de Actividad
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByActivityPlan(): JsonResponse
    {
        $activityPlansProductsId = ProjectTrackingActivityPlan::select('product_name')
            ->whereNotNull('product_name')
            ->get()
            ->toArray();
        $ids = array_column($activityPlansProductsId, 'product_name');
        $productsList = ProjectTrackingProduct::whereIn('id', $ids)->get();
        $products = [];
        array_push(
            $products,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );
        foreach ($productsList->all() as $product) {
            array_push(
                $products,
                [
                    'id' => $product->id,
                    'text' => $product->name,
                    'name' => $product->name,
                    'responsable_id' => $product->responsable,
                    'dependency_id' => $product->dependency,
                    'start_date' => $product->start_date,
                    'end_date' => $product->end_date,
                ]
            );
        }
        return response()->json($products, 200);
    }

    /**
     * Retorna un json con todas las actividades macro asociadas a un Producto para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivitiesByProduct($id): JsonResponse
    {
        $ActivityByProduct = ProjectTrackingProduct::with('ActivityPlan')
            ->where('id', $id)
            ->first();
        $array = $ActivityByProduct->toArray();
        $ActivitiesByActivityPlans = ProjectTrackingActivityPlanActivity::with('projectTrackingActivities')
            ->where('activity_plan_id', $array["activity_plan"]["id"])->get();
        $array2 = $ActivitiesByActivityPlans->toArray();
        $ActivitiesByProduct = [];
        array_push(
            $ActivitiesByProduct,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($array2 as $Activity) {
            array_push(
                $ActivitiesByProduct,
                [
                    'id' => $Activity['project_tracking_activities']['id'],
                    'text' => $Activity['project_tracking_activities']['name_activity']
                ]
            );
        }
        return response()->json(['activities_by_product' => $ActivitiesByProduct], 200);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de un
     * proyecto para ser usado en un componente <select2>
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @param integer $id Identificador del registro
     *
     * @return JsonResponse    Arreglo con el personal por producto
     */
    public function getPersonalByProduct($id): JsonResponse
    {
        $product = ProjectTrackingProduct::find($id);
        $personalByProduct = [];
        $personalByProduct[] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
        foreach ($product->activityPlan->teams as $member) {
            $personalByProduct[] = [
                'id' => $member->id,
                'text' => $member->projectTrackingPersonalRegister->fullName,
            ];
        }
        return response()->json(['personal_by_product' => $personalByProduct], 200);
    }
}
