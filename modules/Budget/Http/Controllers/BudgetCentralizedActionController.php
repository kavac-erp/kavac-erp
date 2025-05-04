<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\Budget\Models\Department;
use Modules\Budget\Models\Institution;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetCentralizedAction;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Carbon\Carbon;

/**
 * @class BudgetCentralizedActionController
 * @brief Controlador de Acciones Centralizadas
 *
 * Clase que gestiona las Acciones Centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetCentralizedActionController extends Controller
{
    use ValidatesRequests;

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
        $this->middleware('permission:budget.centralizedaction.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.centralizedaction.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.centralizedaction.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.centralizedaction.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra un listado de acciones centralizadas
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
     * Muestra el formulario para la creación de acciones centralizadas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => 'budget.centralized-actions.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(Institution::class, ['acronym'], ['active' => true]);

        /* Arreglo de opciones de departamentos a representar en la plantilla para su selección */
        $departments = template_choices(Department::class, ['acronym', '-', 'name'], ['active' => true]);

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
        ) : [];

        /* Arreglo de opciones de personal a representar en la plantilla para su selección */
        $staffs = (Module::has('Payroll') && Module::isEnabled('Payroll'))
        ? template_choices(
            \Modules\Payroll\Models\PayrollStaff::class,
            ['id_number', '-', 'full_name'],
            ['relationship' => 'payrollEmployment', 'where' => ['active' => true]]
        )
        : [];

        return view('budget::centralized_actions.create-edit-form', compact(
            'header',
            'institutions',
            'departments',
            'positions',
            'staffs'
        ));
    }

    /**
     * Registra información de la acción centralizada
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        $rules = [
            'institution_id' => ['required'],
            'department_id' => ['required'],
            'code' => ['required','unique:budget_centralized_actions'],
            'name' => ['required'],
            'payroll_position_id' => ['required'],
            'payroll_staff_id' => ['required'],
            'ca_description' => ['required'],
            'from_date' => ['required'],
            'to_date' => ['required'],
        ];

        $messages = [
            'institution_id.required'     => 'El campo institucion es obligatorio.',
            'department_id.required'     => 'El campo departamento es obligatorio.',
            'code.required'     => 'El campo código es obligatorio.',
            'code.unique' => 'El campo código ya ha sido registrado.',
            'name.required'     => 'El campo nombre es obligatorio.',
            'payroll_position_id.required'     => 'El campo cargo es obligatorio.',
            'payroll_staff_id.required'     => 'El campo responsable es obligatorio.',
            'ca_description.required' => 'El campo descripción es obligatorio. ',
            'from_date.required' => 'El campo fecha de inicio es obligatorio. ',
            'to_date.required' => 'El campo fecha de finalización es obligatorio. ',
        ];

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $rules['payroll_position_id'] = 'required';
            $rules['payroll_staff_id'] = 'required';
        }

        $this->validate($request, $rules, $messages);

        /* Registra el nuevo proyecto */
        BudgetCentralizedAction::create([
            'name' => $request->name,
            'code' => $request->code,
            'custom_date' => Carbon::now()->format('Y-m-d'),
            'active' => ($request->active !== null),
            'department_id' => $request->department_id,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'ca_description' => $request->ca_description,
            'payroll_position_id' => (
                !is_null($request->payroll_position_id)
            ) ? $request->payroll_position_id : null,
            'payroll_staff_id' => (
                !is_null($request->payroll_staff_id)
            ) ? $request->payroll_staff_id : null
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Muestra información de una acción centralizada
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la acción centralizada a mostrar
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario de edición de acciones centralizadas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @param  integer $id Identificador de la acción centralizada a modificar
     * @return Renderable
     */
    public function edit($id)
    {
        /* Objeto con información de la acción centralizada a modificar */
        $budgetCentralizedAction = BudgetCentralizedAction::find($id);
        $budgetCentralizedActionInstitucion = BudgetCentralizedAction::find($id)->department;

        $staffPosition = null;

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $staffPosition = \Modules\Payroll\Models\PayrollEmployment::query()
                ->where('payroll_staff_id', $budgetCentralizedAction->payroll_staff_id)
                ->first()
                ?->payroll_position;
        }

        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => ['budget.centralized-actions.update', $budgetCentralizedAction->id],
            'method' => 'PUT',
            'role' => 'form'
        ];

        /* Objeto con datos del modelo a modificar */
        $model = $budgetCentralizedAction;
        $model["institution_id"] = $budgetCentralizedActionInstitucion["institution_id"];

        /* Arreglo de opciones de instituciones a representar en la plantilla para su selección */
        $institutions = template_choices(
            'App\Models\Institution',
            ['acronym'],
            ['active' => true]
        );

        /* Arreglo de opciones de departamentos a representar en la plantilla para su selección */
        $departments = template_choices(
            'App\Models\Department',
            ['acronym', '-', 'name'],
            ['active' => true]
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
        ) : [];

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
        ) : [];

        return view('budget::centralized_actions.create-edit-form', compact(
            'header',
            'model',
            'institutions',
            'departments',
            'positions',
            'staffs'
        ));
    }

    /**
     * Actualiza información de una acción centralizada
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      Identificador de la acción centralizada a modificar
     *
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $rules = [
            'institution_id' => ['required'],
            'department_id' => ['required'],
            'code' => ['required'],
            'name' => ['required'],
        ];

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            $rules['payroll_position_id'] = 'required';
            $rules['payroll_staff_id'] = 'required';
        }

        $this->validate($request, $rules);

        /* Objeto con información de la acción centralizada a modificar */
        $budgetCentralizedAction = BudgetCentralizedAction::find($id);
        $budgetCentralizedAction->fill($request->all());
        $budgetCentralizedAction->save();

        $request->session()->flash('message', ['type' => 'update']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Elimina una acción centralizada
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id      Identificador de la acción centralizada a eliminar
     *
     * @return Renderable
     */
    public function destroy($id)
    {
        /* Objeto con información de la acción centralizada a eliminar */
        $budgetCentralizedAction = BudgetCentralizedAction::find($id);

        if ($budgetCentralizedAction) {
            $budgetCentralizedAction->delete();
        }

        return response()->json(['record' => $budgetCentralizedAction, 'message' => 'Success'], 200);
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
        /* Objeto con información de las acciones centralizadas */
        $centralizedActions = (
            $active !== null
        ) ? BudgetCentralizedAction::where('active', $active)->with([
            'payrollStaff',
            'specificActions.subSpecificFormulations'
        ])->get() : BudgetCentralizedAction::with([
            'payrollStaff',
            'specificActions.subSpecificFormulations'
        ])->get();

        $records = [];

        foreach ($centralizedActions as $centralizedAction) {
            if (count($centralizedAction->specificActions) > 0) {
                foreach ($centralizedAction->specificActions as $specificAction) {
                    if (count($specificAction->subSpecificFormulations) > 0) {
                        foreach ($specificAction->subSpecificFormulations as $formulation) {
                            if ($formulation->assigned == true) {
                                $centralizedAction->disabled = true;
                                if (!in_array($centralizedAction, $records)) {
                                    array_push($records, $centralizedAction);
                                }
                            } else {
                                $centralizedAction->disabled = false;
                                if (!in_array($centralizedAction, $records)) {
                                    array_push($records, $centralizedAction);
                                }
                            }
                        }
                    } else {
                        $centralizedAction->disabled = false;
                        if (!in_array($centralizedAction, $records)) {
                            array_push($records, $centralizedAction);
                        }
                    }
                }
            } else {
                $centralizedAction->disabled = false;
                if (!in_array($centralizedAction, $records)) {
                    array_push($records, $centralizedAction);
                }
            }
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene las acciones centralizadas registradas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la acción centralizada a buscar, este parámetro es opcional
     *
     * @return \Illuminate\Http\JsonResponse        JSON con los datos de las acciones centralizadas
     */
    public function getCentralizedActions($id = null)
    {
        return response()->json(template_choices(
            BudgetCentralizedAction::class,
            [
                'code',
                '-',
                'name'
            ],
            ($id) ? [
                'id' => $id
            ] : [],
            true
        ));
    }

    /**
     * Obtiene las acciones específicas registradas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id     Identificador de la acción centralizada a buscar, este parámetro es opcional
     *
     * @return \Illuminate\Http\JsonResponse        JSON con los datos de las acciones específicas
     */
    public function getDetailCentralizedActions($id = null)
    {
        $budget = BudgetCentralizedAction::find($id);
        $departments = Department::find($id);
        $cargo = [];

        if (Module::has('Payroll') && Module::isAvailable('Payroll')) {
            $cargo = \Modules\Payroll\Models\PayrollStaff::where("id", $budget->payroll_staff_id)->first();
        }

        return response()->json([
            'result' => true,
            'budget' =>  $budget,
            'cargo' =>  $cargo,
            'departments' =>  $departments
        ], 200);
    }

    /**
     * Obtiene las acciones centralizadas registradas cuyas acciones especifica tiene presupuesto asignado
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id     Identificador de la acción centralizada a buscar, este parámetro es opcional
     *
     * @return \Illuminate\Http\JsonResponse        JSON con los datos de las acciones centralizadas
     */
    public function getCentralizedActionsAssigneds($id = null)
    {
        $centralized_actions = [['id' => '', 'text' => 'Seleccione...']];
        $budgetCentralizedActionsAssigneds = BudgetCentralizedAction::where(
            'active',
            true
        )->with([
            'specificActions.subSpecificFormulations' => function ($query) {
                $query->where('assigned', true);
            }
        ])->get();

        foreach ($budgetCentralizedActionsAssigneds as $budgetCentralizedActionsAssigned) {
            if ($budgetCentralizedActionsAssigned  && count($budgetCentralizedActionsAssigned->specificActions) > 0) {
                foreach ($budgetCentralizedActionsAssigned->specificActions as $specificActions) {
                    if (
                        count($specificActions->subSpecificFormulations) > 0
                        && !$this->searchCentralizedActions($centralized_actions, $budgetCentralizedActionsAssigned->id)
                    ) {
                        array_push($centralized_actions, [
                            'id' => $budgetCentralizedActionsAssigned->id,
                            'text' => $budgetCentralizedActionsAssigned->code
                            . " - " . $budgetCentralizedActionsAssigned->name
                        ]);
                    }
                }
            }
        }
        return response()->json($centralized_actions);
    }

    /**
     * Método que devuelve true o false si el id de la acción centralizada se
     * ecuentra en el arrays de acciones centralizadas asignados.
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve> | <pedrobui@gmail.com>
     *
     * @param  array $centralized_actions_assinegnet    Array de acciones centralizadas asignadas
     * @param  integer $id_centralized_actions         ID de la acción centralizada
     *
     * @return bool
     */
    public function searchCentralizedActions($centralized_actions_assinegnet, $id_centralized_actions)
    {
        $search = false;
        if ($centralized_actions_assinegnet) {
            foreach ($centralized_actions_assinegnet as $assinegnet) {
                if ($assinegnet['id'] == $id_centralized_actions) {
                    $search = true;
                }
            }
        }
        return $search;
    }
}
