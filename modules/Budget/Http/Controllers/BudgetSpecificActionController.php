<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use Modules\Budget\Models\BudgetProject;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetSpecificAction;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Budget\Models\BudgetCentralizedAction;
use Modules\Budget\Models\BudgetModificationAccount;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Budget\Models\BudgetSubSpecificFormulation;

/**
 * @class BudgetSpecificActionController
 * @brief Controlador de Acciones Específicas
 *
 * Clase que gestiona las Acciones Específicas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSpecificActionController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con información de los proyectos registrados
     *
     * @var array $projects
     */
    public $projects;

    /**
     * Arreglo con información de las acciones centralizadas registradas
     *
     * @var array $centralized_actions
     */
    public $centralized_actions;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validate_rules
     */
    public $validate_rules;

    /**
     * Arreglo con los mensajes de error de cada campo de un formulario
     *
     * @var array $validate_messages
     */
    public $validate_messages;

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
        $this->middleware('permission:budget.specificaction.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.specificaction.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.specificaction.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.specificaction.delete', ['only' => 'destroy']);

        /* Arreglo de opciones de proyectos a representar en la plantilla para su selección */
        $this->projects = template_choices(BudgetProject::class, ['code', '-', 'name'], ['active' => true]);

        /* Arreglo de opciones de acciones centralizadas a representar en la plantilla para su selección */
        $this->centralized_actions = template_choices(
            BudgetCentralizedAction::class,
            ['code', '-', 'name'],
            ['active' => true]
        );

        /* Define las reglas de validación para el formulario */
        $this->validate_rules = [
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after:from_date'],
            'code' => ['required', 'unique:budget_specific_actions'],
            'name' => ['required'],
            'description' => ['required'],
            'project_centralized_action' => ['required'],
            'project_id' => ['required_if:project_centralized_action,project'],
            'centralized_action_id' => ['required_if:project_centralized_action,centralized_action']
        ];

        /* Define los mensajes de error para el formulario */
        $this->validate_messages = [
            'from_date.required' => 'El campo fecha de inicio es obligatorio.',
            'from_date.date' => 'El campo fecha de inicio no tiene un formato válido.',
            'to_date.required' => 'El campo fecha final es obligatorio.',
            'to_date.after' => 'El campo fecha de finalización debe ser una fecha posterior a la fecha de inicio.',
            'to_date.date' => 'El campo fecha final no tiene un formato válido.',
            'code.required' => 'El campo código es obligatorio.',
            'code.unique' => 'El campo código ya ha sido registrado.',
            'project_centralized_action.required' => 'Debe indicar si el registro es para un proyecto o acción centralizada.',
            'project_id.required_if' => 'Debe seleccionar un proyecto.',
            'centralized_action_id.required_if' => 'Debe seleccionar una acción centralizada'
        ];
    }

    /**
     * Muestra un listado de acciones específicas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        return view('budget::index');
    }

    /**
     * Muestra el formulario para crear una acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function create()
    {
        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => 'budget.specific-actions.store',
            'method' => 'POST',
            'role' => 'form',
            'class' => 'form-horizontal',
        ];

        /* Arreglo de opciones de proyectos a representar en la plantilla para su selección */
        $projects = $this->projects;
        $projects_date = BudgetProject::get();
        $centralized_actions_date = BudgetCentralizedAction::get();
        /* Arreglo de opciones de acciones centralizadas a representar en la plantilla para su selección */
        $centralized_actions = $this->centralized_actions;

        return view('budget::specific_actions.create-edit-form', compact(
            'header',
            'projects',
            'centralized_actions',
            'projects_date',
            'centralized_actions_date'
        ));
    }

    /**
     * Registra información de la acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validate_rules, $this->validate_messages);

        /* Crea una acción específica */
        $budgetSpecificAction = new BudgetSpecificAction([
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'code' => $request->code,
            'name' => $request->name,
            'description' => $request->description,
            'active' => ($request->active !== null),
        ]);

        if ($request->project_centralized_action === "project") {
            /* Objeto que contiene información de un proyecto */
            $pry_acc = BudgetProject::find($request->project_id);
        } elseif ($request->project_centralized_action === "centralized_action") {
            /* Objeto que contiene información de una acción centralizada */
            $pry_acc = BudgetCentralizedAction::find($request->centralized_action_id);
        }
        $pry_acc->specificActions()->save($budgetSpecificAction);

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Muestra información de una acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function show()
    {
        return view('budget::show');
    }

    /**
     * Muestra el formulario para la edición de una acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la acción específica a modificar
     *
     * @return Renderable
     */
    public function edit($id)
    {
        /* Objeto con información de la acción específica a modificar */
        $BudgetSpecificAction = BudgetSpecificAction::find($id);

        /* Arreglo de opciones a implementar en el formulario */
        $header = [
            'route' => [
                'budget.specific-actions.update',
                $BudgetSpecificAction->id
            ],
            'method' => 'PUT',
            'role' => 'form'
        ];
        /* Objeto con datos del modelo a modificar */
        $model = $BudgetSpecificAction;
        /* Arreglo de opciones de proyectos a representar en la plantilla para su selección */
        $projects = $this->projects;
        /* Arreglo de opciones de acciones centralizadas a representar en la plantilla para su selección */
        $centralized_actions = $this->centralized_actions;

        $projects_date = BudgetProject::get();
        $centralized_actions_date = BudgetCentralizedAction::get();

        return view('budget::specific_actions.create-edit-form', compact(
            'header',
            'model',
            'projects',
            'centralized_actions',
            'projects_date',
            'centralized_actions_date',
        ));
    }

    /**
     * Actualiza información de la acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id Identificador de la acción específica a modificar
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $request['active'] = $request['active'] ?? false;
        $this->validate(
            $request,
            [
                'from_date' => ['required', 'date'],
                'to_date' => ['required', 'date'],
                'code' => ['required'],
                'name' => ['required'],
                'description' => ['required'],
            ],
            [
                'from_date.required' => 'El campo fecha de inicio es obligatorio.',
                'from_date.date' => 'El campo fecha de inicio no tiene un formato válido.',
                'to_date.required' => 'El campo fecha final es obligatorio.',
                'to_date.date' => 'El campo fecha final no tiene un formato válido.',
                'code.required' => 'El campo código es obligatorio.',
            ]
        );

        if ($request->project_centralized_action === "project") {
            /* Objeto que contiene información de un proyecto */
            $pry_acc = BudgetProject::find($request->project_id);
            $specificable_type = BudgetProject::class;
        } elseif ($request->project_centralized_action === "centralized_action") {
            /* Objeto que contiene información de una acción centralizada */
            $pry_acc = BudgetCentralizedAction::find($request->centralized_action_id);
            $specificable_type = BudgetCentralizedAction::class;
        }

        /* Objeto con información de la acción específica a modificar */
        $budgetSpecificAction = BudgetSpecificAction::find($id);
        $budgetSpecificAction->fill($request->all());
        $budgetSpecificAction->specificable_type = $specificable_type;
        $budgetSpecificAction->specificable_id = $pry_acc->id;
        $budgetSpecificAction->save();
        $request->session()->flash('message', ['type' => 'update']);
        return redirect()->route('budget.settings.index');
    }

    /**
     * Elimina una acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificador de la acción específica a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto con información de la acción específica a eliminar */
        $budgetSpecificAction = BudgetSpecificAction::find($id);

        if ($budgetSpecificAction) {
            $budgetSpecificAction->delete();
        }

        return response()->json(['record' => $budgetSpecificAction, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene listado de registros
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  boolean $active Filtrar por estatus del registro, valores permitidos true o false,
     *                         este parámetro es opcional.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList($active = null)
    {
        /* Objeto con información de las acciones específicas registradas */
        $specificActions = ($active !== null)
            ? BudgetSpecificAction::where('active', $active)->with(['specificable', 'subSpecificFormulations'])->get()
            : BudgetSpecificAction::with(['specificable', 'subSpecificFormulations'])->get();

        $records = [];

        foreach ($specificActions as $specificAction) {
            if (count($specificAction->subSpecificFormulations) > 0) {
                foreach ($specificAction->subSpecificFormulations as $formulation) {
                    if ($formulation->assigned == true) {
                        $specificAction->disabled = true;
                        array_push($records, $specificAction);
                    } else {
                        $specificAction->disabled = false;
                        array_push($records, $specificAction);
                    }
                }
            } else {
                $specificAction->disabled = false;
                array_push($records, $specificAction);
            }
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene las acciones específicas registradas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  string  $type   Identifica si la acción a buscar es por proyecto o acción centralizada
     * @param  integer $id     Identificador de la acción centralizada a buscar, este parámetro es opcional
     * @param  string  $source Fuente de donde se realiza la consulta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSpecificActions($type, $id, $source = null)
    {
        /* Arreglo con información de las acciones específicas */
        $data = [['id' => '', 'text' => 'Seleccione...']];
        $specificActions = [];

        if ($type === "Project") {
            /* Objeto con las acciones específicas asociadas a un proyecto */
            $specificActions = BudgetProject::find($id)->specificActions()->where('active', true)->get();
        } elseif ($type == "CentralizedAction") {
            /* Objeto con las acciones específicas asociadas a una acción centralizada */
            $specificActions = BudgetCentralizedAction::find($id)->specificActions()->where('active', true)->get();
        }

        foreach ($specificActions as $specificAction) {
            /* Objeto que determina si la acción específica ya fue formulada para el último presupuesto */
            $existsFormulation = BudgetSubSpecificFormulation::where([
                'budget_specific_action_id' => $specificAction->id,
                'assigned' => true
            ])->orderBy('year', 'desc')->first();

            if ($source === 'report') {
                if ($existsFormulation) {
                    array_push($data, [
                        'id' => $specificAction->id,
                        'text' => $specificAction->code . " - " . $specificAction->name
                    ]);
                }
            } else {
                if (!$existsFormulation) {
                    array_push($data, [
                        'id' => $specificAction->id,
                        'text' => $specificAction->code . " - " . $specificAction->name
                    ]);
                }
            }
        }

        return response()->json($data);
    }

    /**
     * Obtiene todas las acciones específicas agrupadas por Proyectos y Acciones Centralizadas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param string $formulated_year Año de formulación por el cual filtrar la información
     * @param boolean $formulated     Indica si se debe validar con una formulación de presupuesto
     * @param  integer $institutionId Identificador de la institución
     * @param string $selDate         Fecha en la cual se esta realizando la consulta
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGroupAllSpecificActions(
        $formulated_year = '',
        $formulated = null,
        $institutionId = null,
        $selDate = null
    ) {
        if ($formulated_year && strlen($formulated_year) > 4) {
            try {
                $formulated_year = Crypt::decrypt($formulated_year);
            } catch (DecryptException $e) {
                Log::error($e->getMessage());
            }
        }
        /* Arreglo que contiene las acciones específicas agrupadas por proyectos */
        $dataProjects = ['text' => 'Proyectos', 'children' => []];
        /* Arreglo que contiene las acciones específicas agrupadas por acciones centralizadas */
        $dataCentralizedActions = ['text' => 'Acciones Centralizadas', 'children' => []];

        /* Arreglo que contiene las acciones específicas */
        $data = [['id' => '', 'text' => 'Seleccione...']];

        $budgetSpecificAction = BudgetSpecificAction::has('specificable');

        if (!is_null($selDate)) {
            $budgetSpecificAction = $budgetSpecificAction->where(
                'from_date',
                '>=',
                $selDate
            )->where('to_date', '<=', $selDate);
        }

        /* Objeto que contiene información de las acciones específicas */
        $budgetSpecificAction = ($formulated_year)
            ? $budgetSpecificAction->whereYear('from_date', $formulated_year)
            ->orWhereYear('to_date', $formulated_year)
            : $budgetSpecificAction;

        /* Objeto con información de las acciones específicas a consultar */
        $budgetSpecificAction = (is_null($institutionId))
            ? $budgetSpecificAction
            : $budgetSpecificAction->whereHas('specificable', function ($q) use ($institutionId) {
                $q->whereHas('department', function ($qq) use ($institutionId) {
                    $qq->where('institution_id', $institutionId);
                });
            });

        $sp_accs = $budgetSpecificAction->get();

        $withoutFormulations = false;
        $hasFormulations = false;

        /* Agrega las acciones específicas para cada grupo */
        foreach ($sp_accs as $sp_acc) {
            $filter = (!is_null($formulated) && $formulated) ? BudgetSubSpecificFormulation::where(
                [
                    'year' => $formulated_year,
                    'budget_specific_action_id' => $sp_acc->id,
                    'assigned' => true
                ]
            )->first() : '';

            if (str_contains($sp_acc->specificable_type, 'BudgetProject') && !is_null($filter)) {
                array_push($dataProjects['children'], [
                    'id' => $sp_acc->id,
                    'text' => "{$sp_acc->specificable->code} - {$sp_acc->code} | {$sp_acc->name}"
                ]);
            } elseif (str_contains($sp_acc->specificable_type, 'BudgetCentralizedAction') && !is_null($filter)) {
                array_push($dataCentralizedActions['children'], [
                    'id' => $sp_acc->id,
                    'text' => "{$sp_acc->specificable->code} - {$sp_acc->code} | {$sp_acc->name}"
                ]);
            } elseif (!is_null($formulated) && $formulated && is_null($filter) && !$withoutFormulations) {
                $withoutFormulations = true;
                array_push($data, ['id' => '', 'text' => 'Sin formulaciones registradas', 'children' => []]);
            }
        }

        /* Si el grupo Proyectos contiene registros los agrega a la lista */
        if (count($dataProjects['children']) > 0) {
            array_push($data, $dataProjects);
        }
        /* Si el grupo Acciones Centralizadas contiene registros los agrega a la lista */
        if (count($dataCentralizedActions['children']) > 0) {
            array_push($data, $dataCentralizedActions);
        }

        /* Si existen proyectos o acciones centralizadas y el arreglo tiene el texto 'sin formulaciones' se elimina del arreglo */
        if (($key = array_search('Sin formulaciones registradas', array_column($data, 'text'))) !== false) {
            array_splice($data, $key, 1);
        }

        return response()->json($data);
    }

    /**
     * Obtiene detalles de una acción específica
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  integer $id Identificar de la acción específica de la cual se requiere información
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetail($id)
    {
        return response()->json([
            'result' => true, 'record' => BudgetSpecificAction::where('id', $id)->with('specificable')->first()
        ], 200);
    }

    /**
     * Listado de cuentas presupuestarias aperturadas para una acción específica
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer              $specificActionId    Identificador de la acción específica
     * @param     string               $selDate             Fecha a partir de la cual buscar las cuentas aperturadas
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getOpenedAccounts($specificActionId, $selDate)
    {
        list($year, $month, $day) = explode("-", $selDate);
        $records = [['id' => '', 'text' => 'Seleccione...']];
        $anulationStatus = DocumentStatus::where('action', 'AN')->first();

        $accounts = [];

        $openedAccounts = BudgetAccountOpen::query()
            ->with([
                'budgetAccount',
                'subSpecificFormulation'
            ])
            ->whereHas('budgetAccount', function ($q) {
                $q->where('specific', '!=', '00');
            })
            ->whereHas('subSpecificFormulation', function ($q) use ($year, $specificActionId) {
                $q
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($qq) use ($specificActionId) {
                        $qq->where('id', $specificActionId);
                    });
            })
            ->get();

        foreach ($openedAccounts as $account) {
            if (!array_key_exists($account->budget_account_id, $accounts)) {
                $accounts[$account->budget_account_id]['id'] = $account->budget_account_id;
                $accounts[$account->budget_account_id]['amount'] = $account->total_year_amount;
                $accounts[$account->budget_account_id]['budgetAccount'] = $account->budgetAccount;
                $accounts[$account->budget_account_id]['currency'] = $account->subSpecificFormulation->currency;
            } else {
                $accounts[$account->budget_account_id]['amount'] += $account->total_year_amount;
            }
        }

        $modificationAccounts = BudgetModificationAccount::query()
            ->with(['budgetAccount', 'budgetSubSpecificFormulation.specificAction'])
            ->whereHas('budgetSubSpecificFormulation', function ($query) use ($specificActionId, $year) {
                $query
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($q) use ($specificActionId) {
                        $q->where('id', $specificActionId);
                    });
            })
            ->whereHas('budgetAccount', function ($qq) {
                $qq->where('specific', '!=', '00');
            })
            ->get();

        foreach ($modificationAccounts as $account) {
            if ($account->operation == 'I') {
                if (!array_key_exists($account->budget_account_id, $accounts)) {
                    $accounts[$account->budget_account_id]['id'] = $account->budget_account_id;
                    $accounts[$account->budget_account_id]['amount'] = $account->amount;
                    $accounts[$account->budget_account_id]['budgetAccount'] = $account->budgetAccount;
                    $accounts[$account->budget_account_id]['currency'] = $account->budgetSubSpecificFormulation->currency;
                } else {
                    $accounts[$account->budget_account_id]['amount'] += $account->amount;
                }
            } elseif ($account->operation == 'D') {
                if (array_key_exists($account->budget_account_id, $accounts)) {
                    $accounts[$account->budget_account_id]['amount'] -= $account->amount;
                }
            }
        }

        $compromiseAccounts = BudgetCompromiseDetail::query()
            ->whereHas('budgetSubSpecificFormulation', function ($query) use ($specificActionId, $year) {
                $query
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($q) use ($specificActionId) {
                        $q->where('id', $specificActionId);
                    });
            })
            ->whereHas('budgetAccount', function ($qq) {
                $qq->where('specific', '!=', '00');
            })
            ->toBase()
            ->get();

        foreach ($compromiseAccounts as $account) {
            if (
                array_key_exists($account->budget_account_id, $accounts) &&
                $account->document_status_id != $anulationStatus->id
            ) {
                $accounts[$account->budget_account_id]['amount'] -= $account->amount;
            }
        }

        foreach ($accounts as $key => $account) {
            if ($key > 0) {
                $accounts[$key]['amount'] = number_format(
                    $account['amount'],
                    $account['currency']->decimal_places,
                    ".",
                    ""
                );

                $accounts[$key]['text'] = $account['budgetAccount']->code . ' - ' .
                    $account['budgetAccount']->denomination . ' (' .
                    $account['currency']->symbol . " " .
                    number_format(
                        $account['amount'],
                        $account['currency']->decimal_places,
                        ",",
                        "."
                    ) . ')';
            }
        }

        ksort($accounts);

        foreach ($accounts as $account) {
            $records[] = $account;
        }

        return response()->json(['result' => true, 'records' => $records], 200);
    }

    /**
     * Listado de cuentas presupuestarias aperturadas para una acción específica
     *
     * @author     Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer              $specificActionId    Identificador de la acción específica
     * @param     string               $selDate             Fecha a partir de la cual buscar las cuentas aperturadas
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getOpenedTaxAccounts($specificActionId, $selDate)
    {
        list($year, $month, $day) = explode("-", $selDate);
        $records = [];
        $accounts = [];
        $anulationStatus = DocumentStatus::where('action', 'AN')->first();

        $openedAccounts = BudgetAccountOpen::query()
            ->with([
                'budgetAccount',
                'subSpecificFormulation'
            ])
            ->whereHas('budgetAccount', function ($q) {
                $q
                    ->where('specific', '!=', '00')
                    ->where('disaggregate_tax', true);
            })
            ->whereHas('subSpecificFormulation', function ($q) use ($year, $specificActionId) {
                $q
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($qq) use ($specificActionId) {
                        $qq->where('id', $specificActionId);
                    });
            })
            ->get();

        foreach ($openedAccounts as $account) {
            if (!array_key_exists($account->budget_account_id, $accounts)) {
                $accounts[$account->budget_account_id]['id'] = $account->budget_account_id;
                $accounts[$account->budget_account_id]['amount'] = $account->total_year_amount;
                $accounts[$account->budget_account_id]['code'] = $account->budgetAccount->code;
            } else {
                $accounts[$account->budget_account_id]['amount'] += $account->total_year_amount;
            }
        }

        $modificationAccounts = BudgetModificationAccount::query()
            ->with(['budgetAccount', 'budgetSubSpecificFormulation.specificAction'])
            ->whereHas('budgetSubSpecificFormulation', function ($query) use ($specificActionId, $year) {
                $query
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($q) use ($specificActionId) {
                        $q->where('id', $specificActionId);
                    });
            })
            ->whereHas('budgetAccount', function ($qq) {
                $qq
                    ->where('specific', '!=', '00')
                    ->where('disaggregate_tax', true);
            })
            ->get();

        foreach ($modificationAccounts as $account) {
            if ($account->operation == 'I') {
                if (!array_key_exists($account->budget_account_id, $accounts)) {
                    $accounts[$account->budget_account_id]['id'] = $account->budget_account_id;
                    $accounts[$account->budget_account_id]['amount'] = $account->amount;
                    $accounts[$account->budget_account_id]['code'] = $account->budgetAccount->code;
                } else {
                    $accounts[$account->budget_account_id]['amount'] += $account->amount;
                }
            } elseif ($account->operation == 'D') {
                if (array_key_exists($account->budget_account_id, $accounts)) {
                    $accounts[$account->budget_account_id]['amount'] -= $account->amount;
                }
            }
        }

        $compromiseAccounts = BudgetCompromiseDetail::query()
            ->whereHas('budgetSubSpecificFormulation', function ($query) use ($specificActionId, $year) {
                $query
                    ->where('year', $year)
                    ->whereHas('specificAction', function ($q) use ($specificActionId) {
                        $q->where('id', $specificActionId);
                    });
            })
            ->whereHas('budgetAccount', function ($qq) {
                $qq
                    ->where('specific', '!=', '00')
                    ->where('disaggregate_tax', true);
            })
            ->toBase()
            ->get();

        foreach ($compromiseAccounts as $account) {
            if (
                array_key_exists($account->budget_account_id, $accounts) &&
                $account->document_status_id != $anulationStatus->id
            ) {
                $accounts[$account->budget_account_id]['amount'] -= $account->amount;
            }
        }

        ksort($accounts);

        foreach ($accounts as $account) {
            $records[] = $account;
        }
        return response()->json(['result' => true, 'records' => $records], 200);
    }

    /**
     * Metodo que retorna el monto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @param object $account_id Objeto con información de la cuenta
     *
     * @return float Monto del compromiso para la cuenta presupuestaria con 'id' $account_id
     *
     */

    public function getAccountCompromisedAmout(object $accout_id)
    {
        $compromised = BudgetCompromiseDetail::where('budget_sub_specific_formulation_id', $accout_id->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $accout_id->budget_account_id)->get();
        $amout = 0;
        $descriptions = array();
        if (!$compromised->isEmpty()) {
            foreach ($compromised as $com) {
                $amout = $com->getTotalAttribute();
                array_push($descriptions, $com->description);
            }
            $accout_id['compromised_descriptions'] = $descriptions;
            return $amout;
        }
        return $amout;
    }

    /**
     * Metodo que retorna el monto del impuesto comprometido para la cuenta $account_id
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param object $account_id Objeto con información de la cuenta
     *
     * @return float Monto del compromiso para la cuenta presupuestaria con 'id' $account_id
     */

    public function getAccountCompromisedTaxAmount(object $accout_id)
    {
        $compromised = BudgetCompromiseDetail::where('budget_sub_specific_formulation_id', $accout_id->budget_sub_specific_formulation_id)
            ->where('budget_account_id', $accout_id->budget_account_id)->get();
        $amout = 0;
        if (!$compromised->isEmpty()) {
            foreach ($compromised as $com) {
                $amout = $com->tax_amount;
            }
            return $amout;
        }
        return $amout;
    }
}
