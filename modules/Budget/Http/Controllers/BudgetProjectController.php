<?php

namespace Modules\Budget\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\Budget\Models\Department;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetProject;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class BudgetProjectController
 * @brief Controlador de Proyectos
 *
 * Clase que gestiona los Proyectos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetProjectController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validate_rules
     */
    public $validate_rules;

    /**
     * Arreglo con los mensajes de error de cada campo de un formulario
     *
     * @var array $messages
     */
    public $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:budget.project.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.project.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.project.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.project.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
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

        /* Define los mensajes de error para el formulario */
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
     *
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
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => 'budget.projects.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
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

        /* Arreglo de opciones de departamentos a representar en la plantilla para su selección */
        $departments = template_choices(Department::class, ['acronym', '-', 'name'], ['active' => true]);

        /*  Arreglo de opciones de cargos a representar en la plantilla para su selección */
        $positions = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices(
            \Modules\Payroll\Models\PayrollPosition::class,
            'name',
        ) : ['' => 'Seleccione...'];

        /* Arreglo de opciones de personal a representar en la plantilla para su selección */
        $staffs = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices(
            \Modules\Payroll\Models\PayrollStaff::class,
            ['id_number', '-', 'full_name'],
            ['relationship' => 'payrollEmployment', 'where' => ['active' => true]]
        ) : ['' => 'Seleccione...'];

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
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validate_rules, $this->messages);

        /* Registra el nuevo proyecto */
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
     *
     * @param  integer $id Identificador del proyecto a mostrar
     *
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
     *
     * @param  integer $id Identificador del proyecto a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* Objeto con información del proyecto a modificar */
        $budgetProject = BudgetProject::find($id);
        $budgetProjectInstitucion = BudgetProject::find($id)->department;

        $staffPosition = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? \Modules\Payroll\Models\PayrollEmployment::query()
        ->where('payroll_staff_id', $budgetProject->payroll_staff_id)
        ->first()
        ->payroll_position : null;

        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => ['budget.projects.update', $budgetProject->id],
            'method' => 'PUT',
            'role' => 'form'
        ];

        /* Objeto con datos del modelo a modificar */
        $model = $budgetProject;

        $model["institution_id"] = $budgetProjectInstitucion["institution_id"];

        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(Institution::class, [
            'acronym',
            '-',
            'name'
        ]);

        /* Arreglo de opciones de departamentos a representar en la plantilla para su selección */
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

        /* Arreglo de opciones de cargos a representar en la plantilla para su selección */
        $positions = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices(
            \Modules\Payroll\Models\PayrollPosition::class,
            'name',
            [
                'relationship' => 'payrollEmployments',
                'where' => [
                    'payroll_employment_payroll_position.active' => true
                ]
            ]
        ) : ['' => 'Seleccione...'];

        if (!is_null($staffPosition)) {
            $positions[$staffPosition->id] = $staffPosition->name;
        }


        /* Arreglo de opciones de personal a representar en la plantilla para su selección */
        $staffs = (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? template_choices(
            \Modules\Payroll\Models\PayrollStaff::class,
            ['id_number', '-', 'full_name'],
            ['relationship' => 'payrollEmployment', 'where' => ['active' => true]]
        ) : ['' => 'Seleccione...'];

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
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id Identificador del proyecto a modificar
     *
     * @return \Illuminate\Http\RedirectResponse
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

        /* Objeto con información del proyecto a modificar */
        $budgetProject = BudgetProject::find($id);
        $budgetProject->fill($request->all());
        /* Establece si el proyecto esta o no activo */
        $budgetProject->active = $request->active ?? false;
        $budgetProject->save();

        $request->session()->flash('message', ['type' => 'update']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Elimina un proyecto específico
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador del proyecto a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto con información del proyecto a eliminar */
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
     *
     * @param  boolean $active  Filtrar por estatus del registro, valores permitidos true o false,
     *                          este parámetro es opcional.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList($active = null)
    {
        /* Objeto con información de los proyectos registrados */
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
     *
     * @param  integer $id Identificador del proyecto a buscar, este parámetro es opcional
     *
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @param  integer $id Identificador del proyecto a buscar.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetailProject($id)
    {
        $project = BudgetProject::find($id);
        $cargo = (
            Module::has("Payroll") && Module::isEnabled("Payroll")
        ) ? \Modules\Payroll\Models\PayrollStaff::where("id", $project->payroll_staff_id)->first() : [];

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
     *
     * @return \Illuminate\Http\JsonResponse
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
     *
     * @return bool
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
