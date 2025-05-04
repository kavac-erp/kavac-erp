<?php

namespace Modules\Finance\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\DocumentStatus;
use App\Models\Profile;
use App\Models\Tax;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\Jobs\AccountingManageEntries;
use Modules\Accounting\Models\AccountingAccount;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingEntryable;
use Modules\Accounting\Models\AccountingEntryAccount;
use Modules\Accounting\Models\AccountingEntryCategory;
use Modules\Accounting\Models\Institution;
use Modules\Budget\Models\BudgetAccountOpen;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetCompromiseDetail;
use Modules\Budget\Models\BudgetSpecificAction;
use Modules\Budget\Models\BudgetStage;
use Modules\Finance\Models\FinanceBankingMovement;
use Nwidart\Modules\Facades\Module;

/**
 * @class FinanceMovementsController
 *
 * @brief Gestión de Finanzas > Banco > Movimientos.
 *
 * Clase que gestiona lo referente a Conciliaciones bancarias.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceMovementsController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     * @var Array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     * @var Array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:finance.movements.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:finance.movements.create', ['only' => 'store']);
        $this->middleware('permission:finance.movements.edit', ['only' => ['create', 'update']]);
        $this->middleware('permission:finance.movements.delete', ['only' => 'destroy']);
        // $this->middleware('permission:finance.movements.cancel', ['only' => 'cancelMovements']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'institution_id' => ['required'],
            'payment_date' => ['required', new DateBeforeFiscalYear('fecha de pago')],
            'transaction_type' => ['required'],
            'finance_bank_account_id' => ['required'],
            'reference' => ['required', 'max:30'],
            'concept' => ['required', 'max:400'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency_id' => ['required'],
            'entry_category' => ['required'],
            'entry_concept' => ['required', 'max:400'],
            'recordsAccounting' => ['required'],
            'recordsAccounting.*.assets' => ['numeric', 'min:0'],
            'recordsAccounting.*.debit' => ['numeric', 'min:0'],
            'recordsAccounting' => ['required'],
            'accounts.*.description' => ['max:400'],
            'totDebit' => ['same:totAssets', 'numeric', 'min:0.01'],
            'totAssets' => ['numeric', 'min:0.01'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo institución es obligatorio',
            'payment_date.required' => 'El campo fecha de pago es obligatorio',
            'transaction_type.required' => 'El campo tipo de transacción es obligatorio',
            'finance_bank_account_id.required' => 'El campo Nro. de Cuenta es obligatorio',
            'reference.required' => 'El campo documento de referencia es obligatorio',
            'reference.max' => 'El campo documento de referencia debe ser menor a 30 caracteres',
            'concept.required' => 'El campo concepto es obligatorio',
            'concept.max' => 'El campo concepto debe ser menor a 400 caracteres',
            'amount.required' => 'El campo monto es obligatorio',
            'amount.numeric' => 'El campo monto debe ser de tipo numérico',
            'amount.min' => 'El campo monto debe ser mayor que 0',
            'currency_id.required' => 'El campo tipo de moneda es obligatorio',
            'entry_category.required' => 'El campo categoría del asiento es obligatorio',
            'entry_concept.required' => 'El campo concepto o descripción es obligatorio',
            'entry_concept.max' => 'El campo concepto o descripción debe ser menor a 400 carácteres',
            'recordsAccounting.required' => 'Es obligatorio registrar un asiento contable',
            'accounts.*.description.max' => 'El campo concepto del compromiso debe ser menor a 400 carácteres',
            'totDebit.same' => 'El asiento no esta balanceado, Por favor verifique',
            'recordsAccounting.*.debit.min' => 'Los valores agregados en la columna del DEBE deben ser positivos',
            'recordsAccounting.*.assets.min' => 'Los valores agregados en la columna del HABER deben ser positivos',
            'totDebit.min' => 'El total del asiento por la columna del DEBE debe ser mayor que 0',
            'totAssets.min' => 'El total del asiento por la columna del HABER debe ser mayor que 0',
        ];
    }

    /**
     * Muestra la plantilla del módulo Finanzas > Banco > Movimientos.
     *
     * @method index
     *
     * @author Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            return view('finance::movements.list');
        } else {
            return redirect()->route('finance.setting.index')->with('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe tener instalado el módulo de contabilidad para poder utilizar esta funcionalidad.',
            ]);
        }
    }

    /**
     * Muestra el formulario de registro de movimientos bancarios
     *
     * @method    create
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Vista con el formulario
     */
    public function create()
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accounting = 1;
        } else {
            return redirect()->route('finance.setting.index')->with('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe tener instalado el módulo de contabilidad para poder utilizar esta funcionalidad.',
            ]);
        }

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $budget = 1;
        }

        /**
         * [$accountingList contiene las cuentas patrimoniales]
         * @var [Json]
         */
        $accountingList = $this->getGroupAccountingAccount();

        /**
         * [$categories contendra las categorias]
         * @var array
         */
        $categories = [];
        array_push($categories, [
            'id' => '',
            'text' => 'Seleccione...',
            'acronym' => '',
        ]);

        foreach (AccountingEntryCategory::all() as $category) {
            array_push($categories, [
                'id' => $category->id,
                'text' => $category->name,
                'acronym' => $category->acronym,
            ]);
        }

        /**
         * se convierte array a JSON
         */
        $categories = json_encode($categories);

        return view('finance::movements.create', compact('accountingList', 'categories', 'accounting', 'budget'));
    }

    public function store(Request $request)
    {
        $codeSetting = CodeSetting::where('table', 'finance_movements_code')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]);
            return response()->json(['result' => false, 'redirect' => route('finance.setting.index')], 200);
        }

        $codeMovement = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
            $codeSetting->model,
            $codeSetting->field
        );
        $request['amount'] = "1000";
        DB::transaction(function () use ($request, $codeMovement) {
            $this->validate($request, $this->validateRules, $this->messages);

            /** @var Object Estado inicial del registro establecido a 'En Proceso' = 'Pendiente' */
            $documentStatusPR = DocumentStatus::where('action', 'PR')->first();

            $bankingMovement = FinanceBankingMovement::create([
                'code' => $codeMovement,
                'payment_date' => $request->input('payment_date'),
                'transaction_type' => $request->input('transaction_type'),
                'reference' => $request->input('reference'),
                'concept' => $request->input('concept'),
                'amount' => $request->input('totAssets'),
                'currency_id' => $request->input('currency_id'),
                'finance_bank_account_id' => $request->input('finance_bank_account_id'),
                'institution_id' => $request->input('institution_id'),
                'document_status_id' => $documentStatusPR->id,
            ]);

            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                if ($request->recordsAccounting && !empty($request->recordsAccounting)) {
                    $is_admin = auth()->user()->level == 1 ? true : false;
                    if ($is_admin) {
                        $institution = Institution::where('default', true)->first();
                    } else {
                        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

                        $institution = $user_profile['institution'];
                    }

                    AccountingManageEntries::dispatch(
                        [
                            'date' => $request->input('payment_date'),
                            'reference' => $request->input('reference'),
                            'concept' => $request->input('entry_concept'),
                            'observations' => '',
                            'category' => $request->input('entry_category'),
                            'currency_id' => $request->input('currency_id'),
                            'totDebit' => $request->input('totDebit'),
                            'totAssets' => $request->input('totAssets'),
                            'module' => 'Finance',
                            'model' => FinanceBankingMovement::class,
                            'relatable_id' => $bankingMovement->id,
                            'accountingAccounts' => $request->recordsAccounting,
                        ],
                        ($request->institution_id) ?
                        $request->institution_id :
                        $institution->id,
                    );
                }
            }

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                if ($request->accounts && !empty($request->accounts)) {
                    $codeSetting = CodeSetting::where("model", BudgetCompromise::class)->first();

                    if (!$codeSetting) {
                        $request->session()->flash('message', [
                            'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                            'text' => 'Debe configurar previamente el formato para el código a generar',
                        ]);
                        return response()->json(['result' => false, 'redirect' => route('budget.setting.index')], 200);
                    }

                    $year = $request->fiscal_year ?? date("Y");

                    $code = generate_registration_code(
                        $codeSetting->format_prefix,
                        strlen($codeSetting->format_digits),
                        (strlen($codeSetting->format_year) === 2) ? date("y") : $year,
                        BudgetCompromise::class,
                        'code'
                    );

                    /** @var Object Estado inicial del registro del compromiso establecido a 'Aprobado'*/
                    $documentStatusAP = DocumentStatus::where('action', 'AP')->first();

                    $compromisedYear = explode("-", $request->payment_date)[0];

                    /** @var Object Datos del compromiso */
                    $compromise = BudgetCompromise::create([
                        'document_number' => $codeMovement,
                        'institution_id' => $request->institution_id,
                        'compromised_at' => $request->payment_date,
                        'description' => $request->concept,
                        'code' => $code,
                        'document_status_id' => $documentStatusAP->id,
                        'compromiseable_type' => FinanceBankingMovement::class,
                        'compromiseable_id' => $bankingMovement->id,
                    ]);

                    $total = 0;
                    /** Gestiona los ítems del compromiso */
                    foreach ($request->accounts as $account) {
                        $spac = BudgetSpecificAction::find($account['specific_action_id']);
                        $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();
                        $tax = (isset($account['account_tax_id']) || isset($account['tax_id']))
                        ? Tax::find($account['account_tax_id'] ?? $account['tax_id'])
                        : new Tax();
                        $taxHistory = ($tax)
                        ? $tax->histories()->orderBy('operation_date', 'desc')->first()
                        : new Tax();
                        $taxAmount = ($account['amount'] * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;
                        $compromise->budgetCompromiseDetails()->create([
                            'description' => $account['description'],
                            'amount' => $account['amount'],
                            'tax_amount' => $taxAmount,
                            'tax_id' => $account['account_tax_id'] ?? $account['tax_id'],
                            'budget_account_id' => $account['account_id'],
                            'budget_sub_specific_formulation_id' => $formulation->id,
                        ]);
                        $total += ($account['amount'] + $taxAmount);
                    }

                    $compromise->budgetStages()->create([
                        'code' => $code,
                        'registered_at' => $request->payment_date,
                        'type' => 'COM',
                        'amount' => $total,
                    ]);
                }
            }
        });

        $bankingMovement = FinanceBankingMovement::where('code', $codeMovement)->first();
        if (is_null($bankingMovement)) {
            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación.',
                ]
            );
        } else {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return response()->json(['result' => true, 'redirect' => route('finance.movements.index')], 200);
    }

    public function show()
    {
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accounting = 1;
        } else {
            return redirect()->route('finance.setting.index')->with('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe tener instalado el módulo de contabilidad para poder utilizar esta funcionalidad.',
            ]);
        }

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $budget = 1;
        }

        /**
         * [$accountingList contiene las cuentas patrimoniales]
         * @var [Json]
         */
        $accountingList = json_encode($this->getRecordsAccounting());

        /**
         * [$categories contendra las categorias]
         * @var array
         */
        $categories = [];
        array_push($categories, [
            'id' => '',
            'text' => 'Seleccione...',
            'acronym' => '',
        ]);

        foreach (AccountingEntryCategory::all() as $category) {
            array_push($categories, [
                'id' => $category->id,
                'text' => $category->name,
                'acronym' => $category->acronym,
            ]);
        }

        /**
         * se convierte array a JSON
         */
        $categories = json_encode($categories);

        $movement = FinanceBankingMovement::find($id);

        return view(
            'finance::movements.create',
            compact('accountingList', 'categories', 'accounting', 'budget', 'movement')
        );
    }

    public function update(Request $request, $id)
    {
        $bankingMovement = FinanceBankingMovement::find($id);
        $this->validate($request, $this->validateRules, $this->messages);

         /** @var Object Estado inicial del registro establecido a 'En Proceso' = 'Pendiente' */
        $documentStatusPR = DocumentStatus::where('action', 'PR')->first();

        $bankingMovement->payment_date = $request->input('payment_date');
        $bankingMovement->transaction_type = $request->input('transaction_type');
        $bankingMovement->reference = $request->input('reference');
        $bankingMovement->concept = $request->input('concept');
        $bankingMovement->amount =  $request->input('totAssets');
        $bankingMovement->currency_id = $request->input('currency_id');
        $bankingMovement->finance_bank_account_id = $request->input('finance_bank_account_id');
        $bankingMovement->finance_bank_account_id = $request->input('institution_id');
        $bankingMovement->document_status_id = $documentStatusPR->id;
        $bankingMovement->save();

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            if ($request->recordsAccounting && !empty($request->recordsAccounting)) {
                $is_admin = auth()->user()->isAdmin() || false;

                if ($is_admin) {
                    $institution = Institution::where('default', true)->first();
                } else {
                    $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

                    $institution = $user_profile['institution'];
                }

                AccountingManageEntries::dispatch(
                    [
                        'date' => $request->input('payment_date'),
                        'reference' => $request->input('reference'),
                        'concept' => $request->input('entry_concept'),
                        'observations' => '',
                        'category' => $request->input('entry_category'),
                        'currency_id' => $request->input('currency_id'),
                        'totDebit' => $request->input('totDebit'),
                        'totAssets' => $request->input('totAssets'),
                        'module' => 'Finance',
                        'model' => FinanceBankingMovement::class,
                        'relatable_id' => $bankingMovement->id,
                        'accountingAccounts' => $request->recordsAccounting,
                    ],
                    ($request->institution_id) ?
                    $request->institution_id :
                    $institution->id,
                );
            }
        }

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $codeSetting = CodeSetting::where("model", BudgetCompromise::class)->first();

            if (!$codeSetting) {
                $request->session()->flash('message', [
                    'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                    'text' => 'Debe configurar previamente el formato para el código a generar',
                ]);
                return response()->json(['result' => false, 'redirect' => route('budget.setting.index')], 200);
            }

            /** @var Object Estado inicial del registro del compromiso establecido a 'Aprobado'*/
            $documentStatusAP = DocumentStatus::where('action', 'AP')->first();
            $year = $request->fiscal_year ?? date("Y");

            $colum = [
                'compromised_at' => $request->payment_date,
                'description' => $request->concept,
                'document_status_id' => $documentStatusAP->id,
            ];

            if (
                !BudgetCompromise::where('compromiseable_type', FinanceBankingMovement::class)
                ->where('compromiseable_id', $bankingMovement->id)->first()
            ) {
                $code = generate_registration_code(
                    $codeSetting->format_prefix,
                    strlen($codeSetting->format_digits),
                    (strlen($codeSetting->format_year) === 2) ? date("y") : $year,
                    BudgetCompromise::class,
                    'code'
                );
                $colum['code'] = $code;
            }

            $compromisedYear = explode("-", $request->payment_date)[0];

            /** @var Object Datos del compromiso */
            $compromise = BudgetCompromise::updateOrCreate(
                [
                    'document_number' => $bankingMovement->code,
                    'institution_id' => $request->institution_id,
                    'compromiseable_type' => FinanceBankingMovement::class,
                    'compromiseable_id' => $bankingMovement->id,
                ],
                $colum
            );

            $total = 0;

            $compromiseDetails = $compromise->budgetCompromiseDetails()->get();

            foreach ($compromiseDetails as $details) {
                $details->delete();
            }

            /** Gestiona los ítems del compromiso */
            foreach ($request->accounts as $account) {
                $spac = BudgetSpecificAction::find($account['specific_action_id']);
                $formulation = $spac->subSpecificFormulations()->where('year', $compromisedYear)->first();
                $tax = (isset($account['account_tax_id']) || isset($account['tax_id']))
                ? Tax::find($account['account_tax_id'] ?? $account['tax_id'])
                : new Tax();
                $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new Tax();
                $taxAmount = ($account['amount'] * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;

                $compromise->budgetCompromiseDetails()->create([
                    'description' => $account['description'],
                    'amount' => $account['amount'],
                    'tax_amount' => $taxAmount,
                    'tax_id' => $account['account_tax_id'] ?? $account['tax_id'],
                    'budget_account_id' => $account['account_id'],
                    'budget_sub_specific_formulation_id' => $formulation->id,
                ]);
                $total += ($account['amount'] + $taxAmount);
            }

            $compromise->budgetStages()->updateOrcreate(
                [
                    'code' => $compromise->code,
                ],
                [
                    'registered_at' => $request->payment_date,
                    'type' => 'COM',
                    'amount' => $total,
                ]
            );
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('finance.movements.index')], 200);
    }

    public function destroy($id)
    {
        $bankingMovement = FinanceBankingMovement::find($id);

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $entryAccount = AccountingEntry::where('reference', $bankingMovement->reference)->first();
            $entries = AccountingEntryAccount::where('accounting_entry_id', $entryAccount->id)->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }

            $entryAccount->delete();
        }

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $budgetCompromise = BudgetCompromise::where('compromiseable_type', FinanceBankingMovement::class)
                ->where('compromiseable_id', $bankingMovement->id)->first();
            $compromiseDetails = BudgetCompromiseDetail::where('budget_compromise_id', $budgetCompromise->id)->get();

            foreach ($compromiseDetails as $compromiseDetail) {
                $compromiseDetail->delete();
            }

            $budgetCompromise->delete();
        }
        $bankingMovement->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene un listado de los movimientos bancarios registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $movements = FinanceBankingMovement::with([
            'financeBankAccount.financeBankingAgency.financeBank', 'financeBankAccount.financeAccountType',
            'currency', 'institution', 'accountingEntryPivot.accountingEntry.accountingAccounts.account',
            'budgetCompromise.budgetCompromiseDetails.budgetSubSpecificFormulation',
            'budgetCompromise.budgetCompromiseDetails.budgetAccount',
        ])->get();
        return response()->json([
            'records' => $movements,
            // 'cancelBankMovementPermission' => auth()->user()->hasPermission('permission:finance.movements.cancel'),
        ], 200);
    }

    /**
     * Obtiene la información de un registro
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return \Illuminate\Http\JsonResponse Objeto con el registro a mostrar
     */
    public function vueInfo($id)
    {
        $movements = FinanceBankingMovement::where('id', $id)->with(['financeBankAccount', 'currency', 'institution',
            'accountingEntryPivot.accountingEntry.accountingAccounts.account',
            'budgetCompromise.budgetCompromiseDetails.budgetSubSpecificFormulation',
            'budgetCompromise.budgetCompromiseDetails.budgetAccount'])->get();
        return response()->json(['record' => $movements], 200);
    }

    /**
     * Consulta los registros del modelo AccountingAccount que posean conversión
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param  Request $request [array con listado de cuentas a convertir]
     *                             true= todo, false=solo sin conversiones
     * @return Array
     */
    public function getRecordsAccounting()
    {
        /**
         * [$records contendra registros]
         * @var array
         */
        $records = [];
        $index = 0;
        array_push($records, [
            'id' => '',
            'text' => "Seleccione...",
        ]);

        /**
         * ciclo para almacenar en array cuentas patrimoniales disponibles para conversiones
         */
        foreach (
            AccountingAccount::with('accountable')
            ->where('active', true)
            ->orderBy('group', 'ASC')
            ->orderBy('subgroup', 'ASC')
            ->orderBy('item', 'ASC')
            ->orderBy('generic', 'ASC')
            ->orderBy('specific', 'ASC')
            ->orderBy('subspecific', 'ASC')
            ->orderBy('denomination', 'ASC')
            ->cursor() as $AccountingAccount
        ) {
            array_push($records, [
                'id' => $AccountingAccount->id,
                'text' => "{$AccountingAccount->getCodeAttribute()} - {$AccountingAccount->denomination}",
            ]);
            $index++;
        }

        $records[0]['index'] = $index;

        /**
         * se convierte array a JSON
         */
        return $records;
    }
    public function getGroupAccountingAccount($first = true, $parent_id = null)
    {
        /** @var Object Colección con información de las cuentas contables regsitradas en el cátalogo de cuentas patrimoniales  */
        $accountings = AccountingAccount::query()
            ->where('active', true)
            ->orderBy('group')
            ->orderBy('subgroup')
            ->orderBy('item')
            ->orderBy('generic')
            ->orderBy('specific')
            ->orderBy('subspecific')
            ->orderBy('institutional')
            ->toBase()
            ->get();

        /** @var Array Arreglo con el listado de opciones de cuentas patrimoniales a seleccionar */
        $records = $accountings->map(function ($a) {
            return [
                'id' => $a->id,
                'text' => "{$a->group}.{$a->subgroup}.{$a->item}.{$a->generic}.{$a->specific}.{$a->subspecific}.{$a->institutional} - {$a->denomination}",
                'disabled' => ($a->original) ?: false,
            ];
        })->toArray();

        array_unshift(
            $records,
            [
                'id' => '',
                'text' => 'Seleccione...',
            ]
        );

        return json_encode($records);
    }

    /**
     * Establece el nuevo estatus del documento
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function changeDocumentStatus(Request $request)
    {
        try {
            DB::transaction(
                function () use ($request) {
                    $bankingMovement = FinanceBankingMovement::find($request->id);
                    $documentStatus = DocumentStatus::where('action', 'AP')->first();
                    $bankingMovement->document_status_id = $documentStatus->id;
                    $bankingMovement->save();
                }
            );
            return response()->json(['message' => 'Success'], 200);
        } catch (\Exception $e) {
            $message = str_replace("\n", "", $e->getMessage());
            if (strpos($message, 'ERROR') !== false && strpos($message, 'DETAIL') !== false) {
                $pattern = '/ERROR:(.*?)DETAIL/';
                preg_match($pattern, $message, $matches);
                $errorMessage = trim($matches[1]);
            } else {
                $errorMessage = $message;
            }

            $request->session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage)
                ]
            );
        }
    }

    /**
     * Función que permite hacer un reverso de un movimiento bancario.
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function cancelMovements(Request $request)
    {
        $validate_rules = [
            'description' => ['required'],
            'canceled_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de anulación')],
        ];
        $messages = [
            'description.required' => 'El campo descripción del motivo de la anulación es obligatorio.',
            'canceled_at.required' => 'El campo fecha de anulación es obligatorio.',
        ];
        if ($request->is_payment_executed) {
            $errors[0] = ["Este movimiento bancario debe ser anulado desde la emisión de pago."];
            return response()->json(['result' => true, 'errors' => $errors], 422);
        }
        $this->validate($request, $validate_rules, $messages);

        try {
            DB::transaction(function () use ($request) {
                /**
                 * @var Object Se busca el movimiento bancario
                 */
                $bankingMovement = FinanceBankingMovement::query()
                ->where([
                    'id' => $request->id,
                    'document_status_id' => DocumentStatus::where('action', 'AP')->first()->id,
                ])->first();

                if (isset($bankingMovement)) {
                    /**
                     * El moviemto bancario que  proviene de una emisón de pago
                     * no puede ser aulada desde esta funcionalidad
                     */
                    if ($bankingMovement->is_payment_executed) {
                        throw new \Exception(
                            "Este movimiento bancario puede ser anulado desde la emisión de pago."
                            . $bankingMovement->reference
                        );
                    }
                    $documentStatusAN = DocumentStatus::where('action', 'AN')->first();
                    $bankingMovement->concept = $bankingMovement->concept
                    . " ($request->description).";
                    $bankingMovement->document_status_id = $documentStatusAN->id;
                    $bankingMovement->save();

                    /**
                     * Se realiza todo el proceso de reverso del asiento contable
                     */
                    if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        /**
                         * Reverso de Asiento contable del movimiento bancario
                         */
                        $accountEntry = AccountingEntry::query()
                        ->where([
                            'reference' => $bankingMovement->reference,
                            'institution_id' => $bankingMovement->institution_id,
                        ])->first();
                        if (isset($accountEntry)) {
                            $accountEntryNew = AccountingEntry::create([
                                'from_date' => $request->canceled_at,
                                // Código del movimiento bancario como referencia
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

                            $accountingItems = AccountingEntryAccount::query()
                            ->where(
                                'accounting_entry_id',
                                $accountEntry->id,
                            )->get();

                            foreach ($accountingItems as $account) {
                                /**
                                 * Se crea la relación de cuenta a ese asiento
                                 */
                                AccountingEntryAccount::create([
                                    'accounting_entry_id' => $accountEntryNew->id,
                                    'accounting_account_id' => $account['accounting_account_id'],
                                    'debit' => $account['assets'],
                                    'assets' => $account['debit'],
                                ]);
                            }

                            /** Crea la relación entre el asiento contable y el registro de movimiento */
                            AccountingEntryable::create([
                                'accounting_entry_id' => $accountEntryNew->id,
                                'accounting_entryable_type' => FinanceBankingMovement::class,
                                'accounting_entryable_id' => $bankingMovement->id,
                            ]);
                        }
                    }

                    /**
                     * Se realiza todo el proceso de anulación del compromiso si existe
                     */
                    if (Module::has('Budget') && Module::isEnabled('Budget')) {
                        $compromise = BudgetCompromise::query()
                        ->where([
                            'document_number'      => $bankingMovement->code,
                            'compromiseable_type'   => FinanceBankingMovement::class,
                            'compromiseable_id'     => $bankingMovement->id
                        ])->where(
                            'document_status_id',
                            '!=',
                            $documentStatusAN->id
                        )->first();

                        // Si se encuentra un compromiso aprobado para este documento...
                        if (isset($compromise)) {
                            $compromisedYear = explode("-", $compromise->compromised_at)[0];
                            if ($compromisedYear) {
                                BudgetStage::query()
                                ->where(
                                    'budget_compromise_id',
                                    $compromise->id
                                )->where('type', 'COM')->delete();

                                /** Se buscan los ítems del compromiso */
                                $budgetCompromiseDetails = BudgetCompromiseDetail::query()
                                ->where([
                                    'budget_compromise_id' => $compromise->id,
                                    'document_status_id'   => null
                                ])->get();

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
                                    ->whereHas('budgetAccount', function ($query) {
                                        $query->where('specific', '!=', '00');
                                    })->first();
                                    if (isset($budgetAccountOpen)) {
                                        $budgetAccountOpen->update([
                                            'total_year_amount_m'
                                                => $budgetAccountOpen->total_year_amount_m + $total,
                                        ]);
                                    }

                                    //Se anulan los items del compromiso
                                    $budgetCompromiseDetail['document_status_id'] = $documentStatusAN->id;
                                    $budgetCompromiseDetail->save();
                                }

                                //Se cambia el status del documento del compromiso a 'AN'ulado
                                $compromise->document_status_id = $documentStatusAN->id;
                                $compromise->description = "Proceso Anulado: "
                                . $compromise->description . ". "
                                . "(" . $request->description . ")";
                                $compromise->save();
                            }
                        }
                    }
                } else {
                    throw new \Exception(
                        "Movimieto bancario no encontrado."
                    );
                }
            });
        } catch (\Exception $e) {
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
}
