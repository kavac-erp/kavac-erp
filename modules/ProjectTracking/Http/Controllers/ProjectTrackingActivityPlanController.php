<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
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
 * @brief [descripción detallada]
 *
 * Clase controlador del plan de actividades
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 *
 * @license LICENCIA DE SOFTWARE CENDITEL
 * @link    http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/
 */
class ProjectTrackingActivityPlanController extends Controller
{
    use ValidatesRequests;

    /**
     * Vista del plan de actividades
     *
     * @method index
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('projecttracking::activity_plans.index');
    }

    /**
     * Crear formulario de plan de actividades
     *
     * @method create
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function create()
    {
        if (Module::has('Payroll')) {
            $payrollStaff = 1;
        } else {
            $payrollStaff = 0;
        }
        return view('projecttracking::activity_plans.create-edit', compact('payrollStaff'));
    }

    /**
     * Método para crear y registrar el plan de actividades
     *
     * @param object Request $request Objeto con información de la petición
     *
     * @method store
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
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
                /*$this->validate(
                    $request,
                    [
                        'project_name' => isset($request->project_name) ? [
                            'required',
                            'unique:project_tracking_activity_plans,project_name'
                        ] : ['nullable'],
                        'subproject_name' => isset($request->subproject_name) ? [
                            'required',
                            'unique:project_tracking_activity_plans,subproject_name'
                        ] : ['nullable'],
                        'product_name' => isset($request->product_name) ? [
                            'required',
                            'unique:project_tracking_activity_plans,product_name'
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
                );*/

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
                    /*$this->validate(
                        $request,
                        [
                            'start_date' => ['before_or_equal:' . $activity['start_date_activity']],
                            'end_date' => ['after_or_equal:' . $activity['end_date_activity']],
                        ],
                        [
                            'start_date.before_or_equal' => 'El campo fecha inicio' .
                                ' de la actividad tiene que mayor o igual de la fecha de fin del plan de actividades',
                            'end_date.after_or_equal' => 'El campo fecha fin de' .
                            'la actividad tiene que ser menor o igual de la fecha de inicio del plan de actividades',
                        ],
                        [
                            'start_date' => 'Fecha de inicio del plan de actividad',
                            'end_date' => 'Fecha de fin del plan de actividad',
                        ]
                    );*/

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
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method show
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @param integer $employer            Identificador del registro del empleado
     * @param integer $staffClassification Identificador de la clasificación del equipo
     *
     * @method show
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function recordTeamInfo($employer, $staffClassification)
    {
        if (Module::has('Payroll')) {
            $employerRecord = payrollStaff::find($employer);
        } else {
            $employerRecord = ProjectTrackingPersonalRegister::with('Position')->find($employer);
        }
        $classificationRecord = ProjectTrackingStaffClassification::find($staffClassification);
        return response()->json(['employerRecord' => $employerRecord, 'classificationRecord' => $classificationRecord]);
    }

    /**
     * [descripción del método]
     *
     * @param integer $activity Identificador del registro de actividad
     *
     * @method show
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method show
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method edit
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @param object Request $request Objeto con datos de la petición
     * @param integer        $id      Identificador del registro
     *
     * @method update
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
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
                    'start_date' => ['before_or_equal:' . $activity['start_date_activity']],
                    'end_date' => ['after_or_equal:' . $activity['end_date_activity']],
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

            $created_team = ProjectTrackingActivityPlanTeam::where('employers_id', $activity['responsable_activity_id'])
                ->where('activity_plan_id', $activityplans->id)
                ->first();

            $addactivity = ProjectTrackingActivityPlanActivity::create(
                [
                    'activity_id' => $activity['activity_id'],
                    'responsable_activity_id' => $created_team->id,
                    'start_date' => $activity['start_date_activity'],
                    'end_date' => $activity['end_date_activity'],
                    'activity_plan_id' => $activityplans->id,
                    'percentage' => $activity['percentage']
                ]
            );
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('projecttracking.activity_plans.index')], 200);
    }

    /**
     * [descripción del método]
     *
     * @param integer $activityplan_id Identificador del registro
     *
     * @method destroy
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
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
     * @method VueList
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable
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
     * @param integer $id Identificador del registro
     *
     * @method VueList
     *
     * @author Pedro Contreras <pdrocont@gmail.com>
     *
     * @return Renderable
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
     * @method getProjectsByActivityPlan
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
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
     * @param integer $id Identificador del registro
     *
     * @method getActivityByProject
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function getActivitiesByProject($id)
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
        return response()->json(['activities_by_project' => $ActivitiesByProject]);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de
     * un proyecto para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getActivityBySubProject
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getPersonalByProject($id)
    {
        $PersonByProject = ProjectTrackingProject::with('ActivityPlan')->where('id', $id)->first();
        $array = $PersonByProject->toArray();
        $TeamByActivityPlanId = ProjectTrackingActivityPlanTeam::where(
            'activity_plan_id',
            $array["activity_plan"]["id"]
        )->get();
        $array2 = $TeamByActivityPlanId->toArray();
        $EmployersId = array_column($array2, 'employers_id');
        $Employers = ProjectTrackingPersonalRegister::whereIn('id', $EmployersId)->get()->toArray();
        $PersonalByProject = [];
        array_push(
            $PersonalByProject,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($Employers as $Employer) {
            array_push(
                $PersonalByProject,
                [
                    'id' => $Employer['id'],
                    'text' => $Employer['name'] . ' ' . $Employer['last_name']
                ]
            );
        }
        return response()->json(['personal_by_project' => $PersonalByProject]);
    }

    /**
     * Retorna un json con todas los subproyectos asociados a un Proyecto
     * para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getSubProyectsByProject
     *
     * @author Fabian Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getSubProjectsByProject($id)
    {
        $SubProjectsByProject = ProjectTrackingSubProject::where('project_id', $id)->get();
        $subProjects = [];
        // dd($SubProjectsByProject);
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
                    'name' => $subProject->name,
                    'responsable_id' => $subProject->responsable,
                    'start_date' => $subProject->start_date,
                    'end_date' => $subProject->end_date,
                ]
            );
        }

        return response()->json($subProjects);
    }

    /**
     * Retorna un json con todos los Proyectos que tengan un Plan de Actividad
     *
     * @method getSubProjectsByActivityPlan
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getSubProjectsByActivityPlan()
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
        return response()->json($subProjects);
    }

    /**
     * Retorna un json con todas las actividades macro asociadas a un Subproyecto
     * para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getActivityBySubProject
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getActivitiesBySubProject($id)
    {
        $ActivityBySubProject = ProjectTrackingSubProject::with('ActivityPlan')->where('id', $id)->first();
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
        return response()->json(['activities_by_subproject' => $ActivitiesBySubProject]);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de
     * un subproyecto para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getActivityByProject
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getPersonalBySubProject($id)
    {
        $PersonBySubProject = ProjectTrackingSubProject::with('ActivityPlan')->where('id', $id)->first();
        $array = $PersonBySubProject->toArray();
        $TeamByActivityPlanId = ProjectTrackingActivityPlanTeam::where(
            'activity_plan_id',
            $array["activity_plan"]["id"]
        )->get();
        $array2 = $TeamByActivityPlanId->toArray();
        $EmployersId = array_column($array2, 'employers_id');
        $Employers = ProjectTrackingPersonalRegister::whereIn('id', $EmployersId)->get()->toArray();
        $PersonalBySubProject = [];
        array_push(
            $PersonalBySubProject,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($Employers as $Employer) {
            array_push(
                $PersonalBySubProject,
                [
                    'id' => $Employer['id'],
                    'text' => $Employer['name'] . ' ' . $Employer['last_name']
                ]
            );
        }
        return response()->json(['personal_by_subproject' => $PersonalBySubProject]);
    }

    /**
     * Retorna un json con todos los Proyectos que tengan un Plan de Actividad
     *
     * @method getProjectsByActivityPlan
     *
     * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getProductsByActivityPlan()
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
        return response()->json($products);
    }

    /**
     * Retorna un json con todas las actividades macro asociadas a un Producto para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getActivityByProduct
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function getActivitiesByProduct($id)
    {
        $ActivityByProduct = ProjectTrackingProduct::with('ActivityPlan')->where('id', $id)->first();
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
        return response()->json(['activities_by_product' => $ActivitiesByProduct]);
    }

    /**
     * Retorna un json con todo el personal asociado a un plan de actividad de un
     * proyecto para ser usado en un componente <select2>
     *
     * @param integer $id Identificador del registro
     *
     * @method getActivityByProduct
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return Renderable [descripción de los datos devueltos]
     */
    public function getPersonalByProduct($id)
    {
        $PersonByProduct = ProjectTrackingProduct::with('ActivityPlan')->where('id', $id)->first();
        $array = $PersonByProduct->toArray();
        $TeamByActivityPlanId = ProjectTrackingActivityPlanTeam::where(
            'activity_plan_id',
            $array["activity_plan"]["id"]
        )->get();
        $array2 = $TeamByActivityPlanId->toArray();
        $EmployersId = array_column($array2, 'employers_id');
        $Employers = ProjectTrackingPersonalRegister::whereIn('id', $EmployersId)->get()->toArray();
        $PersonalByProduct = [];
        array_push(
            $PersonalByProduct,
            [
                'id' => '',
                'text' => 'Seleccione...'
            ]
        );

        foreach ($Employers as $Employer) {
            array_push(
                $PersonalByProduct,
                [
                    'id' => $Employer['id'],
                    'text' => $Employer['name'] . ' ' . $Employer['last_name']
                ]
            );
        }
        return response()->json(['personal_by_product' => $PersonalByProduct]);
    }
}
