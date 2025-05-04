<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\Department;
use Modules\Budget\Models\BudgetProject;
use Modules\Payroll\Models\PayrollPosition;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Accounting\Models\Profile;
use Modules\Payroll\Models\PayrollEmployment;

/**
 * @class BudgetProjectController
 * @brief Controlador de Proyectos
 *
 * Clase que gestiona los Proyectos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetProjectController extends Controller
{
    use ValidatesRequests;

    /** @var array Arreglo con las reglas de validación sobre los datos de un formulario */
    public $validate_rules;

    /** @var array Arreglo con los mensajes de error de cada campo de un formulario */
    public $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.project.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.project.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.project.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.project.delete', ['only' => 'destroy']);

        /** @var array Define las reglas de validación para el formulario */
        $this->validate_rules = [
            'institution_id' => ['required'],
            'department_id' => ['required'],
            'payroll_position_id' => ['required'],
            'payroll_staff_id' => ['required'],
            'code' => ['required','unique:budget_projects'],
            'name' => ['required'],
            'description' => ['required'],
            'from_date' => ['required'],
            'to_date' => ['required'],
        ];

        /** @var array Define los mensajes de error para el formulario */
        $this->messages = [
            'institution_id.required' => 'El campo institución es obligatorio. ',
            'department_id.required' => 'El campo dependencia es obligatorio. ',
            'payroll_position_id.required' => 'El campo cargo de responsable es obligatorio. ',
            'payroll_staff_id.required' => 'El campo responsable es obligatorio. ',
            'code.required' => 'El campo código es obligatorio. ',
            'code.unique' => 'El campo código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio. ',
            'description.required' => 'El campo descripción es obligatorio. ',
            'from_date.required' => 'El campo fecha de inicio es obligatorio. ',
            'to_date.required' => 'El campo fecha de finalización es obligatorio. ',
        ];
    }

    /**
     * Muestra un listado de proyectos
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return Renderable
     */
    public function index()
    {
        return view('budget::index');
    }

    /**
     * Muestra el formulario para crear un proyecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return Renderable
     */
    public function create()
    {
        /** @var array Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => 'budget.projects.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /** @var array Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(Institution::class, ['acronym'], ['active' => true]);

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if ($user_profile !== null && $user_profile->institution_id !== null) {
            foreach ($institutions as $clave => $valor) {
                if ($user_profile->institution_id == $clave) {
                    $institutions = array(
                        $clave => $valor
                    );
                }
            }
        }

        /** @var array Arreglo de opciones de departamentos a representar en la plantilla para su selección */
        $departments = template_choices(Department::class, ['acronym', '-', 'name'], ['active' => true]);

        /**
         * @var array Arreglo de opciones de cargos a representar en la
         * plantilla para su selección
         */
        $positions = template_choices(
            PayrollPosition::class,
            'name',
        );

        /** @var array Arreglo de opciones de personal a representar en la plantilla para su selección */
        $staffs = template_choices(
            PayrollStaff::class,
            ['id_number', '-', 'full_name'],
            ['relationship' => 'payrollEmployment', 'where' => ['active' => true]]
        );

        return view('budget::projects.create-edit-form', compact(
            'header',
            'institutions',
            'departments',
            'positions',
            'staffs'
        ));
    }

    /**
     * Guarda información del nuevo proyecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  Request $request Objeto con datos de la petición realizada
     * @return Renderable
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validate_rules, $this->messages);

        /**
         * Registra el nuevo proyecto
         */
        BudgetProject::create([
            'name' => $request->name,
            'code' => $request->code,
            'onapre_code' => $request->onapre_code,
            'active' => ($request->active !== null),
            'department_id' => $request->department_id,
            'payroll_position_id' => $request->payroll_position_id,
            'payroll_staff_id' => $request->payroll_staff_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'description' => $request->description
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Muestra información de un proyecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador del proyecto a mostrar
     * @return Renderable
     */
    public function show($id)
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario para editar un proyecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador del proyecto a modificar
     * @return Renderable
     */
    public function edit($id)
    {
        /** @var object Objeto con información del proyecto a modificar */
        $budgetProject = BudgetProject::find($id);
        $budgetProjectInstitucion = BudgetProject::find($id)->department;

        $staffPosition = PayrollEmployment::query()
        ->where('payroll_staff_id', $budgetProject->payroll_staff_id)
        ->first()
        ->payroll_position;


        /** @var array Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => ['budget.projects.update', $budgetProject->id],
            'method' => 'PUT',
            'role' => 'form'
        ];

        /** @var object Objeto con datos del modelo a modificar */
        $model = $budgetProject;

        $model["institution_id"] = $budgetProjectInstitucion["institution_id"];

        /** @var array Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(Institution::class, [
            'acronym',
            '-',
            'name'
        ]);

        /** @var array Arreglo de opciones de departamentos a representar en la plantilla para su selección */
        $departments = template_choices(
            Department::class,
            [
            'acronym',
            '-',
            'name'
            ],
            [
            'active' => true
            ]
        );

        /** @var array Arreglo de opciones de cargos a representar en la plantilla para su selección */
        $positions = template_choices(
            PayrollPosition::class,
            'name',
            [
                'relationship' => 'payrollEmployments',
                'where' => [
                    'payroll_employment_payroll_position.active' => true
                ]
            ]
        );

        if (!is_null($staffPosition)) {
            $positions[$staffPosition->id] = $staffPosition->name;
        }


        /** @var array Arreglo de opciones de personal a representar en la plantilla para su selección */
        $staffs = template_choices(
            PayrollStaff::class,
            ['id_number', '-', 'full_name'],
            ['relationship' => 'payrollEmployment', 'where' => ['active' => true]]
        );

        return view('budget::projects.create-edit-form', compact(
            'header',
            'model',
            'institutions',
            'departments',
            'positions',
            'staffs'
        ));
    }

    /**
     * Actualiza la información de un proyecto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  Request $request Objeto con datos de la petición realizada
     * @param  integer $id Identificador del proyecto a modificar
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //$this->validate($request, $this->validate_rules, $this->messages);
        $this->validate($request, [
            'institution_id'       => ['required'],
            'department_id'        => ['required'],
            'payroll_position_id'  => ['required'],
            'payroll_staff_id'     => ['required'],
            'name'                 => ['required'],
            'code'                 => ['required', 'unique:budget_projects,code,' . $id],
        ], [
            'institution_id.required'      => 'El campo institución es obligatorio. ',
            'department_id.required'       => 'El campo dependencia es obligatorio. ',
            'payroll_position_id.required' => 'El campo cargo de responsable es obligatorio. ',
            'payroll_staff_id.required'    => 'El campo responsable es obligatorio. ',
            'code.required'                => 'El campo código es obligatorio. ',
            'code.unique'                => 'El campo código ya ha sido registrado en otro proyecto.',
            'name.required'                => 'El campo nombre es obligatorio. ',
        ]);

        /** @var object Objeto con información del proyecto a modificar */
        $budgetProject = BudgetProject::find($id);
        $budgetProject->fill($request->all());
        /** @var boolean Establece si el proyecto esta o no activo */
        $budgetProject->active = $request->active ?? false;
        $budgetProject->save();

        $request->session()->flash('message', ['type' => 'update']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Elimina un proyecto específico
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador del proyecto a eliminar
     * @return Renderable
     */
    public function destroy($id)
    {
        /** @var object Objeto con información del proyecto a eliminar */
        $budgetProject = BudgetProject::find($id);

        if ($budgetProject) {
            $budgetProject->delete();
        }

        return response()->json(['record' => $budgetProject, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene listado de registros
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  [boolean] $active Filtrar por estatus del registro, valores permitidos true o false,
     *                           este parámetro es opcional.
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList($active = null)
    {
        /** @var object Objeto con información de los proyectos registrados */
        $budgetProjects = ($active !== null)
            ? BudgetProject::where('active', $active)->with(['payrollStaff', 'specificActions.subSpecificFormulations'])->get()
            : BudgetProject::with(['payrollStaff', 'specificActions.subSpecificFormulations'])->get();

        $records = [];

        foreach ($budgetProjects as $budgetProject) {
            if (count($budgetProject->specificActions) > 0) {
                foreach ($budgetProject->specificActions as $specificAction) {
                    if (count($specificAction->subSpecificFormulations) > 0) {
                        foreach ($specificAction->subSpecificFormulations as $formulation) {
                            if ($formulation->assigned == true) {
                                $budgetProject->disabled = true;
                                if (!in_array($budgetProject, $records)) {
                                    array_push($records, $budgetProject);
                                }
                            } else {
                                $budgetProject->disabled = false;
                                if (!in_array($budgetProject, $records)) {
                                    array_push($records, $budgetProject);
                                }
                            }
                        }
                    } else {
                        $budgetProject->disabled = false;
                        if (!in_array($budgetProject, $records)) {
                            array_push($records, $budgetProject);
                        }
                    }
                }
            } else {
                $budgetProject->disabled = false;
                if (!in_array($budgetProject, $records)) {
                    array_push($records, $budgetProject);
                }
            }
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene los proyectos registrados
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador del proyecto a buscar, este parámetro es opcional
     * @return JSON        JSON con los datos de los proyectos
     */
    public function getProjects($id = null)
    {
        return response()->json(template_choices(
            BudgetProject::class,
            ['code', '-', 'name'],
            ($id) ? ['id' => $id] : [],
            true
        ));
    }

    /**
     * Método que devuelve un proyecto registrado según el id que se le pase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador del proyecto a buscar.
     * @return JSON        JSON con los datos del  proyecto específico.
     */
    public function getDetailProject($id)
    {
        $project = BudgetProject::find($id);
        $cargo = PayrollStaff::where("id", $project->payroll_staff_id)->first();

        return response()->json([
            'result' => true,
            'project' => $project,
            'cargo' =>  $cargo
        ], 200);
    }

    /**
     * Método que devuelve lo(s) proyecto(s) registrado que cuyas acciones específicas tengan asignado presupuesto
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     * @return JSON        JSON con los datos del o de los proyectos.
     */
    public function getBudgetProjectsAssigned()
    {
        $project_assinegnet = [['id' => '', 'text' => 'Seleccione...']];
        $budgetProjectAssinegnets = BudgetProject::where('active', true)->with(['specificActions.subSpecificFormulations' => function ($query) {
                $query->where('assigned', true);
        }])->get();
        foreach ($budgetProjectAssinegnets as $budgetProjectAssinegnet) {
            if ($budgetProjectAssinegnet && count($budgetProjectAssinegnet->specificActions) > 0) {
                foreach ($budgetProjectAssinegnet->specificActions as $specificActions) {
                    if (count($specificActions->subSpecificFormulations) > 0 && !$this->searchProject($project_assinegnet, $budgetProjectAssinegnet->id)) {
                            array_push($project_assinegnet, [
                                'id' => $budgetProjectAssinegnet->id,
                                'text' => $budgetProjectAssinegnet->code . " - " . $budgetProjectAssinegnet->name
                            ]);
                    }
                }
            }
        }
        return response()->json($project_assinegnet);
    }

    /**
     * Método que devuelve true o false si el id del proyecto se ecuentra en el arrays de proyecto asignados
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     * @return bool       true/false.
     */
    public function searchProject($project_assinegnet, $id_project)
    {
        $search = false;
        if ($project_assinegnet) {
            foreach ($project_assinegnet as $assinegnet) {
                if ($assinegnet['id'] == $id_project) {
                    $search = true;
                }
            }
        }
        return $search;
    }
}
