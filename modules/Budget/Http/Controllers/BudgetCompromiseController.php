<?php

namespace Modules\Budget\Http\Controllers;

use App\Models\Tax;
use App\Models\Source;
use App\Models\Receiver;
use App\Models\FiscalYear;
use App\Models\HistoryTax;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\DocumentStatus;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Support\Facades\Log;
use Nwidart\Modules\Facades\Module;
use App\Repositories\ReportRepository;
use Modules\Budget\Models\BudgetStage;
use Modules\Budget\Models\Institution;
use Modules\Budget\Models\BudgetCompromise;
use Illuminate\Contracts\Support\Renderable;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetSpecificAction;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Illuminate\Foundation\Validation\ValidatesRequests;

class BudgetCompromiseController extends Controller
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
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'institution_id' => ['required'],
            'compromised_at' => ['required', 'date'],
            'source_document' => [
                'required',
                'max:20',
                Rule::unique('budget_compromises', 'document_number')
                ->whereNot(
                    'document_status_id',
                    DocumentStatus::where('action', 'AN')->first()->id
                )
            ],
            'description' => ['required'],
            'accounts.*.account_id' => ['required'],
            'accounts.*.specific_action_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo institución es obligatorio.',
            'compromised_at.required' => 'El campo fecha es obligatorio.',
            'source_document.required' => 'El campo documento origen es obligatorio.',
            'source_document.max' => 'El campo documento origen no debe de tener más de 250 carácteres.',
            'source_document.unique' => 'El campo documento origen ya ha sido registrado.',
            'description.required' => 'El campo descripción es obligatorio.',
            // 'accounts.*.specific_action_id.required' => 'El campo acción específica es obligatorio',
            // 'accounts.*.account_id.required' => 'El campo cuenta es obligatorio',
        ];
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:budget.compromise.index', ['only' => 'index']);
        $this->middleware('permission:budget.compromise.store', ['only' => 'store']);
        $this->middleware('permission:budget.compromise.update', ['only' => 'update']);
        $this->middleware('permission:budget.compromise.destroy', ['only' => 'destroy']);
        $this->middleware('permission:budget.compromise.cancel', ['only' => 'cancelBudgetCompromise']);
        $this->middleware('permission:budget.compromise.approve', ['only' => 'approveBudgetCompromise']);
    }

    /**
     * Muestra la lista de compromisos
     *
     * @return Renderable
     */
    public function index()
    {
        return view('budget::compromises.list');
    }

    /**
     * Muestra el formulario para crear un nuevo compromiso
     *
     * @return Renderable
     */
    public function create()
    {
        return view('budget::compromises.create-edit-form');
    }

    /**
     * Almacena información del compromiso
     *
     * @param Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');

        if ($request->compromised_manual == true) {
            $this->validateRules['receiver'] = ['required'];
            $this->validateRules['accounting_account_id'] = $existAccounting ? ['required'] : ['nullable'];

            $this->messages['receiver.required'] = 'El campo beneficiario es obligatorio.';

            if ($existAccounting) {
                $this->messages['accounting_account_id.required'] = 'El campo cuenta contable es obligatorio.';
            }
        }

        for ($i = 0; $i < count($request->input('accounts')); $i++) {
            $this->messages['accounts.' . $i . '.account_id.required'] = 'El campo cuenta con ID: ' . $request->input('accounts.' . $i . '.budget_tax_key') . ' es obligatorio.';
            $this->messages['accounts.' . $i . '.specific_action_id.required'] = 'El campo acción específica con ID: ' . $request->input('accounts.' . $i . '.budget_tax_key') . ' es obligatorio.';
        }

        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::query()->where("model", BudgetCompromise::class)->first();

        if (!$codeSetting) {
            return response()->json(
                [
                    'result' => false,
                    'message' => [
                        'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                        'text' => 'Debe configurar previamente el formato para el código a generar',
                    ]
                ],
                200
            );
        }

        $year = $request->fiscal_year ?? date("Y");

        $codeSetting = CodeSetting::where("model", BudgetCompromise::class)->first();

        if (!$codeSetting) {
            return response()->json(
                [
                    'result' => false,
                    'message' => [
                        'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                        'text' => 'Debe configurar previamente el formato para el código a generar',
                    ]
                ],
                200
            );
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])
            ->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
            substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
            $currentFiscalYear->year : $year),
            BudgetCompromise::class,
            'code'
        );

        $compromisedYear = explode("-", $request->compromised_at)[0];

        $codeStage = generate_registration_code('STG', '00000000', 'YYYY', BudgetStage::class, 'code');

        DB::transaction(
            function () use ($request, $code, $codeStage, $compromisedYear, $existAccounting) {
                /* Proceso de transacción para obtener estado del documento. Estado inicial del compromiso establecido a PROCESADO / Por Aprobar */
                $documentStatus = DocumentStatus::withTrashed()->where('action', 'PR')->first();

                /* Datos del compromiso */
                $compromise = BudgetCompromise::create(
                    [
                        'document_number' => $request->source_document,
                        'institution_id' => $request->institution_id,
                        'compromised_at' => $request->compromised_at,
                        'description' => $request->description,
                        'code' => $code,
                        'document_status_id' => $documentStatus->id,
                    ]
                );

                if ($request->receiver != '' && $request->receiver != null) {
                    $receiver = Receiver::updateOrCreate(
                        [
                            'receiverable_type' => $request->receiver['class'],
                            'receiverable_id' => $request->receiver['class']
                                == BudgetCompromise::class ? $compromise->id : $request->receiver['id'],
                            'associateable_type' => $existAccounting
                                ? \Modules\Accounting\Models\AccountingAccount::class : null,
                            'associateable_id' => $existAccounting ? $request->accounting_account_id : null
                        ],
                        [
                            'group' => $request->receiver['group'],
                            'description' => $request->receiver['text']
                        ]
                    );

                    $source = Source::create(
                        [
                        'receiver_id' => $receiver->id,
                        'sourceable_type' => BudgetCompromise::class,
                        'sourceable_id' => $compromise->id,
                        ]
                    );
                }

                $total = 0;
                $totalEdit = 0;
                /* Gestiona los ítems del compromiso */
                foreach ($request->accounts as $account) {
                    $spac = BudgetSpecificAction::find($account['specific_action_id']);
                    $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();
                    $tax = (isset($account['account_tax_id']) || isset($account['tax_id']))
                    ? Tax::find($account['account_tax_id'] ?? $account['tax_id'])
                    : new Tax();
                    $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
                    $taxAmount = ($account['amount'] * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;

                    $compromise->budgetCompromiseDetails()->create([
                        'description' => $account['description'],
                        'amount' => $account['amount'],
                        'tax_amount' => $taxAmount,
                        'tax_id' => $account['account_tax_id'] ?? $account['tax_id'],
                        'budget_account_id' => $account['account_id'],
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_tax_key' => $account['budget_tax_key'],
                    ]);
                    $total += ($account['amount'] + $taxAmount);
                    $totalEdit = ($account['amount'] + $taxAmount);

                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $account['account_id'])
                        ->first();
                    if ($budgetAccountOpen != null) {
                        $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $totalEdit;
                        $budgetAccountOpen->save();
                    }
                }

                foreach ($request->tax_accounts as $taxAccount) {
                    $spac = BudgetSpecificAction::find($taxAccount['specific_action_id']);
                    $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();

                    $compromise->budgetCompromiseDetails()->create([
                        'description' => $taxAccount['description'],
                        'amount' => $taxAccount['amount'],
                        'tax_amount' => $taxAccount['amount'],
                        'tax_id' => $taxAccount['account_tax_id'] ?? $taxAccount['tax_id'],
                        'budget_account_id' => $taxAccount['account_id'],
                        'budget_sub_specific_formulation_id' => $formulation->id,
                        'budget_tax_key' => $taxAccount['budget_tax_key'],
                    ]);

                    $total += $taxAccount['amount'];
                    $totalEdit = $taxAccount['amount'];

                    $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                        ->where('budget_account_id', $taxAccount['account_id'])
                        ->first();
                    if ($budgetAccountOpen != null) {
                        $budgetAccountOpen->total_year_amount_m = $budgetAccountOpen->total_year_amount_m - $totalEdit;
                        $budgetAccountOpen->save();
                    }
                }

                $compromise->budgetStages()->create([
                    'code' => $code,
                    'registered_at' => $request->compromised_at,
                    'type' => 'COM',
                    'amount' => $total,
                ]);
                $request->session()->flash('message', ['type' => 'store']);
            }
        );

        return response()->json(['result' => true, 'redirect' => route('budget.compromises.index')], 200);
    }

    /**
     * Obtiene los detalles de un compromiso
     *
     * @return JsonResponse
     */
    public function show($id)
    {
        $budget_compromise = BudgetCompromise::find($id);

        return response()->json([
            'result' => true,
            'budget_compromise' => $budget_compromise,
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @author Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @param $id Identificador del registro
     *
     * @return Renderable
     */
    public function edit($id)
    {
        $budgetCompromise = BudgetCompromise::with(
            ['budgetCompromiseDetails' => function ($query) {
                $query->with(
                    ['budgetSubSpecificFormulation' => function ($query) {
                        $query->with(
                            ['specificAction' => function ($query) {
                            }
                            ]
                        );
                    }
                    ]
                );
            }
            ]
        )
        ->where('document_status_id', DocumentStatus::where('action', 'PR')->first()->id)
        ->find($id);

        if (!$budgetCompromise) {
            abort(403);
        }

        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            $payOrder = \Modules\Finance\Models\FinancePayOrder::query()
                ->where('document_sourceable_id', $id)
                ->where('document_sourceable_type', BudgetCompromise::class)
                ->where('document_status_id', '!=', DocumentStatus::where('action', 'AN')->first()->id)
                ->first();

            if ($payOrder) {
                abort(403);
            }
        }

        $source = Source::with('receiver.associateable')->where('sourceable_id', $id)
            ->where('sourceable_type', BudgetCompromise::class)->first();
        $receiver = null;

        if ($source) {
            $receiver = $source->receiver;
        }

        return view('budget::compromises.create-edit-form', compact('budgetCompromise', 'receiver'));
    }

    /**
     * Actualiza los datos de un compromiso
     *
     * @author Francisco Escala <fescala@cenditel.gob.ve>
     *
     * @param Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        $year = $request->fiscal_year ?? date("Y");

        $compromisedYear = explode("-", $request->compromised_at)[0];
        $documentStatus = DocumentStatus::where('action', 'PR')->first(); //PROCESADO//Por Aprobar

        $budget = BudgetCompromise::find($request->id);

        $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');

        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            $payOrder = \Modules\Finance\Models\FinancePayOrder::query()
                ->where('document_sourceable_id', $budget->id)
                ->where('document_sourceable_type', BudgetCompromise::class)
                ->where('document_status_id', '!=', DocumentStatus::where('action', 'AN')->first()->id)
                ->first();

            if ($payOrder) {
                $errors = [
                    'pay_order_exist' => [
                        0 => 'No es posible modificar el compromiso debido a que ya tiene pagos asociados.'
                    ]
                ];

                return response()->json(['message' => 'The given data was invalid.','errors' => $errors], 422);
            }
        }

        if ($request->compromised_manual == true) {
            $this->validateRules['receiver'] = ['required'];
            $this->validateRules['accounting_account_id'] = $existAccounting ? ['required'] : ['nullable'];
        }

        $this->validateRules['source_document'] = [
            'required',
            Rule::unique('budget_compromises', 'document_number')->whereNot(
                'document_status_id',
                DocumentStatus::where('action', 'AN')->first()->id
            )->ignore($budget->id)
        ];
        for ($i = 0; $i < count($request->input('accounts')); $i++) {
            $this->messages['accounts.' . $i . '.account_id.required'] = 'El campo cuenta con ID: ' . $request->input('accounts.' . $i . '.budget_tax_key') . ' es obligatorio.';
            $this->messages['accounts.' . $i . '.specific_action_id.required'] = 'El campo acción específica con ID: ' . $request->input('accounts.' . $i . '.budget_tax_key') . ' es obligatorio.';
        }
        $this->validate($request, $this->validateRules, $this->messages);

        $budget->document_number = $request->source_document;
        $budget->institution_id = $request->institution_id;
        $budget->compromised_at = $request->compromised_at;
        $budget->description = $request->description;
        $budget->document_status_id = $documentStatus->id;
        $budget->save();

        if ($request->receiver != '' && $request->receiver != null) {
            $receiver = Receiver::updateOrCreate(
                [
                    'receiverable_type' => $request->receiver['class'],
                    'receiverable_id' => $request->receiver['class']
                        == BudgetCompromise::class ? $budget->id : $request->receiver['id'],
                    'associateable_type' => $existAccounting
                        ? \Modules\Accounting\Models\AccountingAccount::class : null,
                    'associateable_id' => $existAccounting ? $request->accounting_account_id : null
                ],
                [
                    'group' => $request->receiver['group'],
                    'description' => $request->receiver['text']
                ]
            );

            $source = Source::updateOrCreate(
                [
                    'receiver_id' => $receiver->id
                ],
                [
                    'sourceable_type' => BudgetCompromise::class,
                    'sourceable_id' => $budget->id
                ]
            );
        }

        $total = 0;
        $totalEdit = 0;

        /* Gestiona los ítems del compromiso */
        $deleted = BudgetCompromiseDetail::where('budget_compromise_id', $request->id)->delete();

        foreach ($request->accounts as $account) {
            $spac = BudgetSpecificAction::find($account['specific_action_id']);
            $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();
            $tax = (isset($account['account_tax_id']) || isset($account['tax_id']))
            ? Tax::find($account['account_tax_id'] ?? $account['tax_id'])
            : new Tax();
            $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
            $taxAmount = ($account['amount'] * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;

            $budget->budgetCompromiseDetails()->Create(
                [
                    'description' => $account['description'],
                    'amount' => $account['amount'],
                    'tax_amount' => $taxAmount,
                    'tax_id' => $account['account_tax_id'] ?? $account['tax_id'],
                    'budget_account_id' => $account['account_id'],
                    'budget_sub_specific_formulation_id' => $formulation->id,
                    'budget_tax_key' => $account['budget_tax_key'],
                ]
            );
            $total += ($account['amount'] + $taxAmount);
            $totalEdit = ($account['amountEdit'] + $taxAmount);
            $totalOriginal = isset(
                $account['account_amount_original']
            ) ? ($account['account_amount_original'] + $taxAmount) : 0;

            $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                ->where('budget_account_id', $account['account_id'])
                ->first();

            if (isset($account['account_original'])) {
                $budgetAccountOpenOriginal = BudgetAccountOpen::where(
                    'budget_sub_specific_formulation_id',
                    $formulation->id
                )
                    ->where('budget_account_id', $account['account_original'])
                    ->first();
            }

            if ($budgetAccountOpen != null) {
                if ($account['operation'] == 'I') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                        ]
                    );
                } elseif ($account['operation'] == 'C') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m
                            - ($totalEdit < 0 ? $totalEdit * -1 : $totalEdit),
                        ]
                    );
                    if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null && $account['equal'] == 'N') {
                        $budgetAccountOpenOriginal->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m
                                + $totalOriginal,
                            ]
                        );
                    }
                } elseif ($account['operation'] == 'S') {
                    if ($account['equal'] == 'S') {
                            $budgetAccountOpen->update(
                                [
                                'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + $totalEdit,
                                ]
                            );
                    } else {
                            $budgetAccountOpen->update(
                                [
                                'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - $totalEdit,
                                ]
                            );

                        if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null) {
                            $budgetAccountOpenOriginal->update(
                                [
                                'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m
                                    + $totalOriginal,
                                ]
                            );
                        }
                    }
                } elseif ($account['operation'] == 'R') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m
                            - ($totalEdit < 0 ? $totalEdit * -1 : $totalEdit),
                        ]
                    );

                    if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null && $account['equal'] == 'N') {
                        $budgetAccountOpenOriginal->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m + $totalOriginal,
                            ]
                        );
                    }
                } else {
                    $budgetAccountOpen->total_year_amount_m
                        = $budgetAccountOpen->total_year_amount_m - ($account['amount'] + $taxAmount);
                    $budgetAccountOpen->save();
                }
            }
        }

        foreach ($request->tax_accounts as $taxAccount) {
            $spac = BudgetSpecificAction::find($taxAccount['specific_action_id']);
            $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();

            $budget->budgetCompromiseDetails()->Create(
                [
                    'description' => $taxAccount['description'],
                    'amount' => $taxAccount['amount'],
                    'tax_amount' => $taxAccount['amount'],
                    'tax_id' => $taxAccount['account_tax_id'] ?? $taxAccount['tax_id'],
                    'budget_account_id' => $taxAccount['account_id'],
                    'budget_sub_specific_formulation_id' => $formulation->id,
                    'budget_tax_key' => $taxAccount['budget_tax_key'],
                ]
            );
            $total += $taxAccount['amount'];
            $totalEdit = $taxAccount['amountEdit'];
            $totalOriginal = isset($taxAccount['account_amount_original']) && $taxAccount['account_amount_original'] !=
            $taxAccount['amount'] ? ($taxAccount['account_amount_original']) : 0;

            $budgetAccountOpen = BudgetAccountOpen::where('budget_sub_specific_formulation_id', $formulation->id)
                ->where('budget_account_id', $taxAccount['account_id'])
                ->first();

            if (isset($taxAccount['tax_account_original']) && $taxAccount['tax_account_original'] != $taxAccount['account_id']) {
                $budgetAccountOpenOriginal = BudgetAccountOpen::where(
                    'budget_sub_specific_formulation_id',
                    $formulation->id
                )
                    ->where('budget_account_id', $taxAccount['tax_account_original'])
                    ->first();
            }

            if ($budgetAccountOpen != null) {
                if ($taxAccount['operation'] == 'I') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m,
                        ]
                    );
                } elseif ($taxAccount['operation'] == 'C') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m
                            - ($totalEdit < 0 ? $totalEdit * -1 : $totalEdit),
                        ]
                    );
                    if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null && $taxAccount['equal'] == 'N') {
                        $budgetAccountOpenOriginal->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m
                                + $totalOriginal,
                            ]
                        );
                    }
                } elseif ($taxAccount['operation'] == 'S') {
                    if ($taxAccount['equal'] == 'S') {
                        $budgetAccountOpen->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + $totalEdit,
                            ]
                        );
                    } else {
                        $budgetAccountOpen->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m - $totalEdit,
                            ]
                        );

                        if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null) {
                            $budgetAccountOpenOriginal->update(
                                [
                                'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m
                                    + $totalOriginal,
                                ]
                            );
                        }
                    }
                } elseif ($taxAccount['operation'] == 'R') {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m
                            - ($totalEdit < 0 ? $totalEdit * -1 : $totalEdit),
                        ]
                    );

                    if (isset($budgetAccountOpenOriginal) && $budgetAccountOpenOriginal != null && $taxAccount['equal'] == 'N') {
                        $budgetAccountOpenOriginal->update(
                            [
                            'total_year_amount_m' => $budgetAccountOpenOriginal->total_year_amount_m
                                + $totalOriginal,
                            ]
                        );
                    }
                } else {
                    $budgetAccountOpen->total_year_amount_m
                        = $budgetAccountOpen->total_year_amount_m - ($taxAccount['amount']);
                    $budgetAccountOpen->save();
                }
            }
        }

        $budget->budgetStages()->update(
            [
            'type' => 'COM',
            'amount' => $total,
            ]
        );

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json(['result' => true, 'redirect' => route('budget.compromises.index')], 200);
    }

    /**
     * Elimina el compromiso de presupuesto
     *
     * @param $id Identificador del registro
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        /* Objeto con información del compromiso a eliminar */
        $budgetCompromise = BudgetCompromise::with('budgetStages')->find($id);
        $compromisedYear = explode("-", $budgetCompromise->compromised_at)[0];

        if (Module::has('Finance') && Module::isEnabled('Finance')) {
            $payOrder = \Modules\Finance\Models\FinancePayOrder::where(
                'document_sourceable_id',
                $budgetCompromise->id
            )->first();

            if ($payOrder) {
                abort(403);
            }
        }

        if ($budgetCompromise) {
            $budgetCompromiseDetails = BudgetCompromiseDetail::where('budget_compromise_id', $id)->get();

            foreach ($budgetCompromiseDetails as $budgetCompromiseDetail) {
                $formulation = $budgetCompromiseDetail->budgetSubSpecificFormulation()->where(
                    'year',
                    $compromisedYear
                )->first();
                $taxAmount = isset($budgetCompromiseDetail['tax_id']) ? $budgetCompromiseDetail['amount'] : 0;
                $total = $taxAmount != 0 ? $taxAmount : $budgetCompromiseDetail['amount'];

                $budgetAccountOpen = BudgetAccountOpen::with(
                    'budgetAccount'
                )->where('budget_sub_specific_formulation_id', $formulation->id)
                    ->where('budget_account_id', $budgetCompromiseDetail['budget_account_id'])
                    ->whereHas(
                        'budgetAccount',
                        function ($query) {
                            $query->where('specific', '!=', '00');
                        }
                    )->first();
                if (isset($budgetAccountOpen)) {
                    $budgetAccountOpen->update(
                        [
                        'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + $total,
                        ]
                    );
                }
            }

            if ($budgetCompromise->sourceable_type != '' && $budgetCompromise->sourceable_type != null) {
                $budgetCompromise->budgetStages[0]['type'] = 'PRE';
                $budgetCompromise->budgetStages[0]->save();
            } else {
                $budgetCompromise->delete();
            }
            BudgetCompromiseDetail::where('budget_compromise_id', $id)->delete();
        }

        return response()->json(['record' => $budgetCompromise, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return JsonResponse
     */
    public function vueList(Request $request)
    {
        $documentStatus = DocumentStatus::where('action', 'AN')->first(); //Estatus del documento Anulado
        $records = BudgetCompromise::query()
            ->with(
                [
                    'budgetCompromiseDetails' => function ($query) use ($documentStatus) {
                        $query
                            ->where('document_status_id', null)
                            ->orWhere('document_status_id', $documentStatus->id)
                            ->with(
                                [
                                    'budgetAccount',
                                    'budgetSubSpecificFormulation',
                                    'tax'
                                ]
                            );
                    },
                    'budgetStages',
                    'documentStatus',
                    'institution'
                ]
            )
            ->whereHas(
                'budgetStages',
                function ($query) {
                    $query
                        ->withTrashed()
                        ->where('type', 'COM');
                }
            )
            ->orderBy('id', 'asc')
            ->search($request->query('query'))
            ->paginate($request->limit ?? 10);

        foreach ($records->items() as $record) {
            $record->exist_pay_order = false;
            $record->status_pay_order = '';
            if (Module::has('Finance') && Module::isEnabled('Finance')) {
                $payOrder = \Modules\Finance\Models\FinancePayOrder::where(
                    'document_sourceable_id',
                    $record->id
                )->first();

                if ($payOrder) {
                    $record->exist_pay_order = true;
                    $record->status_pay_order = $payOrder->status_aux;
                }

                $record->is_banking_movement = $this->isBankingMovementApproved($record);
            }
        }

        return response()->json(
            [
                'data' => $records->items(),
                'count' => $records->total(),
                'cancelBudgetCompromisePermission' => auth()->user()->hasPermission('budget.compromise.cancel'),
                'approveBudgetCompromisePermission' => auth()->user()->hasPermission('budget.compromise.approve'),
            ],
            200
        );
    }

    /**
     * Obtiene las fuentes de documentos que aún no han sido comprometidos, solo (PRE)comprometidos y/o (PRO)gramados
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param integer $institution_id Identificador de la institución
     * @param string  $year           Año de ejercicio económico
     *
     * @return JsonResponse|Response
     */
    public function getDocumentSources($institution_id, $year)
    {
        /* Obtiene todos los registros de fuentes de documentos que aún no han sido comprometidos */
        $compromises = BudgetCompromise::query()
        ->where('institution_id', $institution_id)
        ->whereHas(
            'budgetStages',
            function ($query) {
                $query->where('type', 'PRE');
            }
        )
        ->with(
            [
            'budgetCompromiseDetails.budgetSubSpecificFormulation',
            'sourceable',
            'budgetStages',
            ]
        )
        ->get()
        ->filter(function ($compromise) {
            if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                if ($compromise->sourceable_type == "Modules\Payroll\Models\Payroll") {
                    $payroll = \Modules\Payroll\Models\Payroll::where([
                        "id" => $compromise->sourceable_id,
                        'code' => $compromise->document_number,
                    ])->first();

                    if ($payroll && $payroll->payrollPaymentPeriod && $payroll->payrollPaymentPeriod->availability_status == 'AP' && $payroll->payrollPaymentPeriod->payment_status == 'generated') {
                        return true;
                    }
                    return false;
                }
            }
            return true;
        })->values();

        return response(['records' => $compromises], 200);
    }

    /**
     * Valida que el monto de las cuentas no excendan el monto existente
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param Request $request campos que provienen del formulario
     *
     * @return Response
     */
    public function validateMaxAmount(Request $request)
    {
        $usedAmount = 0;
        $totalAccountAmount = $request->account_amount_original + $request->selected_account_amount;

        if (count($request->use_accounts) > 0) {
            foreach ($request->use_accounts as $account) {
                $account['account_id'] = $account['account_id'] ?? $account['budget_account_id'];
                if (is_numeric($account['account_id']) && $account['account_id'] == $request->account_id) {
                    $usedAmount += $account['amount'];
                }
            }
        }

        if (count($request->tax_accounts) > 0) {
            foreach ($request->tax_accounts as $account) {
                if (array_key_exists('account_id', $account)) {
                    $account['account_id'] = $account['account_id'] ?? $account['budget_account_id'];
                    if (is_numeric($account['account_id']) && $account['account_id'] == $request->account_id) {
                        $usedAmount += $account['amount'];
                    }
                }
            }
        }

        $taxAmountEdit = 0;
        $amountEdit = 0;

        if (is_numeric($request->editIndex)) {
            if ($request->tax_account_id) {
                $tax = (isset($request->tax_account_id))
                ? Tax::find($request->tax_account_id)
                : new Tax();
                $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
                $taxAmountEdit = ((float)$request->account_amount_original
                    * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;
            }

            if (count($request->use_accounts) > 0) {
                $amountEdit = $request->use_accounts[$request->editIndex]['amount'];
            }
        }

        $usedAmount = (float)$usedAmount - (float)$amountEdit - (float)$taxAmountEdit;
        $amount = (float)$request->account_amount + (float)$usedAmount;

        if (is_numeric($request->id) && is_numeric($request->editIndex)) {
            $amount = (float)$request->account_amount;
        }

        if ((string)$amount > (string)$totalAccountAmount) {
            return response(['result' => 'error'], 422);
        }

        return response(['result' => 'true'], 200);
    }

    /**
     * Valida que el monto de las cuentas de impuestos no excendan el monto existente
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param Request $request campos que provienen del formulario
     *
     * @return Response
     */
    public function validateMaxTaxAmount(Request $request)
    {
        $tax = (isset($request->tax_account_id))
        ? Tax::find($request->tax_account_id)
        : new Tax();
        $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
        $taxAmount = ($request->account_amount * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;
        $usedAmount = 0;

        if (count($request->use_accounts) > 0) {
            foreach ($request->use_accounts as $account) {
                if ($account['account_id'] == $request->account_id) {
                    $usedAmount += $account['amount'];
                }
            }
        }

        $taxAmountEdit = 0;

        if (is_numeric($request->editIndex)) {
            if ($request->tax_account_id) {
                $tax = (isset($request->tax_account_id))
                ? Tax::find($request->tax_account_id)
                : new Tax();
                $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
                $taxAmountEdit = ((float)$request->account_amount_original
                    * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;
            }
        }
        $usedAmount = (float)$usedAmount - (float)$taxAmountEdit;

        if ((float)$usedAmount + (float)$taxAmount > (float)$request->selected_account_amount) {
            return response(['result' => 'error'], 422);
        }

        return response(['result' => 'true'], 200);
    }

    /**
     * Genera el reporte de compromiso base para su visualización
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param $id Identificador del registro
     *
     * @return void
     */
    public function pdf($id)
    {
        /**
         * [$pdf base para generar el pdf]
         *
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        $pdf = new ReportRepository();
        $documentStatus = DocumentStatus::where('action', 'AN')->first(); //Estatus del documento Anulado
        $records = BudgetCompromise::with(
            [
            'budgetCompromiseDetails' => function ($query) use ($documentStatus) {
                $query
                    ->where('document_status_id', null)
                    ->orWhere('document_status_id', $documentStatus->id)
                    ->with(['budgetAccount', 'tax', 'budgetSubSpecificFormulation']);
            },
            'budgetStages',
            'documentStatus',
            'institution'
            ]
        )->find($id);

        $pdf->setConfig(
            [
            'institution' => Institution::first(),
            'urlVerify'   => url('/budget/compromise/pdf/' . $id)
            ]
        );

        $pdf->setHeader(
            'Reporte de compromiso '  . $records->code,
            'Información de compromiso'
        );
        $pdf->setFooter();

        $pdf->setBody(
            'budget::pdf.budgetCompromise',
            true,
            [
            'pdf'    => $pdf,
            'records' => $records,
            "report_date" => \Carbon\Carbon::today()->format('d-m-Y'),
            ]
        );
    }
    /**
     * Método que permite anular un compromiso
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function cancelBudgetCompromise(Request $request)
    {
        $validate_rules = [
            'description' => ['required'],
            'canceled_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de anulación')],
        ];
        $messages = [
            'description.required' => 'El campo descripción del motivo de la anulación es obligatorio.',
            'canceled_at.required' => 'El campo fecha de anulación es obligatorio.',
        ];
        $this->validate($request, $validate_rules, $messages);


        try {
            /* Objeto con información del compromiso a Anular */
            $compromise = BudgetCompromise::with('budgetStages')->find($request->id);

            // Se verifica que la fecha de anulación no sea menor a las fecha del aprobación
            if ($request->canceled_at < date_format($compromise->compromised_at, 'Y-m-d')) {
                $errors[0] = ["La fecha de anulación no puede ser menor a la fecha de aprobación ("
                . date_format($compromise->compromised_at, 'd/m/Y') . ")"];
                return response()->json(['result' => true, 'errors' => $errors], 422);
            }

            $isFinance = Module::has('Finance') && Module::isEnabled('Finance');
            $isPayroll = Module::has('Payroll') && Module::isEnabled('Payroll');
            $isPurchase = Module::has('Purchase') && Module::isEnabled('Purchase');

            DB::transaction(
                function () use (
                    $compromise,
                    $request,
                    $isFinance,
                    $isPayroll,
                    $isPurchase,
                ) {
                    if (isset($compromise)) {
                        $documentStatusAN = DocumentStatus::where('action', 'AN')->first();
                        $compromisedYear = explode("-", $compromise->compromised_at)[0];
                        /* Se verifica que el compromiso no sea un aporte de nómina de lo contrario no podrá ser anulado */
                        $CodePayroll = $isPayroll
                        ? CodeSetting::where(
                            "model",
                            \Modules\Payroll\Models\Payroll::class
                        )->first()
                        : null;

                        $regexPattern = '/^AP - \\d+' . $CodePayroll?->format_prefix . '/';

                        if (!preg_match($regexPattern, $compromise->document_number)) {
                            /* Se buscan todas las BudgetStage (etapas presupuestarias)pertenecintes al compromiso. (COMprometido) */
                            BudgetStage::query()
                                ->where(
                                    'budget_compromise_id',
                                    $compromise->id
                                )->where('type', 'COM')->delete();

                            /* Se buscan los ítems del compromiso */
                            $budgetCompromiseDetails = BudgetCompromiseDetail::query()
                                ->where(
                                    [
                                    'budget_compromise_id' => $compromise->id,
                                    'document_status_id'   => null
                                    ]
                                )->get();

                            foreach ($budgetCompromiseDetails as $budgetCompromiseDetail) {
                                $formulation = $budgetCompromiseDetail
                                    ->budgetSubSpecificFormulation()
                                    ->where('year', $compromisedYear)->first();

                                $taxAmount = isset($budgetCompromiseDetail['tax_id'])
                                ? $budgetCompromiseDetail['amount'] : 0;

                                $total = $taxAmount != 0
                                ? $taxAmount : $budgetCompromiseDetail['amount'];

                                $budgetAccountOpen = BudgetAccountOpen::with('budgetAccount')
                                    ->where(
                                        'budget_sub_specific_formulation_id',
                                        $formulation->id
                                    )->where(
                                        'budget_account_id',
                                        $budgetCompromiseDetail['budget_account_id']
                                    )
                                    ->whereHas(
                                        'budgetAccount',
                                        function ($query) {
                                            $query->where('specific', '!=', '00');
                                        }
                                    )->first();
                                if (isset($budgetAccountOpen)) {
                                    $budgetAccountOpen->update(
                                        [
                                            'total_year_amount_m'
                                                => $budgetAccountOpen->total_year_amount_m + $total,
                                        ]
                                    );
                                }
                                //Se anulan los items del compromiso
                                $budgetCompromiseDetail['document_status_id'] = $documentStatusAN->id;
                                $budgetCompromiseDetail->save();
                            }
                        } else {
                            throw new \Exception(
                                'El compromiso perteneciente a un aporte de nómina, no puede ser anulado.'
                            );
                        }

                        /* Se Cambia el estatus de la orden de compra sí existe */
                        if ($isPurchase) {
                            $purchaseOrder = (isset($compromise->sourceable_type)
                            && $compromise->sourceable_type
                            == \Modules\Purchase\Models\PurchaseDirectHire::class)
                            ? \Modules\Purchase\Models\PurchaseDirectHire::query()
                            ->where(
                                [
                                'id' => $compromise->sourceable_id,
                                'code' => $compromise->document_number
                                ]
                            )->first() : null;

                            if ($purchaseOrder) {
                                $purchaseOrder->status = 'WAIT';
                                $purchaseOrder->save();
                            }
                        }

                        /* Se Cambia el estatus de la Nómina sí existe */
                        if ($isPayroll) {
                            $payroll = \Modules\Payroll\Models\Payroll::query()
                                ->where(
                                    [
                                    'id' => $compromise->sourceable_id,
                                    'code' => $compromise->document_number
                                    ]
                                )->first() ?? null;

                            if (isset($payroll)) {
                                $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                                $payrollPaymentPeriod->payment_status = 'pending';
                                $payrollPaymentPeriod->availability_status = 'AN';
                                $payrollPaymentPeriod->save();

                                // Se procede a realizar todo el proceso de anulación
                                // de los aportes de nómina
                                $this->cancelContribution(
                                    $payroll->code,
                                    $request->canceled_at,
                                    $request->description,
                                );
                            }
                        }

                        /* Se Anula el movimiento bancario si existe*/
                        if (
                            $isFinance
                            && $compromise->compromiseable_type == \Modules\Finance\Models\FinanceBankingMovement::class
                        ) {
                            /* Buscar los movimientos bancarios, actualizar el concepto del movimiento,
                            y cambiar el estatus a anulado */
                            $bankingMovement = \Modules\Finance\Models\FinanceBankingMovement::query()
                            ->where(
                                'id',
                                $compromise->compromiseable_id
                            )->where(
                                'code',
                                $compromise->document_number
                            )->where(
                                'document_status_id',
                                '!=',
                                $documentStatusAN->id
                            )->first();

                            if ($bankingMovement) {
                                /*
                                 * Se buscan todas las BudgetStage (etapas presupuestarias)
                                 * pertenecintes al compromiso. (CAUsado y PAGado)
                                 */
                                BudgetStage::query()
                                ->where(
                                    'budget_compromise_id',
                                    $compromise->id
                                )->where('type', 'CAU')->delete();

                                BudgetStage::query()
                                ->where(
                                    'budget_compromise_id',
                                    $compromise->id
                                )->where('type', 'PAG')->delete();

                                $bankingMovement->concept = 'Anulado: '
                                . $bankingMovement->concept
                                . '. (' . $request->description . ')';
                                $bankingMovement->document_status_id = $documentStatusAN->id;
                                $bankingMovement->save();
                            }

                            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                                /* Reverso de Asiento contable del Movimiento Bancario */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::query()
                                ->where('reference', $bankingMovement->code)
                                ->orWhere('reference', $bankingMovement->reference)
                                ->first();

                                if ($accountEntry) {
                                    $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
                                        'from_date' => $request->canceled_at,
                                        'reference' => $accountEntry->reference,
                                        'concept' => 'Anulación: ' . $accountEntry->concept,
                                        'observations' => $request->description,
                                        'accounting_entry_category_id' => $accountEntry->accounting_entry_category_id,
                                        'institution_id' => $accountEntry->institution_id,
                                        'currency_id' => $accountEntry->currency_id,
                                        'tot_debit' => $accountEntry->tot_assets,
                                        'tot_assets' => $accountEntry->tot_debit,
                                        'approved' => false,
                                    ]);

                                    $accountingItems = \Modules\Accounting\Models\AccountingEntryAccount::query()
                                    ->where(
                                        'accounting_entry_id',
                                        $accountEntry->id,
                                    )->get();

                                    foreach ($accountingItems as $account) {
                                        /* Se crea la relación de cuenta a ese asiento */
                                        \Modules\Accounting\Models\AccountingEntryAccount::create([
                                            'accounting_entry_id' => $accountEntryNew->id,
                                            'accounting_account_id' => $account['accounting_account_id'],
                                            'debit' => $account['assets'],
                                            'assets' => $account['debit'],
                                        ]);
                                    }

                                    /* Crea la relación entre el asiento contable y el movimiento bancario*/
                                    \Modules\Accounting\Models\AccountingEntryable::create([
                                        'accounting_entry_id' => $accountEntryNew->id,
                                        'accounting_entryable_type' => \Modules\Finance\Models\FinanceBankingMovement::class,
                                        'accounting_entryable_id' => $bankingMovement->id,
                                    ]);
                                }
                            }
                        }

                        /* Se cambia el status del documento del compromiso a 'AN'ulado */
                        $compromise->document_status_id = $documentStatusAN->id;
                        $compromise->description = "Proceso Anulado: "
                        . $compromise->description . ". "
                        . "(" . $request->description . ")";
                        $compromise->save();
                    }
                }
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $message = str_replace("\n", "", $e->getMessage());
            if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                $pattern = '/ERROR:(.*?)DETAIL/';
                preg_match($pattern, $message, $matches);
                $errorMessage = trim($matches[1]);
            } else {
                $errorMessage = $message;
            }

            return response()->json(
                ['message' =>
                [
                    'type' => 'custom',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage)
                ]],
                500
            );
        }
        return response()->json(['record' => $compromise, 'message' => 'Success'], 200);
    }

        /**
         * Método que permite anular las emisiones, las ordenes
         * de pago y los compromisios de los aportes de nómina
         *
         * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
         *
         * @param string $code        Código
         * @param string $date        Fecha
         * @param string $description Descripción
         *
         * @return void
         */
    public function cancelContribution($code, $date, $description)
    {
        /* Se buscan todas las ordenes de pago asociadas a este compromiso */
        //Status del documento ANulado
        $documentStatusAN = DocumentStatus::where('action', 'AN')->first();
        // Patrón de la expresión regular relacionada con el código de nómina
        $regexPattern = "AP - \\d+$code";
        $compromiseContribution = BudgetCompromise::query()
            ->where(
                'document_number',
                '~',
                $regexPattern
            )->where(
                'document_status_id',
                '!=',
                $documentStatusAN->id
            )->get() ?? null;

        $isAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        $isFinance = Module::has('Finance') && Module::isEnabled('Finance');

        if (isset($compromiseContribution)) {
            foreach ($compromiseContribution as $compContribution) {
                $compromisedYear = explode("-", $compContribution->compromised_at)[0];

                /* Se buscan todas las etapas presupuestarias */

                //Etapa relacionada con la emisión de pago
                $paymentExecuteBugetStages = BudgetStage::query()
                    ->where([
                        'budget_compromise_id'  => $compContribution->id,
                        'stageable_type'        => \Modules\Finance\Models\FinancePaymentExecute::class,
                    ])
                    ->where('type', 'PAG')->get() ?? null;

                //Se realiza todo el proceso de anulación para las emisiones de pago
                if (isset($paymentExecuteBugetStages)) {
                    foreach ($paymentExecuteBugetStages as $paymentExecuteBugetStage) {
                        $financePaymentExecute =  $isFinance
                        ? \Modules\Finance\Models\FinancePaymentExecute::query()
                            ->find($paymentExecuteBugetStage->stageable_id)
                        : null;

                        if (isset($financePaymentExecute)) {
                            $financePaymentExecute->status = 'AN';
                            $financePaymentExecute->description = $description;
                            $financePaymentExecute->document_status_id = $documentStatusAN->id;

                            /* Se eliminan las retenciones asociadas a la emisión de pago */
                            $financePaymentExecute->financePaymentDeductions()->delete();
                            /* Se guadan los cambios en la emisión de pago */
                            $financePaymentExecute->save();

                            if ($isAccounting) {
                                /**
                                 * Reverso de Asiento contable de la emisión de pago
                                 */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where(
                                    'reference',
                                    $financePaymentExecute->code
                                )->first();
                                $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create(
                                    [
                                        'from_date' => $date,
                                        // Código de la ejecución de pago como referencia
                                        'reference' => $financePaymentExecute->code,
                                        'concept' => 'Anulación: ' . $accountEntry->concept ,
                                        'observations' => $description,
                                        'accounting_entry_category_id' => $accountEntry->accounting_entry_category_id,
                                        'institution_id' => $accountEntry->institution_id,
                                        'currency_id' => $accountEntry->currency_id,
                                        'tot_debit' => $accountEntry->tot_assets,
                                        'tot_assets' => $accountEntry->tot_debit,
                                        'approved' => false,
                                    ]
                                );

                                $accountingItems = \Modules\Accounting\Models\AccountingEntryAccount::query()
                                    ->where(
                                        'accounting_entry_id',
                                        $accountEntry->id,
                                    )->get();

                                foreach ($accountingItems as $account) {
                                    /* Se crea la relación de cuenta a ese asiento */
                                    \Modules\Accounting\Models\AccountingEntryAccount::create(
                                        [
                                        'accounting_entry_id' => $accountEntryNew->id,
                                        'accounting_account_id' => $account['accounting_account_id'],
                                        'debit' => $account['assets'],
                                        'assets' => $account['debit'],
                                        ]
                                    );
                                }

                                /* Crea la relación entre el asiento contable y el registro de emisión de pago */
                                \Modules\Accounting\Models\AccountingEntryable::create(
                                    [
                                        'accounting_entry_id' => $accountEntryNew->id,
                                        'accounting_entryable_type' => \Modules\Finance\Models\FinancePaymentExecute::class,
                                        'accounting_entryable_id' => $financePaymentExecute->id,
                                    ]
                                );
                            }

                            //Se eliminan las estapas presupuestarias
                            BudgetStage::query()
                                ->where(
                                    [
                                    'budget_compromise_id'  => $compContribution->id,
                                    'stageable_type'        => \Modules\Finance\Models\FinancePaymentExecute::class,
                                    'stageable_id'          => $financePaymentExecute->id
                                    ]
                                )
                                ->where('type', 'PAG')->delete();

                            /* Buscar los movimientos bancarios, actualizar el concepto del movimiento,
                            y cambiar el estatus a anulado el registro */
                            $bankingMovementPaymentExecute = \Modules\Finance\Models\FinanceBankingMovement::query()
                                ->where(
                                    'reference',
                                    $financePaymentExecute->code
                                )->where(
                                    'document_status_id',
                                    '!=',
                                    $documentStatusAN->id
                                )->first();

                            if ($bankingMovementPaymentExecute) {
                                $bankingMovementPaymentExecute->concept = 'Anulado: '
                                . $bankingMovementPaymentExecute->concept
                                . '. (' . $description . ')';
                                $bankingMovementPaymentExecute->document_status_id = $documentStatusAN->id;
                                $bankingMovementPaymentExecute->save();
                            }
                        }
                    }
                }

                //Etapa relacionada con la orden de pago
                $payOrderBugetStages = BudgetStage::query()
                    ->where(
                        [
                            'budget_compromise_id'  => $compContribution->id,
                            'stageable_type'        => \Modules\Finance\Models\FinancePayOrder::class,
                        ]
                    )
                    ->where('type', 'CAU')->get();

                //Se realiza todo el proceso de anulación para las ordenes de pago
                if (isset($payOrderBugetStages)) {
                    foreach ($payOrderBugetStages as $payOrderBugetStage) {
                        // Se buscan todas las órdenes de pago asociadas a este compromiso
                        $financePayOrder = $isFinance
                        ? \Modules\Finance\Models\FinancePayOrder::query()
                            ->find($payOrderBugetStage->stageable_id)
                        : null;

                        if (isset($financePayOrder)) {
                            $financePayOrder->status = 'PE';
                            $financePayOrder->document_status_id = $documentStatusAN->id;
                            $financePayOrder->observations = 'ANULADO: ' . $financePayOrder->observations
                            . '. (' . $description . ')';
                            $financePayOrder->save();

                            if ($isAccounting) {
                                /* Reverso de Asiento contable de la orden de pago */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePayOrder->code)->first();
                                $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create(
                                    [
                                        'from_date' => $date,
                                        // Código de la ejecución de pago como referencia
                                        'reference' => $financePayOrder->code,
                                        'concept' => 'Anulación: ' . $accountEntry->concept ,
                                        'observations' => $description,
                                        'accounting_entry_category_id' => $accountEntry->accounting_entry_category_id,
                                        'institution_id' => $accountEntry->institution_id,
                                        'currency_id' => $accountEntry->currency_id,
                                        'tot_debit' => $accountEntry->tot_assets,
                                        'tot_assets' => $accountEntry->tot_debit,
                                        'approved' => false,
                                    ]
                                );

                                $accountingItems = \Modules\Accounting\Models\AccountingEntryAccount::query()
                                    ->where(
                                        'accounting_entry_id',
                                        $accountEntry->id,
                                    )->get();
                                foreach ($accountingItems as $account) {
                                    /* Se crea la relación de cuenta a ese asiento */
                                    \Modules\Accounting\Models\AccountingEntryAccount::create(
                                        [
                                        'accounting_entry_id' => $accountEntryNew->id,
                                        'accounting_account_id' => $account['accounting_account_id'],
                                        'debit' => $account['assets'],
                                        'assets' => $account['debit'],
                                        ]
                                    );
                                }

                                /* Crea la relación entre el asiento contable y el registro de orden de pago */
                                \Modules\Accounting\Models\AccountingEntryable::create(
                                    [
                                        'accounting_entry_id' => $accountEntryNew->id,
                                        'accounting_entryable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                        'accounting_entryable_id' => $financePayOrder->id,
                                    ]
                                );
                            }

                            //Se eliminan las estapas presupuestarias
                            BudgetStage::query()
                                ->where(
                                    [
                                        'budget_compromise_id'  => $compContribution->id,
                                        'stageable_type'        => \Modules\Finance\Models\FinancePayOrder::class,
                                        'stageable_id'          => $financePayOrder->id
                                    ]
                                )
                                ->where('type', 'CAU')->delete();
                        }
                    }
                }

                //Se elimina Etapa presupuestaria COMprometido
                BudgetStage::query()
                    ->where(
                        'budget_compromise_id',
                        $compContribution->id,
                    )->where('type', 'COM')->delete();

                /* Se buscan los ítems del compromiso */
                $budgetCompromiseDetails = BudgetCompromiseDetail::query()
                    ->where(
                        [
                        'budget_compromise_id' => $compContribution->id,
                        'document_status_id'   => null
                        ]
                    )->get();

                foreach ($budgetCompromiseDetails as $budgetCompromiseDetail) {
                    $formulation = $budgetCompromiseDetail
                        ->budgetSubSpecificFormulation()
                        ->where('year', $compromisedYear)->first();

                    $taxAmount = isset($budgetCompromiseDetail['tax_id'])
                    ? $budgetCompromiseDetail['amount'] : 0;

                    $total = $taxAmount != 0
                    ? $taxAmount : $budgetCompromiseDetail['amount'];

                    $budgetAccountOpen = BudgetAccountOpen::with('budgetAccount')
                        ->where(
                            'budget_sub_specific_formulation_id',
                            $formulation->id
                        )->where(
                            'budget_account_id',
                            $budgetCompromiseDetail['budget_account_id']
                        )
                        ->whereHas(
                            'budgetAccount',
                            function ($query) {
                                $query->where('specific', '!=', '00');
                            }
                        )->first();

                    if (isset($budgetAccountOpen)) {
                        $budgetAccountOpen->update(
                            [
                            'total_year_amount_m'
                                => $budgetAccountOpen->total_year_amount_m + $total,
                            ]
                        );
                    }

                    //Se anulan los items del compromiso
                    $budgetCompromiseDetail['document_status_id'] = $documentStatusAN->id;
                    $budgetCompromiseDetail->save();
                }

                //Se cambia el status del documento del compromiso a 'AN'ulado
                $compContribution['document_status_id'] = $documentStatusAN->id;
                $compContribution['description'] = "Proceso Anulado: "
                . $compContribution->description . ". "
                . "(" . $description . ")";
                $compContribution->save();
            }
        }
    }

    /**
     * Método para cambiar el estado de una emisión de pago a Pagado 'PA'
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function approveBudgetCompromise(Request $request)
    {
        try {
            DB::transaction(
                function () use ($request) {
                    /* Objeto con información del compromiso a eliminar */
                    $budgetCompromise = BudgetCompromise::with('budgetStages')->find($request->id);

                    if (!isset($budgetCompromise)) {
                        throw new \Exception('El compromiso no existe');
                    }
                    $documentStatus = DocumentStatus::where('action', 'AP')->first();  //Status del documento Aprobado
                    $budgetCompromise->document_status_id = $documentStatus->id;
                    $budgetCompromise->save();
                }
            );
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $message = str_replace("\n", "", $e->getMessage());
            if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                $pattern = '/ERROR:(.*?)DETAIL/';
                preg_match($pattern, $message, $matches);
                $errorMessage = trim($matches[1]);
            } else {
                $errorMessage = $message;
            }

            return response()->json(
                ['message' =>
                [
                    'type' => 'custom',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage)
                ]],
                500
            );
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Verifica si el compromiso dado está relacionado con un movimiento bancario
     * con estatus de aprobado en el módulo de finanzas.
     *
     * @param $compromise El compromiso a verificar
     *
     * @return bool
     */
    private function isBankingMovementApproved($compromise)
    {
        if (
            $compromise
            && ($compromise->compromiseable_type == \Modules\Finance\Models\FinanceBankingMovement::class)
            && Module::has('Finance')
            && Module::isEnabled('Finance')
        ) {
            return \Modules\Finance\Models\FinanceBankingMovement::query()
            ->where('id', $compromise->compromiseable_id)
            ->where('document_status_id', DocumentStatus::where('action', 'AP')->first()->id)
            ->exists();
        }
        return false;
    }
}
