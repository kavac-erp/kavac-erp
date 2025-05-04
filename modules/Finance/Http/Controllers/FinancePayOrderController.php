<?php

namespace Modules\Finance\Http\Controllers;

use App\Models\Institution;
use App\Models\Receiver;
use App\Models\Currency;
use App\Models\CodeSetting;
use App\Models\Deduction;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use App\Models\FiscalYear;
use App\Models\Parameter;
use App\Models\Source;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use App\Repositories\ReportRepository;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinancePayOrderFinancePaymentExecute;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Models\FinancePaymentDeduction;
use Modules\Finance\Models\FinancePaymentExecute;

/**
 * @class FinancePayOrderController
 * @brief Gestiona las ordenes de pago
 *
 * Establece los métodos a implementar en la gestión de órdenes de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePayOrderController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validate_rules
     */
    public $validate_rules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

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
        $this->middleware('permission:finance.payorder.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:finance.payorder.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:finance.payorder.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:finance.payorder.delete', ['only' => 'destroy']);
        $this->middleware('permission:finance.payorder.cancel', ['only' => 'cancelPayOrder']);
        $this->middleware('permission:finance.payorder.approve', ['only' => 'changeDocumentStatus']);

        $this->validate_rules = [
            'institution_id' => ['required'],
            'ordered_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de pago')],
            'type' => ['required', Rule::in(['PR', 'NP'])],
            'documentType' => ['required'],
            'source_amount' => ['required', 'numeric', 'regex:/^\d+(\.\d{1,2})?$/'],
            'amount' => ['required', 'numeric'],
            'concept' => ['required'],
            'name_sourceable_id' => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo Institución es obligatorio.',
            'ordered_at.required' => 'El campo Fecha es obligatorio.',
            'type.required' => 'El campo Tipo de orden es obligatorio.',
            'documentType.required' => 'El campo Tipo de documento es obligatorio.',
            'source_amount.required' => 'El campo Monto es obligatorio.',
            'source_amount.regex' => 'El campo Monto debe contener como máximo dos decimales.',
            'amount.required' => 'El campo Monto a pagar es obligatorio.',
            'concept.required' => 'El campo Concepto es obligatorio.',
            'name_sourceable_id.required' => 'El campo Nombre o Razón social es obligatorio.',
        ];
    }

    /**
     * Muestra el listado de ordenes de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('finance::pay_orders.list');
    }

    /**
     * Muestra el formulario para registrar una nueva orden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::pay_orders.create-edit-form');
    }

    /**
     * Registra una nueva orden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($request->documentType != 'M') {
            $this->validate_rules['document_sourceable_id'] = ['required'];
            $this->messages['document_sourceable_id.required'] = 'El campo Nro. Documento origen es obligatorio';
        } else {
            $this->validate_rules['document_number'] = ['required'];
            $this->messages['document_number.required'] = 'El campo Nro. de Documento (solo '
            . 'para órdenes manuales) es obligatorio';
        }

        if ($request->documentType == 'T') {
            $this->validate_rules['accounting_account_id'] = ['required'];
            $this->validate_rules['receiver'] = ['required'];
            $this->validate_rules['name_sourceable_id'] = ['nullable'];
            $this->validate_rules['month'] = ['required'];
            $this->validate_rules['period'] = ['required'];

            $this->messages['accounting_account_id.required'] = 'El campo cuenta contable es obligatorio';
            $this->messages['receiver.required'] = 'El campo nombre o razón social es obligatorio';
            $this->messages['month.required'] = 'El campo Mes es obligatorio';
            $this->messages['period.required'] = 'El campo Periodo es obligatorio';
        }
        if (count($request->accountingItems) > 0) {
            $this->validate_rules['accounting.totDebit'] = ['same:accounting.totAssets'];
            $this->messages['accounting.totDebit.same'] = 'El asiento no esta balanceado, por favor verifique.';
        }

        $this->validate($request, $this->validate_rules, $this->messages);

        $codeSetting = CodeSetting::where("model", FinancePayOrder::class)->first();

        if (!$codeSetting) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]], 200);
        }

        list($year, $month, $day) = explode("-", $request->ordered_at);

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
            substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
            $currentFiscalYear->year : $year),
            FinancePayOrder::class,
            'code'
        );
        $compromise = ($request->type == 'PR')
        ? \Modules\Budget\Models\BudgetCompromise::find($request->budget_compromise_id) : null;

        $specificActionId = null;
        $codeStage = null;
        if ($compromise) {
            foreach ($compromise->budgetCompromiseDetails as $compromiseDetail) {
                $specificActionId = $compromiseDetail->budgetSubSpecificFormulation->specificAction->id;
                break;
            }
            $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');
        }

        $documentStatus = DocumentStatus::where('action', 'PR')->first(); // Estatus Por revisar = Por aprobar

        $financePayOrder = DB::transaction(
            function () use (
                $request,
                $code,
                $codeStage,
                $compromise,
                $specificActionId,
                $documentStatus,
            ) {
                //Si la orden de pago es de una retencion se verifica si el beneficiario ya ha sido registrado
                if ($request->type === 'NP' && $request->documentType == 'T') {
                    $existAccounting = $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
                    $deductions_ids = json_decode($request->deductions_ids, false);
                    $documentStatusEL = default_document_status_el();
                    $financePaymentDeductions = FinancePaymentDeduction::query()
                    ->whereIn('id', $deductions_ids)
                    ->where('document_status_id', $documentStatusEL->id)
                    ->get();

                    /*
                     | Se procede a Cambiar el status del documeto a PR = En Proceso
                     | de todas las deducciones agrupadas que se están pagando
                     */
                    foreach (
                        $financePaymentDeductions as $paymentDeduction
                    ) {
                        $paymentDeduction['document_status_id'] = $documentStatus->id;
                        $paymentDeduction->save();
                    }

                    $financePaymentDeduction = FinancePaymentDeduction::create(
                        [
                            'amount' => $request->amount ?? 0,
                            'mor' => $request->source_amount ?? 0,
                            'deduction_id' => $request->document_sourceable_id,
                            'deductions_ids' => json_encode($deductions_ids),
                            'document_status_id' =>  $documentStatus->id, //Estatus del documento establecido PR = En Proceso
                            'deducted_at' => $request->ordered_at,
                        ]
                    );

                    if (!$request->name_sourceable_id && $request->receiver != '' && $request->receiver != null) {
                        $receiver = Receiver::updateOrCreate(
                            [
                                'receiverable_type' => $request->receiver['class'],
                                'receiverable_id' => null,
                                'associateable_type' => $existAccounting
                                    ? \Modules\Accounting\Models\AccountingAccount::class : null,
                                'associateable_id' => $existAccounting ? $request->accounting_account_id : null
                            ],
                            [
                                'group' => $request->receiver['group'],
                                'description' => $request->receiver['text']
                            ]
                        );

                        $receiver->receiverable_id = $receiver->id;
                        $receiver->save();

                        $source = Source::create(
                            [
                            'receiver_id' => $receiver->id,
                            'sourceable_type' => $request->receiver['class'],
                            'sourceable_id' => $financePaymentDeduction->id,
                            ]
                        );
                    } else {
                        $receiver = Receiver::find($request->name_sourceable_id);
                    }
                } else {
                    $receiver = Receiver::find($request->name_sourceable_id);
                }

                $isCustom = false;
                if (
                    Module::has('Budget') && Module::isEnabled('Budget') ||
                    Module::has('Payroll') && Module::isEnabled('Payroll') ||
                    ($request->type === 'NP' && $request->documentType === 'T')
                ) {
                    $isCustom = match (true) {
                        ($receiver->receiverable_type == \Modules\Budget\Models\BudgetCompromise::class) => true,
                        ($receiver->receiverable_type == \Modules\Payroll\Models\PayrollConcept::class) => true,
                        ($receiver->receiverable_type == FinancePaymentDeduction::class) => true,
                        ($receiver->receiverable_type == null) => true,
                        default => false
                    };
                }

                $pendingAmount = $request->source_amount - $request->amount;
                $financePayOrder = FinancePayOrder::create([
                    'code' => $code,
                    'ordered_at' => $request->ordered_at,
                    'type' => $request->type,
                    'is_partial' => ($request->is_partial !== null) ? true : false,
                    'pending_amount' => $pendingAmount,
                    'completed' => ($pendingAmount > 0) ? false : true,
                    'document_type' => $request->documentType,
                    'document_number' => $request->document_number ??
                                        (($request->documentType == 'T' && isset($financePaymentDeduction)) ? $financePaymentDeduction->id : null),
                    'source_amount' => $request->source_amount,
                    'amount' => $request->amount,
                    'concept' => $request->concept,
                    'observations' => $request->observations,
                    'status' => 'PE', //Estatus pendiente por defecto, este estatus lo modifica la ejecución de pago
                    'budget_specific_action_id' => $specificActionId,
                    'institution_id' => $request->institution_id,
                    'document_status_id' => $documentStatus->id,
                    'currency_id' => $request->accounting['currency']['id'],
                    'name_sourceable_type' => str_replace("modules", "Modules", $isCustom == true
                        ? Receiver::class : $receiver->receiverable_type),
                    'name_sourceable_id' => $isCustom == true ? $receiver->id : $receiver->receiverable_id,
                    'document_sourceable_id' => $request->document_sourceable_id ?? null,
                    'document_sourceable_type' => $request->documentType == 'M' ? null
                    : ($request->documentType != 'T' ? \Modules\Budget\Models\BudgetCompromise::class ?? null : Deduction::class),
                    'month' => $request->documentType == 'T' ? $request->month : null,
                    'period' => $request->documentType == 'T' ? $request->period : null,
                ]);

                if (isset($compromise)) {
                    foreach ($compromise->budgetCompromiseDetails as $detail) {
                        $compromise->budgetStages()->create([
                            'code' => generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code'),
                            'registered_at' => $request->ordered_at,
                            'type' => 'CAU',
                            'amount' => $detail->amount,
                            'stageable_type' => FinancePayOrder::class,
                            'stageable_id' => $financePayOrder->id,
                        ]);
                    }
                }

                $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'SOP')->first();
                $accountEntry = \Modules\Accounting\Models\AccountingEntry::create([
                    'from_date'                      => $request->ordered_at,
                    'reference'                      => $code, //Código de la órden de pago como referencia
                    'concept'                        => $request->concept,
                    'observations'                   => $request->observations,
                    'accounting_entry_category_id'   => $accountingCategory->id,
                    'institution_id'                 => $request->institution_id,
                    'currency_id'                    => $request->accounting['currency']['id'],
                    'tot_debit'                      => $request->accounting['totDebit'],
                    'tot_assets'                     => $request->accounting['totAssets'],
                    'approved'                       => false,
                    'document_status_id'             => default_document_status_el()->id,
                ]);

                foreach ($request->accountingItems as $account) {
                    /*
                     | Se crea la relación de cuenta a ese asiento si ya existe lo actualiza,
                     | de lo contrario crea el nuevo registro de cuenta
                     */
                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                    'accounting_entry_id' => $accountEntry->id,
                    'accounting_account_id' => $account['id'],
                    'bank_reference' => $account['bank_reference'] ?? null,
                    'debit' => $account['debit'],
                    'assets' => $account['assets'],
                    ]);
                }

                /* Crea la relación entre el asiento contable y el registro de orden de pago */
                \Modules\Accounting\Models\AccountingEntryable::create([
                    'accounting_entry_id' => $accountEntry->id,
                    'accounting_entryable_type' => FinancePayOrder::class,
                    'accounting_entryable_id' => $financePayOrder->id
                ]);

                return $financePayOrder;
            }
        );

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json(['record' => $financePayOrder, 'message' => 'Success'], 200);
    }

    /**
     * Muestra detalles de una órden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador de la orden de pago
     *
     * @return    void
     */
    public function show($id)
    {
        //
    }

    /**
     * Muestra el formulario para la actualización de datos de la órden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador de la orden de pago
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $payOrder = FinancePayOrder::find($id);
        $registeredAccounts = \Modules\Accounting\Models\AccountingEntryable::with('accountingEntry.accountingAccounts')
            ->where('accounting_entryable_type', FinancePayOrder::class)
            ->where('accounting_entryable_id', $id)
            ->first();

        return view('finance::pay_orders.create-edit-form', compact('payOrder', 'registeredAccounts'));
    }

    /**
     * Actualiza los datos de una órden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request   $request   Datos de la petición
     * @param     integer   $id        Identificador de la orden de pago
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $financePayOrder = FinancePayOrder::find($id);

        if ($request->documentType != 'M') {
            $this->validate_rules['document_sourceable_id'] = ['required'];
            $this->messages['document_sourceable_id.required'] = 'El campo Nro. Documento origen es obligatorio';
        } else {
            $this->validate_rules['document_number'] = ['required'];
            $this->messages['document_number.required'] = 'El campo Nro. de Documento '
                . '(solo para órdenes manuales) es obligatorio';
        }
        if ($request->documentType == 'T') {
            $this->validate_rules['accounting_account_id'] = ['required'];
            $this->validate_rules['receiver'] = ['required'];
            $this->validate_rules['name_sourceable_id'] = ['nullable'];
            $this->validate_rules['month'] = ['required'];
            $this->validate_rules['period'] = ['required'];

            $this->messages['accounting_account_id.required'] = 'El campo cuenta contable es obligatorio';
            $this->messages['receiver.required'] = 'El campo nombre o razón social es obligatorio';
            $this->messages['month.required'] = 'El campo Mes es obligatorio';
            $this->messages['period.required'] = 'El campo Periodo es obligatorio';
        }

        if (count($request->accountingItems) > 0) {
            $this->validate_rules['accounting.totDebit'] = ['same:accounting.totAssets'];
            $this->messages['accounting.totDebit.same'] = 'El asiento no esta balanceado, por favor verifique.';
        }

        $this->validate($request, $this->validate_rules, $this->messages);

        if ($financePayOrder->is_payroll_contribution === true) {
            $financePayOrder->ordered_at = $request->ordered_at;
            $financePayOrder->observations = $request->observations;
            $financePayOrder->save();

            $request->session()->flash('message', ['type' => 'update']);
            return response()->json(['record' => $financePayOrder, 'message' => 'Success'], 200);
        }

        $specificActionId = null;
        $compromise = null;
        $isCustom = false;

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $compromise = ($request->type == 'PR')
            ? \Modules\Budget\Models\BudgetCompromise::find($request->budget_compromise_id) : null;

            if ($compromise) {
                foreach ($compromise->budgetCompromiseDetails as $compromiseDetail) {
                    $specificActionId = $compromiseDetail->budgetSubSpecificFormulation->specificAction->id;
                    break;
                }
            }
        }

        DB::transaction(function () use (
            $request,
            $financePayOrder,
            $specificActionId,
            $isCustom,
            $compromise,
        ) {
            $documentStatus = default_document_status();//Status del docmento en PR = En Proceso

            //Si la orden de pago es de una retencion se verifica si el beneficiario ya ha sido registrado
            if ($request->type === 'NP' && $request->documentType === 'T') {
                $existAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
                $deductions_ids = json_decode($request->deductions_ids, false);
                $documentStatusEL = default_document_status_el();

                $financePaymentDeduction = FinancePaymentDeduction::query()
                ->where('id', $financePayOrder->document_number)
                ->where('document_status_id', $documentStatus->id)
                ->first();

                /*
                 | Si Existe la deducción que agrupara varias deducciones se procede a actualizar el estatus
                 | Y se elimina la antigua deducción.
                 */
                if (isset($financePaymentDeduction)) {
                    $financePaymentDeductions = FinancePaymentDeduction::query()
                    ->whereIn('id', json_decode($financePaymentDeduction->deductions_ids))
                    ->where('document_status_id', $documentStatus->id)
                    ->get();

                    /*
                     | Se procede a Cambiar el status del documeto a EL = Elaborado(a)
                     | de todas las deducciones agrupadas que se están pagando
                     */
                    foreach (
                        $financePaymentDeductions as $paymentDeduction
                    ) {
                        $paymentDeduction['document_status_id'] = $documentStatusEL->id;
                        $paymentDeduction->save();
                    }
                    $financePaymentDeduction->delete();
                }

                /*
                 | Actualiza el estado del documento de las deducciones pretenecientes
                 | y crea una nueva deducción en grupo.
                 */
                $financePaymentDeductions = FinancePaymentDeduction::query()
                    ->whereIn('id', $deductions_ids)
                    ->where('document_status_id', $documentStatusEL->id)
                    ->get();

                    /*
                     | Se procede a Cambiar el status del documeto a PR = En Proceso
                     | de todas las deducciones agrupadas que se están pagando
                     */
                foreach (
                    $financePaymentDeductions as $paymentDeduction
                ) {
                    $paymentDeduction['document_status_id'] = $documentStatus->id;
                    $paymentDeduction->save();
                }

                $financePaymentDeduction = FinancePaymentDeduction::create(
                    [
                        'amount' => $request->amount ?? 0,
                        'mor' => $request->source_amount ?? 0,
                        'deduction_id' => $request->document_sourceable_id,
                        'deductions_ids' => json_encode($deductions_ids),
                        'document_status_id' =>  $documentStatus->id, //Estatus del documento establecido PR = En Proceso
                        'deducted_at' => $request->paid_at,
                    ]
                );

                if (!$request->name_sourceable_id && $request->receiver != '' && $request->receiver != null) {
                    $receiver = Receiver::updateOrCreate(
                        [
                            'receiverable_type' => $request->receiver['class'],
                            'associateable_type' => $existAccounting
                                ? \Modules\Accounting\Models\AccountingAccount::class : null,
                            'associateable_id' => $existAccounting ? $request->accounting_account_id : null
                        ],
                        [
                            'receiverable_id' => null,
                            'group' => $request->receiver['group'],
                            'description' => $request->receiver['text']
                        ]
                    );

                    $receiver->receiverable_id = $receiver->id;
                    $receiver->save();

                    $source = Source::create(
                        [
                        'receiver_id' => $receiver->id,
                        'sourceable_type' => $request->receiver['class'],
                        'sourceable_id' => $financePaymentDeduction->id,
                        ]
                    );
                } else {
                    $receiver = Receiver::find($request->name_sourceable_id);
                }
            } else {
                $receiver = Receiver::find($request->name_sourceable_id);
            }

            if (
                Module::has('Budget') && Module::isEnabled('Budget') ||
                Module::has('Payroll') && Module::isEnabled('Payroll') ||
                ($request->type === 'NP' && $request->documentType == 'T')
            ) {
                $isCustom = match (true) {
                    ($receiver->receiverable_type == \Modules\Budget\Models\BudgetCompromise::class) => true,
                    ($receiver->receiverable_type == \Modules\Payroll\Models\PayrollConcept::class) => true,
                    ($receiver->receiverable_type == FinancePaymentDeduction::class) => true,
                    ($receiver->receiverable_type == null) => true,
                    default => false
                };
            }

            $pendingAmount = $request->source_amount - $request->amount;
            $financePayOrder->ordered_at = $request->ordered_at;
            $financePayOrder->type = $request->type;
            $financePayOrder->is_partial = ($request->is_partial !== null) ? true : false;
            $financePayOrder->pending_amount = $pendingAmount;
            $financePayOrder->completed = ($pendingAmount > 0) ? false : true;
            $financePayOrder->document_type = $request->documentType;
            $financePayOrder->document_number = $request->document_number ??
                (($request->documentType == 'T' && isset($financePaymentDeduction)) ? $financePaymentDeduction->id : null);
            $financePayOrder->source_amount = $request->source_amount;
            $financePayOrder->amount = $request->amount;
            $financePayOrder->concept = $request->concept;
            $financePayOrder->observations = $request->observations;
            $financePayOrder->budget_specific_action_id = $specificActionId;
            $financePayOrder->institution_id = $request->institution_id;
            $financePayOrder->currency_id = $request->accounting['currency']['id'];
            $financePayOrder->name_sourceable_type = str_replace("modules", "Modules", $isCustom == true
                ? Receiver::class : $receiver->receiverable_type);
            $financePayOrder->name_sourceable_id = $isCustom == true ? $receiver->id : $receiver->receiverable_id;
            $financePayOrder->document_sourceable_id = $request->document_sourceable_id ?? null;
            $financePayOrder->document_sourceable_type = $request->documentType == 'M' ? null
                    : ($request->documentType != 'T' ? \Modules\Budget\Models\BudgetCompromise::class ?? null : Deduction::class);
            $financePayOrder->month = $request->documentType == 'T' ? $request->month : null;
            $financePayOrder->period = $request->documentType == 'T' ? $request->period : null;
            $financePayOrder->save();

            if (Module::has('Budget') && Module::isEnabled('Budget')) {
                $budgetStage = \Modules\Budget\Models\BudgetStage::where('stageable_type', FinancePayOrder::class)
                    ->where('stageable_id', $financePayOrder->id)->delete();
                if (isset($compromise)) {
                    foreach ($compromise->budgetCompromiseDetails as $detail) {
                        $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');
                        $compromise->budgetStages()->create([
                            'code' => $codeStage,
                            'registered_at' => $request->ordered_at,
                            'type' => 'CAU',
                            'amount' => $detail->amount,
                            'stageable_type' => FinancePayOrder::class,
                            'stageable_id' => $financePayOrder->id,
                        ]);
                    }
                }
            }

            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePayOrder->code)->first();
                $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'SOP')->first();

                $accountEntry->from_date                      = $request->ordered_at;
                $accountEntry->concept                        = $request->concept;
                $accountEntry->observations                   = $request->observations;
                $accountEntry->accounting_entry_category_id   = $accountingCategory->id;
                $accountEntry->institution_id                 = $request->institution_id;
                $accountEntry->currency_id                    = $request->accounting['currency']['id'];
                $accountEntry->tot_debit                      = $request->accounting['totDebit'];
                $accountEntry->tot_assets                     = $request->accounting['totAssets'];
                $accountEntry->document_status_id             = default_document_status_el()->id;
                $accountEntry->save();

                /* Se eliminan las cuentas anteriores del asiento contable */
                $accountingEntryAccounts = \Modules\Accounting\Models\AccountingEntryAccount::where('accounting_entry_id', $accountEntry->id)
                    ->delete();

                foreach ($request->accountingItems as $account) {
                    /* Se crea la relación con las nuevas cuentas para ese asiento contable */
                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $account['id'],
                        'debit' => $account['debit'],
                        'assets' => $account['assets'],
                    ]);
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['record' => $financePayOrder, 'message' => 'Success'], 200);
    }

    /**
     * Elimina una órden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador de la orden de pago
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $financePayOrder = FinancePayOrder::find($id);

        if ($financePayOrder) {
            $financePayOrder->delete();
        }

        return response()->json(['record' => $financePayOrder, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de documentos para los cuales ordenar pago
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSourceDocuments(Request $request)
    {
        ini_set('max_execution_time', 1000); /** 10min */
        if (isset($request->documentType) && isset($request->ordered_at) && isset($request->type)) {
            $payOrder = null;
            $totals = [];

            if (isset($request->document_sourceable_id)) {
                $payOrder = FinancePayOrder::where('document_sourceable_type', \Modules\Budget\Models\BudgetCompromise::class)
                    ->where('document_sourceable_id', $request->document_sourceable_id)
                    ->where('id', $request->id)
                    ->first();
            }
            $documentStatus = DocumentStatus::where('action', 'AP')->first();
            list($year, $month, $day) = explode('-', $request->ordered_at);

            $data = [['id' => '', 'text' => 'Seleccione...']];
            if (
                $request->type === 'PR' &&
                Module::has('Budget') &&
                Module::isEnabled('Budget') &&
                Module::has('Purchase') &&
                Module::isEnabled('Purchase')
            ) {
                $compromises = \Modules\Budget\Models\BudgetCompromise::query()
                    ->whereHas(
                        'budgetCompromiseDetails',
                        function ($q) use ($year) {
                            $q->whereHas(
                                'budgetSubSpecificFormulation',
                                fn ($qq) => $qq->where(['assigned' => true, 'year' => $year])
                            )->whereNull('document_status_id');
                        }
                    )->where('document_status_id', $documentStatus->id)
                    ->where('compromised_at', '<=', $request->ordered_at)
                    ->when('M' == $request->documentType, function ($query) {
                        $query->doesnthave('sourceable');
                    })
                    ->when('P' == $request->documentType, function ($query) {
                        $query->whereNull('sourceable_type')->whereNull('sourceable_id')
                        ->whereNull('compromiseable_type')->whereNull('compromiseable_id');
                    })
                    ->when('C' == $request->documentType, function ($query) {
                        $query->whereHas('sourceable')
                            ->whereNotNull('sourceable_type')->whereNotNull('sourceable_id')
                            ->whereNull('compromiseable_type')->whereNull('compromiseable_id')
                            ->where('sourceable_type', \Modules\Purchase\Models\PurchaseDirectHire::class);
                    })
                    ->when(!in_array($request->documentType, ['M', 'P', 'C']), function ($query) use ($request) {
                        $query->whereHas('sourceable')
                            ->whereNotNull('sourceable_type')->whereNotNull('sourceable_id')
                            ->where('sourceable_type', '!=', \Modules\Purchase\Models\PurchaseDirectHire::class)
                            ->whereDoesntHave('financePayOrders')
                            ->orWhere('id', $request->document_sourceable_id);
                    })
                    ->get();

                $currency = '';
                $amounts = [];
                $totalDeductionAmount = [];
                $deducctionAccounts = [];
                $accountingAccountIds = [];

                foreach ($compromises as $cKey => $compromise) {
                    $compromiseDetails = $compromise->budgetCompromiseDetails()
                        ->with('budgetAccount.accountable.accountingAccount')->get();

                    $deducctionAmount = 0;
                    foreach ($compromiseDetails as $det => $detail) {
                        if (!array_key_exists($cKey . $detail['budget_account_id'], $amounts)) {
                            $amounts[$cKey . $detail['budget_account_id']] = (float)$detail['amount'];
                        } else {
                            $amounts[$cKey . $detail['budget_account_id']] += ((float)$detail['amount']);
                        }
                    }
                }

                foreach ($compromises as $key => $compromise) {
                    $accounting_accounts = [];
                    $compromiseStages = $compromise->budgetStages()->where('type', '!=', 'PAG')->get();
                    $compromiseDetails = $compromise->budgetCompromiseDetails()
                        ->with('budgetAccount.accountable.accountingAccount')->get();
                    $total = 0;
                    $hasOrder = false;

                    foreach ($compromiseStages as $stage) {
                        if ($stage->type == 'CAU') {
                            $total -= $stage->amount;

                            if (!isset($payOrder)) {
                                $hasOrder = true;
                            }
                        }
                    }

                    foreach ($compromiseDetails as $detail) {
                        if ($hasOrder == false) {
                            if (empty($currency)) {
                                $currency = $detail->budgetSubSpecificFormulation->currency;
                            }

                            $total += ((float)$detail['tax_amount'] > 0 ? (float)$detail['tax_amount'] : (float)$detail['amount']);

                            foreach ($detail['budgetAccount']['accountable'] as $accountable) {
                                if (!array_key_exists($accountable['accountable_id'], $accounting_accounts)) {
                                    $amount = ((float)$amounts[$key . $detail['budget_account_id']] /
                                        count($detail['budgetAccount']['accountable']));

                                    $arrayData = [
                                        'account' => $accountable['accountingAccount']['id'],
                                        'amount' => $amount,
                                    ];

                                    $accounting_accounts[$accountable['accountable_id']] = $arrayData;
                                }
                            }
                        }
                    }

                    if (isset($payOrder) && $compromise->id === $payOrder->document_sourceable_id) {
                        $total = $payOrder->amount;
                        $payOrder = null;
                    }

                    $description = '';
                    if ($compromise->sourceable_type == \Modules\Purchase\Models\PurchaseDirectHire::class) {
                        $pOrder = \Modules\Purchase\Models\PurchaseDirectHire::find($compromise->sourceable_id);
                        $description = $pOrder->description;
                    } else {
                        $description = strip_tags($compromise->description);
                    }

                    if ($total > 0) {
                        $data[] = [
                            'id' => $compromise->id, 'text' => $compromise->document_number,
                            'budget_compromise_id' => $compromise->id, 'budget_total_amount' => $total,
                            'currency' => $currency, 'accounting_accounts' => $accounting_accounts,
                            'receiver' => $compromise->receiver, 'description' => $description
                        ];
                    }
                }
            } elseif (
                $request->type === 'NP' &&
                    $request->documentType === 'T' &&
                    $request->month &&
                    $request->period &&
                    Module::has('Accounting')
            ) {
                // Se establece el perido de busqueda dado por el mes y el periodo seleccionados.
                [$startDate, $endDate] = $this->getPeriod($request->month, $request->period);

                //Se establecen los estatus del documento.
                $documentStatusPR = default_document_status();
                $documentStatusEL = default_document_status_el();
                $load_records = true;

                if ($request->edit) {
                    $payOrderD = FinancePayOrder::query()
                        ->with('nameSourceable')
                        ->where('document_sourceable_type', Deduction::class)
                        ->where('document_sourceable_id', $request->document_sourceable_id)
                        ->where('id', $request->id)
                        ->first();

                    if (isset($payOrderD) && $payOrderD->document_number) {
                        $deductionEdit = FinancePaymentDeduction::query()
                        ->with('deduction')
                        ->whereNotNull('deductions_ids')
                        ->where('id', $payOrderD->document_number)
                        ->first();

                        if ($request->month == $payOrderD->month && $request->period == $payOrderD->period) {
                            if (isset($deductionEdit)) {
                                $data[] = [
                                    'id' => $deductionEdit->deduction_id,
                                    'text' => $deductionEdit->deduction->name . ' ' .
                                    'del ' . date('d/m/Y', strtotime($startDate)) .
                                    ' al ' . date('d/m/Y', strtotime($endDate)),
                                    'budget_compromise_id' => '',
                                    'budget_total_amount' => $deductionEdit->amount,
                                    'currency' => Currency::query()->where('default', true)->first(),
                                    'accounting_accounts' => [
                                        $deductionEdit->deduction->accounting_account_id => [
                                            'account' => $deductionEdit->deduction->accounting_account_id,
                                            'amount' => $deductionEdit->amount
                                        ]],
                                    'receiver' => $payOrderD->nameSourceable->toArray(),
                                    'deductions_ids' => $deductionEdit->deductions_ids,
                                    'end_date' => $endDate,
                                ];
                                /*
                                 | Se busacan retenciones que estan pendientes que no estén dentro de 'deductions_ids'
                                 | y que tengan dean del mismo tipo de deducción.
                                 */
                                $finance_deduction_ = FinancePaymentDeduction::query()
                                ->whereHas('financePaymentExecute')
                                ->with(['deduction'])
                                ->whereBetween('deducted_at', [
                                    $startDate,
                                    $endDate
                                ])
                                ->whereNotIn('id', json_decode($deductionEdit->deductions_ids))
                                ->where('deduction_id', $deductionEdit->deduction_id)
                                ->where('document_status_id', default_document_status_el()->id) // Todas las deducciones que se encuentren en estatus EL = Elaborada(a)
                                ->selectRaw(
                                    'deduction_id,
                                    SUM(amount) as amount,
                                    STRING_AGG(id::text, \',\') as deductions_ids'
                                )
                                ->groupBy('deduction_id')
                                ->first();

                                if (isset($finance_deduction_)) {
                                    $deductions_ids = explode(',', $finance_deduction_->deductions_ids);
                                    $data_deductions_ids = json_decode($data[1]['deductions_ids']);
                                    $deductions_ids = array_unique(array_merge($deductions_ids, $data_deductions_ids));
                                    foreach ($data[1]['accounting_accounts'] as $key => $value) {
                                        $data[1]['accounting_accounts'][$key]['amount'] += $finance_deduction_->amount;
                                    }
                                    $data[1] = [
                                        'id' => $data[1]['id'],
                                        'text' => $data[1]['text'],
                                        'budget_compromise_id' => '',
                                        'budget_total_amount' => $data[1]['budget_total_amount'] + $finance_deduction_->amount,
                                        'currency' => $data[1]['currency'],
                                        'accounting_accounts' => $data[1]['accounting_accounts'],
                                        'receiver' => $data[1]['receiver'],
                                        'deductions_ids' => json_encode($deductions_ids),
                                        'end_date' => $endDate,
                                    ];
                                }
                                /*
                                 | Se busacan retenciones que estan en estatus EL
                                 | de los demás tipos de deducciones.
                                */
                                $deductionsGroup = FinancePaymentDeduction::query()
                                ->whereHas('financePaymentExecute')
                                ->with(['deduction'])
                                ->whereBetween('deducted_at', [
                                    $startDate,
                                    $endDate
                                ])
                                ->where('deduction_id', '!=', $deductionEdit->deduction_id)
                                ->where('document_status_id', default_document_status_el()->id) // Todas las deducciones que se encuentren en estatus EL = Elaborada(a)
                                ->selectRaw(
                                    'deduction_id,
                                    SUM(amount) as amount,
                                    STRING_AGG(id::text, \',\') as deductions_ids'
                                )
                                ->groupBy('deduction_id')
                                ->get();

                                foreach ($deductionsGroup as $finance_deduction) {
                                    $data[] = [
                                        'id' => $finance_deduction->deduction_id,
                                        'text' => $finance_deduction->deduction->name . ' ' .
                                        'del ' . date('d/m/Y', strtotime($startDate)) .
                                        ' al ' . date('d/m/Y', strtotime($endDate)),
                                        'budget_compromise_id' => $finance_deduction->deduction_id,
                                        'budget_total_amount' => $finance_deduction->amount,
                                        'currency' => Currency::query()->where('default', true)->first(),
                                        'accounting_accounts' => [
                                            $finance_deduction->deduction->accounting_account_id => [
                                                'account' => $finance_deduction->deduction->accounting_account_id,
                                                'amount' => $finance_deduction->amount
                                            ]],
                                        'receiver' => [],
                                        'deductions_ids' => json_encode(explode(',', $finance_deduction->deductions_ids)),
                                        'end_date' => $endDate,
                                    ];
                                }
                                $load_records = false;
                            }
                        } elseif ($request->month == $payOrderD->month && $request->period != $payOrderD->period) {
                            /*
                             | Si Existe la deducción que agrupa varias deducciones se procede a actualizar el estatus
                             | de las deducciones agrupadas que se encuentren en estatus PR
                             */

                            try {
                                DB::transaction(function () use ($deductionEdit, $documentStatusPR, $documentStatusEL) {
                                    if (isset($deductionEdit)) {
                                        $financePaymentDeductions = FinancePaymentDeduction::query()
                                        ->whereIn('id', json_decode($deductionEdit->deductions_ids))
                                        ->whereNull('deductions_ids')
                                        ->where('document_status_id', $documentStatusPR->id)
                                        ->get();

                                        /*
                                         | Se procede a Cambiar el status del documeto a EL = Elaborado(a)
                                         | de todas las deducciones.
                                         */
                                        foreach (
                                            $financePaymentDeductions as $paymentDeduction
                                        ) {
                                            $paymentDeduction['document_status_id'] = $documentStatusEL->id;
                                            $paymentDeduction->save();
                                        }
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
                        } elseif ($request->month != $payOrderD->month && $request->period != $payOrderD->period) {
                            /**
                             * @todo Devolver registros que no sean del mismo mes ni del mismo periodo
                             */
                        }
                    }
                }

                if ($load_records) {
                    $deductionsGroup = FinancePaymentDeduction::query()
                    ->whereHas('financePaymentExecute')
                    ->with(['deduction'])
                    ->whereBetween('deducted_at', [
                        $startDate,
                        $endDate
                    ])
                    ->where('document_status_id', default_document_status_el()->id) // Todas las deducciones que se encuentren en estatus EL = Elaborada(a)
                    ->selectRaw(
                        'deduction_id,
                        SUM(amount) as amount,
                        STRING_AGG(id::text, \',\') as deductions_ids'
                    )
                    ->groupBy('deduction_id')
                    ->get();

                    foreach ($deductionsGroup as $finance_deduction) {
                        $data[] = [
                            'id' => $finance_deduction->deduction_id,
                            'text' => $finance_deduction->deduction->name . ' ' .
                            'del ' . date('d/m/Y', strtotime($startDate)) .
                            ' al ' . date('d/m/Y', strtotime($endDate)),
                            'budget_compromise_id' => '',
                            'budget_total_amount' => $finance_deduction->amount,
                            'currency' => Currency::query()->where('default', true)->first(),
                            'accounting_accounts' => [
                                $finance_deduction->deduction->accounting_account_id => [
                                    'account' => $finance_deduction->deduction->accounting_account_id,
                                    'amount' => $finance_deduction->amount
                                ]],
                            'receiver' => [],
                            'deductions_ids' => json_encode(explode(',', $finance_deduction->deductions_ids)),
                            'end_date' => $endDate,
                        ];
                    }
                }
            }
            return response()->json(['records' => $data], 200);
        } else {
            return response()->json(['records' => ['id'   => '','text' => 'Seleccione...']], 200);
        }
    }

    /**
     * Cálcula los montos de las cuentas Contables
     *
     * @param mixed $compromise Datos del compromiso
     * @param array $accountingAccounts Datos de las cuentas contables
     * @param Currency $currency Datos de la Moneda
     *
     * @return array
     */
    public function accountingAmountsCalc($compromise, $accountingAccounts, $currency)
    {
        $accountingAmounts = [];

        foreach ($accountingAccounts as $key => $account) {
            $accountingAmounts[$key] = $account['amount'];
        }

        $positiveSum = 0;
        foreach ($accountingAmounts as $value) {
            if ($value > 0) {
                $positiveSum += $value;
            }
        }

        $positiveCount = count(array_filter($accountingAmounts, function ($value) {
            return $value > 0;
        }));

        $negativeCount = count(array_filter($accountingAmounts, function ($value) {
            return $value < 0;
        }));

        if ($negativeCount > 0) {
            $amountToDistribute = abs(array_sum(array_filter($accountingAmounts, function ($value) {
                return $value < 0;
            }))) / $positiveCount;

            foreach ($accountingAmounts as &$value) {
                if ($value > 0) {
                    $value -= (float)currency_format($amountToDistribute, $currency->decimal_places);
                }
            }
        }

        return $accountingAmounts;
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $records = FinancePayOrder::query()
            ->with([
                'institution:id,name',
                'documentStatus:id,name,action',
                'nameSourceable',
                'documentSourceable',
                'currency:id,name,symbol,decimal_places',
            ])
            ->orderBy('id')
            ->search($request->query('query'))
            ->paginate($request->limit ?? 10);

        $total = $records->total();
        $records = $records->each(function ($record) {
            if ($record->month && $record->period) {
                [$startDate, $endDate] = $this->getPeriod($record->month, $record->period);
                $record->start_date = date('d/m/Y', strtotime($startDate));
                $record->end_date = date('d/m/Y', strtotime($endDate));
                if ($record->document_sourceable_type == Deduction::class && $record->document_number) {
                    $deductions = FinancePaymentDeduction::query()
                    ->where('id', $record->document_number)
                    ->first();
                    $record->deductions_ids = $deductions->deductions_ids ?? '';
                }
            }
        });

        return response()->json([
            'data' => $records->toArray(),
            'count' => $total,
            'cancelPayOrderPermission' => auth()->user()->hasPermission('finance.payorder.cancel'),
            'approvePayOrderPermission' => auth()->user()->hasPermission('finance.payorder.approve'),
        ], 200);
    }

    /**
     * Listado con órdenes de pago pendientes por cancelar
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPendingPayOrders($receiver_ids = null, $currency_id = null, $is_update = null)
    {
        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $documentStatus = DocumentStatus::where('action', 'AP')->first();
        if ($receiver_ids === null && $currency_id === null) {
            $financePayOrders = FinancePayOrder::with(
                'budgetSpecificAction',
                'institution',
                'documentStatus',
                'nameSourceable',
                'documentSourceable',
                'currency'
            )
            ->whereIn('status', ['PE', 'PP'])
            ->where('document_status_id', $documentStatus->id)
            ->where('institution_id', $institution->id)->orderBy('code')->get();
        } else {
            $receiverIds = explode(',', $receiver_ids);
            $receivers = Receiver::query()
                ->whereIn('id', $receiverIds)
                ->toBase()
                ->get();

            $receiverableIds = [];

            foreach ($receivers as $receiver) {
                $receiverableIds[] = $receiver->receiverable_id;
            }

            $isCustom = false;

            if (
                Module::has('Budget') && Module::isEnabled('Budget') ||
                Module::has('Payroll') && Module::isEnabled('Payroll')
            ) {
                if (count($receivers) > 0) {
                    $isCustom = match (true) {
                        ($receivers[0]->receiverable_type == \Modules\Budget\Models\BudgetCompromise::class) => true,
                        ($receivers[0]->receiverable_type == \Modules\Payroll\Models\PayrollConcept::class) => true,
                        ($receivers[0]->receiverable_type == FinancePaymentDeduction::class) => true,
                        ($receivers[0]->receiverable_type == null) => true,
                        default => false
                    };
                }
            }

            $financePayOrders = FinancePayOrder::with(
                'budgetSpecificAction',
                'institution',
                'documentStatus',
                'nameSourceable',
                'documentSourceable',
                'currency'
            )
            ->whereIn('status', ['PE', 'PP'])
            ->where([
                'name_sourceable_type' => $isCustom ? Receiver::class : $receivers[0]->receiverable_type,
                'currency_id' => $currency_id
            ])
            ->whereIn('name_sourceable_id', $isCustom ? $receiverIds : $receiverableIds)
            ->where('institution_id', $institution->id)
            ->where('document_status_id', $documentStatus->id)
            ->orderBy('code')
            ->get();
        }

        $options = [['id'   => '', 'text' => 'Seleccione...']];

        foreach ($financePayOrders as $financePayOrder) {
            $orders = FinancePayOrderFinancePaymentExecute::with('financePaymentExecute')
            ->where('finance_pay_order_id', $financePayOrder->id)->get();
            $oldAmount = 0;

            foreach ($orders as $order) {
                $oldAmount += $order->financePaymentExecute->status == 'AN' ? 0
                : ($order->financePaymentExecute->paid_amount + $order->financePaymentExecute->deduction_amount);
            }
            if (isset($is_update)) {
                $orders = FinancePayOrderFinancePaymentExecute::with('financePaymentExecute')
                ->where('finance_pay_order_id', $financePayOrder->id)
                ->where('finance_payment_execute_id', $is_update)->get();
                $updateAmount = 0;

                foreach ($orders as $order) {
                    $updateAmount += $order->financePaymentExecute->paid_amount
                        + $order->financePaymentExecute->deduction_amount;
                }
            }

            if (isset($is_update)) {
                $totalAmount = $financePayOrder->amount - $oldAmount + $updateAmount;
            } else {
                $totalAmount = $financePayOrder->amount - $oldAmount;
            }
            if ($totalAmount > 0) {
                array_push($options, [
                    'id' => $financePayOrder->id,
                    'text' => $financePayOrder->code,
                    'amount' => $totalAmount,
                    'ordered_at' => $financePayOrder->ordered_at,
                    'budgetSpecificAction' => $financePayOrder->budgetSpecificAction,
                    'institution' => $financePayOrder->institution,
                    'documentStatus' => $financePayOrder->documentStatus,
                    'nameSourceable' => $financePayOrder->nameSourceable,
                    'documentSourceable' => $financePayOrder->documentSourceable,
                    'currency' => $financePayOrder->currency,
                    'is_deduction' => $financePayOrder->document_sourceable_type == Deduction::class,
                ]);
            }
        }
        return response()->json(['records' => $options], 200);
    }

    /**
     * Establece el nuevo estatus del documento
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeDocumentStatus(Request $request)
    {
        try {
            $documentStatus = DocumentStatus::query()->where('action', $request->action)->firstOrFail();
            $financePayOrder = FinancePayOrder::query()->findOrFail($request->id);

            $accountingEntryable = \Modules\Accounting\Models\AccountingEntryable::query()
            ->where([
                'accounting_entryable_type' => FinancePayOrder::class,
                'accounting_entryable_id'   => $financePayOrder->id
            ])->firstOrFail();

            DB::transaction(function () use ($documentStatus, $financePayOrder, $accountingEntryable) {
                $financePayOrder->document_status_id = $documentStatus->id;
                $financePayOrder->save();

                \Modules\Accounting\Models\AccountingEntry::query()
                ->orWhere([
                    'id'                 => $accountingEntryable->accounting_entry_id,
                    'reference'          => $financePayOrder->code,
                ])->firstOrFail()
                ->update(['document_status_id' => default_document_status()->id]);
            });

            $financePayOrder = FinancePayOrder::with(
                'budgetSpecificAction',
                'institution',
                'documentStatus'
            )->where('id', $request->id)->firstOrFail();

            return response()->json(['record' => $financePayOrder, 'message' => 'Success'], 200);
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
    }

    /**
     * Genera el reporte de la orden de pago
     *
     * @param integer $id ID de la orden de pago
     *
     * @return void
     */
    public function pdf($id)
    {
        $financePayOrder = FinancePayOrder::with(
            'institution',
            'currency',
            'budgetSpecificAction',
            'documentSourceable',
            'nameSourceable'
        )->find($id);

        $budjetProjectAcc = null;
        $specificAction = null;
        $deductions = null;
        if ($financePayOrder) {
            if ($financePayOrder->budgetSpecificAction) {
                $budjetProjectAcc = $financePayOrder->budgetSpecificAction->specificable->getTable();
                $specificAction = [
                    'type' => ($budjetProjectAcc === 'budget_projects') ? 'Proyecto' : 'Acción Centralizada',
                    'code' => $financePayOrder->budgetSpecificAction->specificable->code . ' - ' .
                        $financePayOrder->budgetSpecificAction->code
                ];
            }
            if ($financePayOrder->month && $financePayOrder->period) {
                list($startDate, $endDate) = $this->getPeriod($financePayOrder->month, $financePayOrder->period);
                $financePayOrder['startDate'] = date('d/m/Y', strtotime($startDate));
                $financePayOrder['endDate'] = date('d/m/Y', strtotime($endDate));

                if ($financePayOrder->document_sourceable_type == Deduction::class && $financePayOrder->document_number) {
                    $deductionsIds = FinancePaymentDeduction::query()
                    ->where('id', $financePayOrder->document_number)
                    ->first();
                    $deductions_ids = json_decode($deductionsIds->deductions_ids);
                    $deductions = FinancePaymentDeduction::query()
                    ->with('deduction')
                    ->whereIn('id', $deductions_ids)
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc')
                    ->get()
                    ->map(function ($deduc) {
                        return [
                            'id' => $deduc->id,
                            'name' => $deduc->deduction->name,
                            'amount' => $deduc->amount,
                            'deducted_at' => date('d/m/Y', strtotime($deduc->deducted_at)),
                        ];
                    });
                }
            }

            $accountingEntry = \Modules\Accounting\Models\AccountingEntry::with(['accountingAccounts' => function ($q) {
                $q->with('account');
            }])->where('reference', $financePayOrder->code)->first();

            $accountable = [];

            if ($financePayOrder->documentSourceable) {
                $budgetCompromise = \Modules\Budget\Models\BudgetCompromise::with('budgetCompromiseDetails')
                    ->find($financePayOrder->documentSourceable->id);

                if ($accountingEntry) {
                    foreach ($accountingEntry->accountingAccounts as $entryAccount) {
                        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
                            $code = CodeSetting::where('table', 'payrolls')
                                ->first();

                            if ($code) {
                                $regexPattern = '/' . $code->format_prefix . '-\d+-\d{4}/';

                                if (
                                    preg_match($regexPattern, $financePayOrder->concept, $matches)
                                    && str_contains($financePayOrder->concept, 'aportes de nómina')
                                ) {
                                    $match = $matches[0];

                                    $compromise = \Modules\Budget\Models\BudgetCompromise::with('budgetCompromiseDetails')
                                        ->where('document_number', $match)
                                        ->first();

                                    $compromiseAccountableAccounts = \Modules\Accounting\Models\Accountable::whereIn('accountable_id', $compromise
                                        ->budgetCompromiseDetails
                                        ->pluck('budget_account_id'))
                                        ->where('accounting_account_id', $entryAccount->accounting_account_id)
                                        ->with('accountable')
                                        ->get();

                                    if (
                                        count($compromiseAccountableAccounts) > 0
                                        && !array_key_exists($entryAccount->accounting_account_id, $accountable)
                                    ) {
                                        $accountable[$entryAccount->accounting_account_id] = $compromiseAccountableAccounts;
                                    }
                                }
                            }
                        }

                        $accountableAccounts = \Modules\Accounting\Models\Accountable::whereIn('accountable_id', $budgetCompromise
                            ?->budgetCompromiseDetails
                            ->pluck('budget_account_id') ?? [])
                            ->where('accounting_account_id', $entryAccount->accounting_account_id)
                            ->with('accountable')
                            ->get();

                        if (
                            count($accountableAccounts) > 0
                            && !array_key_exists($entryAccount->accounting_account_id, $accountable)
                        ) {
                            $accountable[$entryAccount->accounting_account_id] = $accountableAccounts;
                        }

                        if (count($accountable) > 0) {
                            foreach ($accountable as $key => $account) {
                                if (count($account) > 0) {
                                    foreach ($account as $keyAcc => $acc) {
                                        if ($acc->accounting_account_id == $entryAccount->accounting_account_id) {
                                            $acc['amount'] += (float)$entryAccount['debit'] > 0 ?
                                                (float)$entryAccount['debit'] / count($account) :
                                                (float)$entryAccount['assets'] / count($account);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $pdf = new ReportRepository();
            $filename = "pay-order-$financePayOrder->code.pdf";
            $file = storage_path() . '/reports/' . $filename;
            list($year, $month, $day) = explode("-", $financePayOrder->ordered_at);
            $pdf->setConfig(
                [
                    'institution' => $financePayOrder->institution,
                    'reportDate' => date("d-m-Y", strtotime(now())),
                    'urlVerify'   => url(''),
                    'orientation' => 'P',
                    'filename'    => $filename
                ]
            );
            $pdf->setHeader(
                "ORDEN DE PAGO Nº $financePayOrder->code",
                "En ejercicio fiscal: $year",
                true,
                false,
                '',
                'C',
                'C'
            );
            $pdf->setFooter();
            $pdf->setBody(
                'finance::pay_orders.report',
                true,
                compact('financePayOrder', 'specificAction', 'accountingEntry', 'accountable', 'deductions')
            );
        }
    }

    /**
     * Obtiene un listado de receptores para las órdenes de pago
     *
     * @author Daniel Contreras <exodiadaniel@gmail.com> | <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPayOrderReceivers()
    {
        $data = [['id' => '', 'text' => 'Seleccione...']];
        $groups = Receiver::query()
            ->select('group')
            ->groupBy('group')
            ->orderBy('group')
            ->get();

        foreach ($groups as $g) {
            $childrenValue = [];

            $childrens = Receiver::query()
                ->select(
                    'id',
                    'description AS text',
                    'receiverable_type',
                    'receiverable_id',
                    'associateable_id',
                    'associateable_type'
                )
                ->where('group', $g->group)
                ->toBase()
                ->get()
                ->toArray();

            foreach ($childrens as $children) {
                $childreInfo = (!empty($children->receiverable_type))
                    ? $children->receiverable_type::find($children->receiverable_id)
                    : null;

                $accounting_account = null;
                $text = $children->text;

                if (
                    isset($childreInfo) &&
                    isset($childreInfo['accounting_account_id']) &&
                    !isset($children->associateable_id)
                ) {
                    $accounting_account = \Modules\Accounting\Models\AccountingAccount::find(
                        $childreInfo['accounting_account_id']
                    );
                } else {
                    $accounting_account = \Modules\Accounting\Models\AccountingAccount::find(
                        $children->associateable_id
                    );
                }

                if ($accounting_account) {
                    $text = $children->text . (' - ' .  $accounting_account->code ?? '');
                }

                $existingIndex = -1;

                foreach ($childrenValue as $index => $child) {
                    if ($child['text'] === $text && $child['accounting_account_id'] === ($accounting_account ? $accounting_account->id : '')) {
                        $existingIndex = $index;
                        break;
                    }
                }

                if ($existingIndex !== -1) {
                    // El elemento ya existe, realiza la edición
                    array_push($childrenValue[$existingIndex]['associateables'], $children->id);
                    // ...actualiza los otros campos necesarios
                } else {
                    // El elemento no existe, realiza la inserción
                    array_push(
                        $childrenValue,
                        [
                            'id' => $children->id,
                            'text' => $text,
                            'accounting_account_id' => $accounting_account ? $accounting_account->id : '',
                            'associateables' => [$children->id]
                        ]
                    );
                }
            }

            if (array_search(['text' => $g->group, 'children' => $childrenValue], $data) === false) {
                array_push($data, ['text' => $g->group, 'children' => $childrenValue]);
            }
        }

        return response()->json(['records' => $data], 200);
    }


    /**
     * Método que permite anular una orden de pago, de manera parcial o total
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelPayOrder(Request $request)
    {
        $validate_rules = [
            'cancel_pay_order_option_id' => ['required'],
            'description' => ['required'],
            'canceled_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de anulación')],
        ];
        $messages = [
            'cancel_pay_order_option_id.required' => 'El campo ¿Anulación? es obligatorio.',
            'description.required' => 'El campo descripción del motivo de la anulación es obligatorio.',
            'canceled_at.required' => 'El campo fecha de anulación es obligatorio.',
        ];

        if ($request->is_payroll_contribution && $request->cancel_pay_order_option_id == 1) {
            $errors[0] = ["Esta opción no puede ser procesada por el sistema."];
            return response()->json(['result' => true, 'errors' => $errors], 422);
        }
        $this->validate($request, $validate_rules, $messages);

        try {
            DB::Transaction(function () use ($request) {
                $financePayOrder = FinancePayOrder::find($request->id);

                if (isset($financePayOrder)) {
                    $documentStatusAN = DocumentStatus::where('action', 'AN')->first(); //Status del documento ANulado
                    $documentStatusPR = DocumentStatus::where('action', 'PR')->first(); //Status del documento En Proceso
                    $financePayOrder->status = 'AN';
                    $financePayOrder->observations = 'ANULADO: ' . $request->description;
                    $financePayOrder->document_status_id = $documentStatusAN->id;
                    $financePayOrder->save();

                    $isBudget = Module::has('Budget') && Module::isEnabled('Budget');
                    $isAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
                    $isPayroll = Module::has('Payroll') && Module::isEnabled('Payroll');

                    if ($isAccounting) {
                        /* Reverso de Asiento contable de la orden de pago */
                        $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePayOrder->code)->first();
                        $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
                            'from_date' => $request->canceled_at,
                            // Código de la orden de pago como referencia
                            'reference' => $financePayOrder->code,
                            'concept' => 'Anulación: ' . $accountEntry->concept ,
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

                        /* Crea la relación entre el asiento contable y el registro de orden de pago */
                        \Modules\Accounting\Models\AccountingEntryable::create([
                            'accounting_entry_id' => $accountEntryNew->id,
                            'accounting_entryable_type' => FinancePayOrder::class,
                            'accounting_entryable_id' => $financePayOrder->id,
                        ]);
                    }
                    /* Anulación sin remisión */
                    if ($request->cancel_pay_order_option_id == 1) {
                        /*
                         | Anulación sin remisión, se anula el compromiso y se cambia su estatus a ANulado
                         | Se liberra el Documento de origen (Nómina u orden de compra)
                         */
                        if ($isBudget && $financePayOrder->document_sourceable_type == \Modules\Budget\Models\BudgetCompromise::class) {
                            //se busca el compromiso asociado a la orden de pago
                            $compromise = $isBudget ? \Modules\Budget\Models\BudgetCompromise::query()
                            ->find($financePayOrder->document_sourceable_id) : null;
                            /*
                             | Se buscan todas las BudgetStage (etapas presupuestarias)
                             | pertenecintes al compromiso relacionado con la orden de pago
                             | para ser eliminadas
                             */
                            if (isset($compromise)) {
                                $compromisedYear = explode("-", $compromise->compromised_at)[0];

                                \Modules\Budget\Models\BudgetStage::query()
                                ->where([
                                    'budget_compromise_id'  => $compromise->id,
                                    'stageable_type'        => FinancePayOrder::class,
                                    'stageable_id'          => $financePayOrder->id
                                    ])
                                ->where('type', 'CAU')->delete();

                                //Se cambia el status del documento del compromiso a En Proceso
                                $compromise->document_status_id = $documentStatusPR->id;
                                $compromise->save();
                                /*
                                 | Se verifica que el compromiso no sea un aporte de nómina
                                 | de lo contrario solo se anularán la etapa presuspuestaria
                                 | CAUsado y se mantiene COMprometido
                                 */
                                $CodePayroll = $isPayroll
                                ? CodeSetting::where(
                                    "model",
                                    \Modules\Payroll\Models\Payroll::class
                                )->first()
                                : null;

                                $regexPattern = '/^AP - \\d+' . $CodePayroll?->format_prefix . '/';

                                if (!preg_match($regexPattern, $compromise->document_number)) {
                                    \Modules\Budget\Models\BudgetStage::query()
                                    ->where(
                                        'budget_compromise_id',
                                        $compromise->id
                                    )->where('type', 'COM')->delete();

                                    /* Se buscan los ítems del compromiso que no estén anulados*/
                                    $budgetCompromiseDetails = \Modules\Budget\Models\BudgetCompromiseDetail::query()
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

                                        $budgetAccountOpen = \Modules\Budget\Models\BudgetAccountOpen::with('budgetAccount')
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

                                //Se Cambia el estatus de la orden de compra sí existe
                                if (Module::has('Purchase') && Module::isEnabled('Purchase')) {
                                    $purchaseOrder = (isset($compromise->sourceable_type)
                                    && $compromise->sourceable_type
                                    == \Modules\Purchase\Models\PurchaseDirectHire::class)
                                    ? \Modules\Purchase\Models\PurchaseDirectHire::query()
                                    ->where([
                                        'id' => $compromise->sourceable_id,
                                        'code' => $compromise->document_number
                                    ])->first() : null;

                                    if ($purchaseOrder) {
                                        $purchaseOrder->status = 'WAIT';
                                        $purchaseOrder->save();
                                    }
                                }

                                //Se Cambia el estatus de la Nómina sí existe
                                if (isset($isPayroll)) {
                                    $payroll = \Modules\Payroll\Models\Payroll::query()
                                    ->where([
                                        'id' => $compromise->sourceable_id,
                                        'code' => $compromise->document_number
                                    ])->first() ?? null;

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
                                            true
                                        );
                                    }
                                }
                            }
                        }
                    } elseif ($request->cancel_pay_order_option_id == 2) {
                        /* Anulación Con remisión, se libera el compromiso y se camnbia su estatus a ELaborado y queda COMprometido */
                        $documentStatusPR = DocumentStatus::where('action', 'PR')->first();
                        //se busca el compromiso asociado a la orden de pago
                        $compromise = $isBudget ? \Modules\Budget\Models\BudgetCompromise::query()
                        ->find($financePayOrder->document_sourceable_id) : null;

                        /* Se busca toda la etapa presupuestaria asociadas a esta orden de compra */
                        if (isset($compromise)) {
                            \Modules\Budget\Models\BudgetStage::query()
                            ->where([
                                'budget_compromise_id'  => $compromise->id,
                                'stageable_type'        => FinancePayOrder::class,
                                'stageable_id'          => $financePayOrder->id
                                ])
                            ->where('type', 'CAU')->delete();

                            if ($isPayroll) {
                                $payroll = \Modules\Payroll\Models\Payroll::query()
                                ->where([
                                    'id' => $compromise->sourceable_id,
                                    'code' => $compromise->document_number
                                ])->first() ?? null;

                                if (isset($payroll)) {
                                    /*
                                     | Se procede a realizar todo el proceso de anulación
                                     | de los aportes de nómina hasta la etapa presupuestaria
                                     | Comprometido.
                                     */
                                    $this->cancelContribution(
                                        $payroll->code,
                                        $request->canceled_at,
                                        $request->description,
                                    );
                                }
                            }
                            $compromise->document_status_id = $documentStatusPR->id;
                            $compromise->save();
                        }
                    }
                }
            });
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
     * Método que permite anular las emisiones, las ordenes
     * de pago y los compromisios de los aportes de nómina
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param  string $code Código o número de documento a búscar
     * @param  string $description Descripción de la anulación
     * @param  boolean $option (true = Anulación de todo el proceso, false = Anula solo el proceso de emision y orden de pago)
     *
     * @return void
     */
    private function cancelContribution($code, $date, $description, $option = false)
    {
        /* Se buscan todas las ordenes de pago asociadas a este compromiso */
        // Patrón de la expresión regular relacionada con el código de nómina
        $documentStatusAN = DocumentStatus::where('action', 'AN')->first(); //Status del documento ANulado
        $regexPattern = "AP - \\d+$code";
        $compromiseContribution = \Modules\Budget\Models\BudgetCompromise::query()
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

        if (isset($compromiseContribution)) {
            foreach ($compromiseContribution as $compContribution) {
                $compromisedYear = explode("-", $compContribution->compromised_at)[0];

                /* Se buscan todas las etapas presupuestarias */

                //Etapa relacionada con la emisión de pago
                $paymentExecuteBugetStages = \Modules\Budget\Models\BudgetStage::query()
                ->where([
                    'budget_compromise_id'  => $compContribution->id,
                    'stageable_type'        => FinancePaymentExecute::class,
                ])
                ->where('type', 'PAG')->get() ?? null;

                //Se realiza todo el proceso de anulación para las emisiones de pago si existen
                if (isset($paymentExecuteBugetStages)) {
                    foreach ($paymentExecuteBugetStages as $paymentExecuteBugetStage) {
                        $financePaymentExecute = FinancePaymentExecute::query()
                        ->find($paymentExecuteBugetStage->stageable_id);

                        if (isset($financePaymentExecute)) {
                            $financePaymentExecute->status = 'AN';
                            $financePaymentExecute->description = $description;
                            $financePaymentExecute->document_status_id = $documentStatusAN->id;

                            /* se eliminan las retenciones asociadas a la emisión de pago */
                            $financePaymentExecute->financePaymentDeductions()->delete();
                            /* Se guadan los cambios en la emisión de pago */
                            $financePaymentExecute->save();

                            if ($isAccounting) {
                                /* Reverso de Asiento contable de la emisión de pago */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where(
                                    'reference',
                                    $financePaymentExecute->code
                                )->first();
                                $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
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

                                /* Crea la relación entre el asiento contable y el registro de emisión de pago */
                                \Modules\Accounting\Models\AccountingEntryable::create([
                                    'accounting_entry_id' => $accountEntryNew->id,
                                    'accounting_entryable_type' => FinancePaymentExecute::class,
                                    'accounting_entryable_id' => $financePaymentExecute->id,
                                ]);
                            }

                            //Se eliminan las estapas presupuestarias
                            \Modules\Budget\Models\BudgetStage::query()
                            ->where([
                                'budget_compromise_id'  => $compContribution->id,
                                'stageable_type'        => FinancePaymentExecute::class,
                                'stageable_id'          => $financePaymentExecute->id
                                ])
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
                            )
                            ->first();

                            if ($bankingMovementPaymentExecute) {
                                $bankingMovementPaymentExecute->concept = 'Anulado: '
                                . $bankingMovementPaymentExecute->concept
                                . '. (' . $financePaymentExecute->description . ')';
                                $bankingMovementPaymentExecute->document_status_id = $documentStatusAN->id;
                                $bankingMovementPaymentExecute->save();
                            }
                        }
                    }
                }

                //Etapa relacionada con la orden de pago
                $payOrderBugetStages = \Modules\Budget\Models\BudgetStage::query()
                ->where([
                    'budget_compromise_id'  => $compContribution->id,
                    'stageable_type'        => FinancePayOrder::class,
                    // 'stageable_id'          => $pay_order->id
                    ])
                ->where('type', 'CAU')->get();

                //Se realiza todo el proceso de anulación para las ordenes de pago si existen
                if (isset($payOrderBugetStages)) {
                    foreach ($payOrderBugetStages as $payOrderBugetStage) {
                        // Se buscan todas las órdenes de pago asociadas a este compromiso
                        $financePayOrder = FinancePayOrder::query()
                        ->find($payOrderBugetStage->stageable_id);

                        if (isset($financePayOrder)) {
                            $financePayOrder->status = 'PE';
                            $financePayOrder->document_status_id = $documentStatusAN->id;
                            $financePayOrder->observations = 'ANULADO: '
                            . $financePayOrder->observations
                            . '. (' . $description . ')';
                            $financePayOrder->save();

                            if ($isAccounting) {
                                /* Reverso de Asiento contable de la orden de pago */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePayOrder->code)->first();
                                $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
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

                                /* Crea la relación entre el asiento contable y el registro de orden de pago */
                                \Modules\Accounting\Models\AccountingEntryable::create([
                                    'accounting_entry_id' => $accountEntryNew->id,
                                    'accounting_entryable_type' => FinancePayOrder::class,
                                    'accounting_entryable_id' => $financePayOrder->id,
                                ]);
                            }

                            //Se eliminan las estapas presupuestarias
                            \Modules\Budget\Models\BudgetStage::query()
                            ->where([
                                'budget_compromise_id'  => $compContribution->id,
                                'stageable_type'        => FinancePayOrder::class,
                                'stageable_id'          => $financePayOrder->id
                                ])
                            ->where('type', 'CAU')->delete();
                        }
                    }
                }

                if ($option) {
                    //Se elimina Etapa presupuestaria COMprometido
                    \Modules\Budget\Models\BudgetStage::query()
                    ->where(
                        'budget_compromise_id',
                        $compContribution->id,
                    )->where('type', 'COM')->delete();

                    /* Se buscan los ítems del compromiso */
                    $budgetCompromiseDetails = \Modules\Budget\Models\BudgetCompromiseDetail::query()
                    ->where([
                        'budget_compromise_id' => $compContribution->id,
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

                        $budgetAccountOpen = \Modules\Budget\Models\BudgetAccountOpen::with('budgetAccount')
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
                    $compContribution['document_status_id'] = $documentStatusAN->id;
                    $compContribution['description'] = "Proceso Anulado: "
                    . $compContribution->description . ". "
                    . "(" . $description . ")";
                    $compContribution->save();
                } else {
                    //Se cambia el status del documento del compromiso a En PRoceso
                    //quedando en la etapa presupuestaria 'COM'prometido
                    $compContribution['document_status_id'] = DocumentStatus::where('action', 'PR')->first()->id;
                    $compContribution->save();
                }
            }
        }
    }

    /**
     * Obtiene las fechas de inicio y fin de un periodo especificado dentro de un mes dado.
     *
     * @param integer $month el mes para el cual se está obteniendo el periodo (por defecto: 1)
     * @param integer $period el periodo a obtener (por defecto: 3)
     *
     * @return array un arreglo que contiene las fechas de inicio y fin del periodo especificado
     */
    private function getPeriod($month = 1, $period = 3)
    {
        // Se establece el perido de busqueda dado por el año fiscal en curso y el mes seleccionado.
        $currentFiscalYear = FiscalYear::select('year')
        ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
        $monthPeriod = $currentFiscalYear->year . '-' . $month;

        // Determinar las fechas de inicio y finalización del rango de fechas según el periodo y el mes proporcionados
        if ($period == 3) {
            // Calcular las fechas de inicio y finalización para el mes completo
            $startDate = date('Y-m-01', strtotime($monthPeriod));
            $endDate = date('Y-m-t', strtotime($monthPeriod));
        } elseif ($period == 1) {
            // Calcular las fechas de registro y finalización para la primera quincena del mes
            $startDate = date('Y-m-01', strtotime($monthPeriod));
            $endDate = date('Y-m-15', strtotime($monthPeriod));
        } elseif ($period == 2) {
            // Calcular las fechas de registro y finalización para la segunda quincena del mes
            $startDate = date('Y-m-16', strtotime($monthPeriod));
            $endDate = date('Y-m-t', strtotime($monthPeriod));
        }
        return [$startDate, $endDate];
    }
}
