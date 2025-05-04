<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Source;
use App\Models\Receiver;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Modules\Payroll\Models\PayrollConceptAssignOption;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Actions\GetPayrollConceptParameters;
use Modules\Payroll\Repositories\PayrollAssociatedParametersRepository;

/**
 * @class      PayrollConceptController
 * @brief      Controlador de conceptos
 *
 * Clase que gestiona los conceptos
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        //$this->middleware('permission:payroll.concept.list',   ['only' => 'index']);
        $this->middleware('permission:payroll.concept.create', ['only' => 'store']);
        $this->middleware('permission:payroll.concept.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.concept.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'unique:payroll_concepts,name'],
            'payroll_concept_type_id' => ['required'],
            'institution_id' => ['required'],
            'assign_to' => ['required'],
            'currency_id' => ['required'],
            'is_strict' => ['required'],


            'budget_specific_action_id' => ['required_if:check_both_fields,1'],
            'budget_account_id' => ['required_if:check_both_fields,1'],
            'accounting_account_id' => ['required_if:check_both_fields,1'],

            'check_both_fields' => ['required'],
        ];
        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'payroll_concept_type_id.required' => 'El campo tipo de concepto es obligatorio.',
            'institution_id.required' => 'El campo organización es obligatorio.',
            'assign_to.required' => 'El campo "¿asignar a?" es obligatorio.',
            'payroll_salary_tabulator_id.required' => 'El campo tabulador salarial es obligatorio.',
            'formula.required' => 'El campo fórmula es obligatorio.',
            'currency_id.required' => 'El campo moneda es obligatorio',
            'is_strict.required' => 'El campo reglas es obligatorio',
            'budget_specific_action_id.required_if' => 'El campo Acción específica es obligatorio.',
            'budget_account_id.required_if' => 'El campo Cuenta presupuestaria es obligatorio.',
            'accounting_account_id.required_if' => 'El campo Cuenta contable es obligatorio.',
            'check_both_fields.required' => 'No se puede seleccionar ambas opciones.',

        ];
    }

    /**
     * Muestra un listado de los conceptos registradas (activos e inactivos)
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index(Request $request)
    {
        /* Objeto asociado al modelo PayrollConcept*/
        $payrollConcepts = PayrollConcept::query()
            ->with(
                'payrollSalaryTabulator',
                'payrollConceptAssignOptions',
                'budgetSpecificAction',
                'budgetAccount',
                'accountingAccount'
            );
        if (!empty($request->query('query')) && $request->query('query') !== "{}") {
            $payrollConcepts = $payrollConcepts->search($request->query('query'));
        }
        if ($request->orderBy) {
            $payrollConcepts = $payrollConcepts->orderBy($request->orderBy, ($request->ascending) ? 'asc' : 'desc');
        }
        $records = $payrollConcepts->paginate($request->limit ?? 10);
        $items = $records?->items() ?? [];

        foreach ($items as $payrollConcept) {
            $assign_to = json_decode($payrollConcept->assign_to);
            $assign_options = [];
            if ($assign_to) {
                foreach ($assign_to as $field) {
                    if ($field->type) {
                        $key = $field->id;
                        $options = [];
                        if ($payrollConcept->payrollConceptAssignOptions) {
                            foreach ($payrollConcept->payrollConceptAssignOptions as $assign_option) {
                                if ($key == $assign_option['key']) {
                                    if ($field->type == 'range') {
                                        $options = json_decode($assign_option['value']);
                                    } elseif ($field->type == 'list') {
                                        $option = $field->model::find($assign_option['assignable_id']);
                                        if (isset($option)) {
                                            array_push(
                                                $options,
                                                [
                                                    'id' => $assign_option['assignable_id'],
                                                    'text' => $option->name ?? ($option->fullName ?? '')
                                                ]
                                            );
                                        }
                                    }
                                }
                            }
                            $assign_options[$field->id] = $options;
                        }
                    }
                }
            }
            $payrollConcept->assign_to = $assign_to;
            $payrollConcept->assign_options = (object) json_decode(json_encode($assign_options));

            $source = Source::with('receiver.associateable')->where('sourceable_id', $payrollConcept->id)
                ->where('sourceable_type', PayrollConcept::class)->first();

            $payrollConcept->receiver = null;

            if ($source) {
                $text = $source->receiver->description . (!empty($source->receiver->associateable?->code)
                    ? (' - ' . $source->receiver->associateable->code)
                    : '');
                $payrollConcept->receiver = [
                    'id' => $source->receiver->id,
                    'text' => $text,
                    'class' => $source->receiver->receiverable_type,
                    'group' => $source->receiver->group,
                    'description' => $source->receiver->description ?? '',
                    'accounting_account_id' => $source->receiver->associateable_id ?? null,
                    'accounting_account' => $source->receiver->associateable?->code ?? ''
                ];
            }
        }
        return response()->json([
            'data' => $items,
            'count' => $records->total(),
        ], 200, [], env('APP_DEBUG') == true ? JSON_PRETTY_PRINT : 0);
    }

    /**
     * Valida y registra un nuevo concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request     $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $checkBothFields =
            ($request->input('budget_centralized_action_id') == '' ^ $request->input('budget_project_id') == '');
        $request->merge(['check_both_fields' => $checkBothFields]);
        $validateRules = $this->validateRules;
        $this->validate($request, $validateRules, $this->messages);

        foreach ($request->assign_to as $assign_to) {
            if ($assign_to['type'] == 'range' && $request->assign_options[$assign_to['id']]) {
                if ($assign_to['id'] != 'all_staff_according_start_date') {
                    $validateRules = array_merge($validateRules, [
                        "assign_options." . $assign_to['id'] . ".minimum" => ['required'],
                    ]);
                    $this->messages = array_merge(
                        $this->messages,
                        [
                            "assign_options." . $assign_to['id'] . ".minimum.required" =>
                            'El campo rango minimo perteneciente a ' . $assign_to['name'] . ' es obligatorio.',
                        ]
                    );
                }
                $validateRules = array_merge($validateRules, [
                    "assign_options." . $assign_to['id'] . ".maximum" => ['required'],
                ]);

                $field = ('all_staff_according_start_date' == $assign_to['id'])
                    ? 'a partir del año de servicio'
                    : 'rango máximo';
                $this->messages = array_merge($this->messages, [
                    "assign_options." . $assign_to['id'] . ".maximum.required" =>
                    'El campo ' . $field . ' perteneciente a ' . $assign_to['name'] . ' es obligatorio.',
                ]);
            } elseif ($assign_to['type'] == 'list') {
                $validateRules = array_merge($validateRules, [
                    "assign_options." . $assign_to['id'] . ".0.id" => ['required'],
                ]);
                $this->messages = array_merge($this->messages, [
                    "assign_options." . $assign_to['id'] . ".0.id.required" =>
                    'El campo ' . $assign_to['name'] . ' es obligatorio.',
                ]);
            }
        }

        $this->validate($request, $validateRules, $this->messages);

        if (!$this->validateFormula($request->formula)) {
            return response()->json(['errors' => ['formula' => ['El formato de lo formula es incorrecto']]], 422);
        };

        $payrollConcept = PayrollConcept::create([
            'name' => $request->name,
            'description' => $request->description ?? '',
            'active' => !empty($request->active)
                ? $request->active
                : false,
            'arc' => !empty($request->arc)
                ? $request->arc
                : false,
            'formula' => $request->formula,
            'institution_id' => $request->institution_id,
            'payroll_concept_type_id' => $request->payroll_concept_type_id,
            'accounting_account_id' => $request->accounting_account_id,
            'budget_account_id' => $request->budget_account_id,
            'budget_project_id' => $request->budget_centralized_action_id
                ? null
                : $request->budget_project_id,
            'budget_centralized_action_id' => $request->budget_project_id
                ? null :
                $request->budget_centralized_action_id,
            'budget_specific_action_id' => $request->budget_specific_action_id,
            'assign_to' => json_encode($request->assign_to),
            'currency_id' => $request->currency_id,
            'is_strict' => $request->is_strict,
        ]);
        $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        if (!empty($request->receiver)) {
            $receiver = Receiver::updateOrCreate(
                [
                    'id' => $request->receiver['id'] ?? null,
                ],
                [
                    'receiverable_type' => $request->receiver['class'] ?? null,
                    'receiverable_id' => $request->receiver['id'] ?? null,
                    'associateable_type' => $existAccounting
                        ? \Modules\Accounting\Models\AccountingAccount::class
                        : null,
                    'associateable_id' => $existAccounting ? $request->receiver_account : null,
                    'group' => $request->receiver['group'],
                    'description' => $request->receiver['description'] ?? $request->receiver['text']
                ]
            );

            Source::updateOrCreate(
                [
                    'sourceable_type' => PayrollConcept::class,
                    'sourceable_id' => $payrollConcept->id
                ],
                [
                    'receiver_id' => $receiver->id
                ]
            );
        }

        foreach ($request->assign_to as $assign_to) {
            if ($assign_to['type'] == 'range') {
                PayrollConceptAssignOption::create([
                    'key' => $assign_to['id'],
                    'value' => json_encode($request->assign_options[$assign_to['id']]),
                    'applicable_type' => PayrollConcept::class,
                    'applicable_id' => $payrollConcept->id,
                ]);
            } elseif ($assign_to['type'] == 'list') {
                foreach ($request->assign_options[$assign_to['id']] as $assign_option) {
                    /* Objeto asociado al modelo PayrollConceptAssignOption */
                    $payrollConceptAssignOption = PayrollConceptAssignOption::create([
                        'key' => $assign_to['id'],
                        'applicable_type' => PayrollConcept::class,
                        'applicable_id' => $payrollConcept->id,
                    ]);
                    /* Se guarda la información en el campo morphs */
                    $assignModel = $assign_to['optionModel'] ?? $assign_to['model'];
                    $option = $assignModel::find($assign_option['id']);
                    $option->payrollConceptAssignOptions()->save($payrollConceptAssignOption);
                }
            }
        };
        return response()->json(['record' => $payrollConcept, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de una asignación salarial
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        /* Objeto con la información del concepto a editar asociado al modelo PayrollConcept */
        $payrollConcept = PayrollConcept::find($id);

        $checkBothFields =
            ($request->input('budget_centralized_action_id') == '' ^ $request->input('budget_project_id') == '');
        $request->merge(['check_both_fields' => $checkBothFields]);
        $validateRules = $this->validateRules;
        $validateRules = array_replace(
            $validateRules,
            ['name' => ['required', 'unique:payroll_concepts,name,' . $payrollConcept->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        foreach ($request->assign_to as $assign_to) {
            if ($assign_to['type'] == 'range' && $request->assign_options[$assign_to['id']]) {
                if ($assign_to['id'] != 'all_staff_according_start_date') {
                    $validateRules = array_merge($validateRules, [
                        "assign_options." . $assign_to['id'] . ".minimum" => ['required'],
                    ]);
                    $this->messages = array_merge($this->messages, [
                        "assign_options." . $assign_to['id'] . ".minimum.required" =>
                        'El campo rango minimo perteneciente a ' . $assign_to['name'] . ' es obligatorio.',
                    ]);
                }
                $validateRules = array_merge($validateRules, [
                    "assign_options." . $assign_to['id'] . ".maximum" => ['required'],
                ]);

                $field = ('all_staff_according_start_date' == $assign_to['id'])
                    ? 'a partir del año de servicio'
                    : 'rango máximo';
                $this->messages = array_merge($this->messages, [
                    "assign_options." . $assign_to['id'] . ".maximum.required" =>
                    'El campo ' . $field . ' perteneciente a ' . $assign_to['name'] . ' es obligatorio.',
                ]);
            } elseif ($assign_to['type'] == 'list') {
                $validateRules = array_merge($validateRules, [
                    "assign_options." . $assign_to['id'] . ".0.id" => ['required'],
                ]);
                $this->messages = array_merge($this->messages, [
                    "assign_options." . $assign_to['id'] . ".0.id.required" =>
                    'El campo ' . $assign_to['name'] . ' es obligatorio.',
                ]);
            }
        }
        $this->validate($request, $validateRules, $this->messages);

        if (!$this->validateFormula($request->formula)) {
            return response()->json(['errors' => ['formula' => ['El formato de lo formula es incorrecto']]], 422);
        };

        $payrollConcept->name = $request->name;
        $payrollConcept->description = $request->description ?? '';
        $payrollConcept->active = !empty($request->active)
            ? $request->active
            : false;
        $payrollConcept->arc = !empty($request->arc)
            ? $request->arc
            : false;
        $payrollConcept->formula = $request->formula;
        $payrollConcept->institution_id = $request->institution_id;
        $payrollConcept->payroll_concept_type_id = $request->payroll_concept_type_id;
        $payrollConcept->accounting_account_id = $request->accounting_account_id;
        $payrollConcept->budget_account_id = $request->budget_account_id;
        $payrollConcept->budget_project_id = $request->budget_centralized_action_id
            ? null :
            $request->budget_project_id;
        $payrollConcept->budget_centralized_action_id = $request->budget_project_id
            ? null :
            $request->budget_centralized_action_id;
        $payrollConcept->budget_specific_action_id = $request->budget_specific_action_id;
        $payrollConcept->assign_to = json_encode($request->assign_to);
        $payrollConcept->currency_id = $request->currency_id;
        $payrollConcept->is_strict = $request->is_strict;
        $payrollConcept->save();

        $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        if (!empty($request->receiver)) {
            $receiver = Receiver::updateOrCreate(
                [
                    'id' => $request->receiver['id'] ?? null,
                ],
                [
                    'group' => $request->receiver['group'],
                    'description' => $request->receiver['description'] ?? $request->receiver['text'],
                    'receiverable_type' => $request->receiver['class'] ?? null,
                    'receiverable_id' => $request->receiver['receiverable_id'] ?? null,
                    'associateable_type' => $existAccounting
                        ? \Modules\Accounting\Models\AccountingAccount::class
                        : null,
                    'associateable_id' => $existAccounting ? $request->receiver_account : null,
                ]
            );

            Source::updateOrCreate(
                [
                    'sourceable_type' => PayrollConcept::class,
                    'sourceable_id' => $payrollConcept->id
                ],
                [
                    'receiver_id' => $receiver->id
                ]
            );
        }
        /* Se eliminan las opciones de asignación asociadas al concepto */
        $assignOptions = PayrollConceptAssignOption::query()
            ->where('applicable_type', PayrollConcept::class)
            ->where('applicable_id', $payrollConcept->id)
            ->get();
        foreach ($assignOptions as $assignOption) {
            $assignOption->forceDelete();
        }

        /* Se agregan las nuevas opciones de asignación asociadas al concepto */
        foreach ($request->assign_to as $assign_to) {
            if ($assign_to['type'] == 'range') {
                PayrollConceptAssignOption::create([
                    'key' => $assign_to['id'],
                    'value' => json_encode($request->assign_options[$assign_to['id']]),
                    'applicable_type' => PayrollConcept::class,
                    'applicable_id' => $payrollConcept->id,
                ]);
            } elseif ($assign_to['type'] == 'list') {
                foreach ($request->assign_options[$assign_to['id']] as $assign_option) {
                    /* Objeto asociado al modelo PayrollConceptAssignOption */
                    $payrollConceptAssignOption = PayrollConceptAssignOption::create([
                        'key' => $assign_to['id'],
                        'applicable_type' => PayrollConcept::class,
                        'applicable_id' => $payrollConcept->id,
                    ]);
                    /* Se guarda la información en el campo morphs */
                    $assignModel = $assign_to['optionModel'] ?? $assign_to['model'];
                    $option = $assignModel::find($assign_option['id']);
                    $option->payrollConceptAssignOptions()->save($payrollConceptAssignOption);
                }
            }
        };
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina un concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del concepto a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        /* Objeto con la información del concepto a eliminar asociado al modelo PayrollConcept */
        $payrollConcept = PayrollConcept::find($id);
        $payrollConcept->delete();
        return response()->json(['record' => $payrollConcept, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de conceptos registrados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getPayrollConcepts()
    {
        return template_choices('Modules\Payroll\Models\PayrollConcept', ['name'], ['active' => true], true);
    }

    /**
     * Obtiene las opciones a asignar registrados asociadas a un concepto
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    mixed    Listado de los registros a mostrar
     */
    public function getPayrollConceptAssignTo()
    {
        $assignTo = new PayrollAssociatedParametersRepository();
        return $assignTo->loadData('assignTo');
    }

    /**
     * Obtiene la lista de opciones de acuerdo al parametro seleccionado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array|void    Listado de los registros a mostrar
     */
    public function getPayrollConceptAssignOptions($id)
    {
        $assignTo = new PayrollAssociatedParametersRepository();
        foreach ($assignTo->loadData('assignTo') as $field) {
            if ($field['type'] == 'list') {
                if ($field['id'] == $id) {
                    if (isset($field['optionModel'])) {
                        return template_choices($field['optionModel'], $field['optionField'] ?? 'name', '', true);
                    }
                }
            }
        }
    }

    /**
     * Obtiene la lista de personal asignado en un concepto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getPayrollPersonalConceptAssign(
        int $id,
        Request $request,
        GetPayrollConceptParameters $conceptParameters
    ) {
        return $conceptParameters->getPayrollPersonalConceptAssign($id, $request->payroll_id);
    }

    /**
     * Obtiene el concepto asociado a un parametro
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    integer    concepto a mostrar
     */
    public function getPayrollConceptParameter($idParameter)
    {
        //Consulta del concepto con un parametro especifico
        $payrollConcepts = PayrollConcept::where('formula', 'like', '%parameter(' . $idParameter . ')%')->get();

        return $payrollConcepts[0]['id'];
    }

    /**
     * Método que valida que la formula a agregar en el concepto sea una formula con un formato válido
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    boolean Define si es una formula válida
     */
    public function validateFormula($formula)
    {
        $parameters = new PayrollAssociatedParametersRepository();
        $typesParameters = [
            'associatedBenefit',
            'associatedVacation',
            'associatedWorkerFile',
            'parameter',
            'concept',
            'tabulator',
            'ari_register'
        ];

        foreach ($typesParameters as $typeParameter) {
            if (in_array($typeParameter, ['parameter', 'concept', 'tabulator', 'ari_register'])) {
                if ($typeParameter == 'parameter') {
                    $types = Parameter::where(
                        [
                            'required_by' => 'payroll',
                            'active' => true,
                        ]
                    )->where('p_key', 'like', 'global_parameter_%')->get();
                    foreach ($types as $type) {
                        $jsonValue = json_decode($type->p_value);
                        $formula = str_replace('parameter(' . $jsonValue->id . ')', '1', $formula);
                    }
                } elseif ($typeParameter == 'concept') {
                    $types = PayrollConcept::all();
                    foreach ($types as $type) {
                        $formula = str_replace('concept(' . $type['id'] . ')', '1', $formula);
                    }
                } elseif ($typeParameter == 'tabulator') {
                    $types = PayrollSalaryTabulator::all();
                    foreach ($types as $type) {
                        $formula = str_replace('tabulator(' . $type['id'] . ')', '1', $formula);
                    }
                } elseif ($typeParameter == 'ari_register') {
                    $formula = str_replace('ari_register', '1', $formula);
                }
            } else {
                $types = $parameters->loadData($typeParameter);
                foreach ($types as $type) {
                    if (empty($type['children'])) {
                        $formula = str_replace($type['id'], '1', $formula);
                    } else {
                        foreach ($type['children'] as $children) {
                            $formula = str_replace($children['id'], '1', $formula);
                        }
                    }
                }
            }
        }

        $exploded = multiexplode(
            [
                'if', '(', ')', '{', '}',
                '==', '<=', '>=', '<', '>', '!=',
                '+', '-', '*', '/', 'select', 'case',
                'then', 'when', 'else', 'end', ';', '1', '2',
                '3', '4', '5', '6', '7', '8', '9', '0', '.', ','
            ],
            $formula
        );

        foreach ($exploded as $exp) {
            if (trim($exp) != '') {
                return false;
            }
        }

        try {
            if (!is_numeric(str_eval($formula))) {
                throw new \Exception('El formato de lo formula es incorrecto', 1);
            }
        } catch (\Exception $error) {
            Log::error($error->getMessage());
            return false;
        }

        return true;
    }

    /**
     * Obtiene los registros de las cuentas patrimoniales asociadas a una cuenta presupuestaria
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     *
     * @return array con la información de las cuentas formateada
     */
    public function getPayrollConceptAccountingAccounts($account_id = null)
    {
        /* Arreglo que almacenara la lista de cuentas patrimoniales */
        $records = [];
        /* se realiza la busqueda de manera ordenada en base al código */
        if (Module::has('Accounting') && isset($account_id)) {
            $account = \Modules\Accounting\Models\Accountable::query()
                ->with('accountingAccount')
                ->where('accountable_id', $account_id)
                ->first();

            if (isset($account)) {
                array_push($records, [
                    'id' => $account->accountingAccount->id,
                    'code' => $account->accountingAccount->code,
                    'denomination' => $account->accountingAccount->denomination,
                    'active' => $account->accountingAccount->active,
                    'original' => $account->accountingAccount->original,
                    'text' => "{$account->accountingAccount->code} - {$account->denomination}",
                    'parent' => $account->accountingAccount->parent,
                ]);
            }
        }
        return $records;
    }

    /**
     * Obtiene los registros de las cuentas patrimoniales asociadas a una cuenta contable
     *
     * @author  Pedro Buitrago <pbuitrago@cenditel.gob.ve | pedrobui@gmail.com>
     *
     * @return array con la información de las cuentas formateada
     */
    public function getPayrollConceptAccountable($account_id = null)
    {
        /* arreglo que almacenara la lista de cuentas patrimoniales */
        $records = [];
        /* se realiza la busqueda de manera ordenada en base al código */
        if (Module::has('Accounting') && isset($account_id)) {
            $account = \Modules\Accounting\Models\Accountable::query()
                ->with('accountingAccount')
                ->where('accounting_account_id', $account_id)
                ->first();

            if (isset($account)) {
                array_push($records, [
                    'id' => $account->accountingAccount->id,
                    'code' => $account->accountingAccount->code,
                    'denomination' => $account->accountingAccount->denomination,
                    'active' => $account->accountingAccount->active,
                    'original' => $account->accountingAccount->original,
                    'text' => "{$account->accountingAccount->code} - {$account->denomination}",
                    'parent' => $account->accountingAccount->parent,
                ]);
            }
        }
        return $records;
    }
}
