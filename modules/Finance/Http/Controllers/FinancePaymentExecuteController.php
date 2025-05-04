<?php

namespace Modules\Finance\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\DocumentStatus;
use App\Models\FiscalYear;
use App\Models\Institution;
use App\Models\Receiver;
use App\Repositories\ReportRepository;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Exports\FinanceIvaExport;
use Modules\Finance\Models\FinanceBankAccount;
use Modules\Finance\Models\FinanceBankingMovement;
use Modules\Finance\Models\FinancePaymentExecute;
use Modules\Finance\Models\FinancePaymentExecuteIva;
use Modules\Finance\Models\FinancePayOrder;
use Modules\Finance\Models\FinancePayOrderFinancePaymentExecute;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class FinancePaymentExecuteController
 * @brief Gestión de información de ejecuciones de pago
 *
 * Contiene los métodos necesarios para la gestión de la ejecución de pagos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentExecuteController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validate_rules
     */
    public $validate_rules;

    /**
     * Mensajes de validación
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
        $this->validate_rules = [
            'paid_at' => ['required', 'date', new DateBeforeFiscalYear('fecha')],
            'receiver_id' => ['required'],
            'currency_id' => ['required'],
            'reference_selected' => ['required'],
            'reference_selected.*.ordered_at' => ['before_or_equal:paid_at'],
            'source_amount' => ['required'],
            'sub_amount' => ['required'],
            'paid_amount' => ['required'],
            'finance_payment_method_id' => ['required'],
            'finance_bank_id' => ['required'],
            'finance_bank_account_id' => ['required'],
            'observations' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'paid_at.required' => 'El campo fecha es obligatorio.',
            'receiver_id.required' => 'El campo proveedor o beneficiario es obligatorio.',
            'currency_id.required' => 'El campo tipo de moneda es obligatorio.',
            'reference_selected.required' => 'El campo Nro. Referencia es obligatorio.',
            'reference_selected.*.ordered_at.before_or_equal' => 'El campo fecha debe ser mayor o igual a la fecha de la orden de pago.',
            'source_amount.required' => 'El campo monto de la orden es obligatorio.',
            'sub_amount.required' => 'El campo monto del pago (subtotal) es obligatorio.',
            'paid_amount.required' => 'El campo total a pagar es obligatorio.',
            'observations.required' => 'El campo observaciones es obligatorio.',
            'finance_payment_method_id.required' => 'El campo Método de pago es obligatorio.',
            'finance_bank_id.required' => 'El campo Banco es obligatorio.',
            'finance_bank_account_id.required' => 'El campo Nro. Cuenta es obligatorio.',
            'general_bank_reference.required' => 'El campo Nro. de Referencia bancaria es obligatorio.',
            'general_bank_reference.integer' => 'El campo Nro. de Referencia bancaria debe ser un número entero.',
            'general_bank_reference.min' => 'El campo Nro. de Referencia bancaria debe ser mayor o igual a 1.',
        ];
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:finance.paymentexecute.index', ['only' => 'index']);
        $this->middleware('permission:finance.paymentexecute.store', ['only' => 'store']);
        $this->middleware('permission:finance.paymentexecute.update', ['only' => 'update']);
        $this->middleware('permission:finance.paymentexecute.destroy', ['only' => 'destroy']);
        $this->middleware('permission:finance.paymentexecute.cancel', ['only' => 'cancelPaymentExecute']);
        $this->middleware('permission:finance.paymentexecute.approve', ['only' => 'changeDocumentStatus']);
    }

    /**
     * Muestra la plantilla con los registros de ejecuciones de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('finance::payments_execute.list');
    }

    /**
     * Muestra la plantilla con el formulario para el registro de ejecuciones de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('finance::payments_execute.create-edit-form');
    }

    /**
     * Realiza las acciones para almacenar una órden de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if (count($request->accountingItems) > 0) {
            $this->validate_rules['accounting.totDebit'] = ['same:accounting.totAssets'];
            $this->messages['accounting.totDebit.same'] = 'El asiento no esta balanceado, por favor verifique.';
        }

        $this->validate_rules['general_bank_reference'] = ['required', 'integer', 'min:1'];

        $this->validate($request, $this->validate_rules, $this->messages);

        $codeSetting = CodeSetting::where("model", FinancePaymentExecute::class)->first();

        if (!$codeSetting) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]], 200);
        }

        list($year, $month, $day) = explode("-", $request->paid_at);

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            FinancePaymentExecute::class,
            $codeSetting->field
        );

        $documentStatus = default_document_status(); // Estatus PR, Por revisar = Por aprobar
        $documentStatusAN = DocumentStatus::where('action', 'AN')->first(); //Status del documento ANulado
        $documentStatusEL = default_document_status_el(); // Estatus EL = Elaborado

        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $financePaymentExecute = DB::transaction(function () use ($request, $code, $documentStatus, $documentStatusAN, $documentStatusEL, $institution) {
            $amount = (float) $request->paid_amount + (float) $request->deduction_amount;
            $sAmount = (float) $request->source_amount;
            $tolerance = 0.000001; // Margen de tolerancia
            $result = $sAmount - $amount;
            if (abs($result) < $tolerance) {
                $pendingAmount = 0;
            } else {
                $pendingAmount = $sAmount - $amount;
            }

            $amount = (float) $request->paid_amount + (float) $request->deduction_amount;
            $sAmount = (float) $request->source_amount;
            $tolerance = 0.000001; // Margen de tolerancia
            $result = $sAmount - $amount;
            if (abs($result) < $tolerance) {
                $pendingAmount = 0;
            } else {
                $pendingAmount = $sAmount - $amount;
            }

            $is_partial = false;
            if (count($request->reference_selected) == 1) {
                $pay_order = FinancePayOrder::findOrFail($request->reference_selected[0]['id']);
                //Se verifica sí orden de págo pertenece a varias emisones de pago
                $finance_count = $pay_order->financePaymentExecute()
                    ->where('document_status_id', '!=', $documentStatusAN->id)->count();

                if ($finance_count > 0 || $request->is_partial == 'true') {
                    $is_partial = true;
                }
            }

            $financePaymentExecute = FinancePaymentExecute::create([
                'code' => $code,
                'paid_at' => $request->paid_at,
                'has_budget' => true,
                'is_partial' => $is_partial,
                'source_amount' => $request->source_amount,
                'deduction_amount' => $request->deduction_amount,
                'payment_number' => $request->payment_number,
                'paid_amount' => $request->paid_amount,
                'pending_amount' => $pendingAmount,
                'completed' => ($pendingAmount > 0) ? false : true,
                'observations' => $request->observations,
                //El stado de la emisión de pago se cambiará al momento de aprobar dicha emisión
                // 'status' => ($pendingAmount > 0) ? 'PP' : 'PA',
                'document_status_id' => $documentStatus->id,
                'currency_id' => $request->currency_id,
                'finance_payment_method_id' => $request->finance_payment_method_id,
                'finance_bank_account_id' => $request->finance_bank_account_id,
                'general_bank_reference' => $request->general_bank_reference,
            ]);
            foreach ($request->deductions as $deduction) {
                $financePaymentExecute->financePaymentDeductions()->create([
                    'amount' => $deduction['amount'] ?? 0,
                    'mor' => $deduction['mor'] ?? 0,
                    'deduction_id' => $deduction['id'],
                    'finance_payment_execute_id' => $financePaymentExecute->id,
                    'deducted_at' => $financePaymentExecute->paid_at,
                ]);
            }

            foreach ($request->reference_selected as $reference) {
                $payOrder = FinancePayOrder::find($reference['id']);

                FinancePayOrderFinancePaymentExecute::create([
                    'finance_pay_order_id' => $payOrder->id,
                    'finance_payment_execute_id' => $financePaymentExecute->id,
                ]);

                if ($payOrder->document_sourceable_type == \Modules\Budget\Models\BudgetCompromise::class) {
                    $compromise = \Modules\Budget\Models\BudgetCompromise::find($payOrder->document_sourceable_id);
                }
                if (isset($compromise)) {
                    foreach ($compromise->budgetCompromiseDetails as $detail) {
                        $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');
                        $compromise->budgetStages()->create([
                            'code' => $codeStage,
                            'registered_at' => $request->paid_at,
                            'type' => 'PAG',
                            'amount' => $detail->amount,
                            'stageable_type' => FinancePaymentExecute::class,
                            'stageable_id' => $financePaymentExecute->id,
                        ]);
                    }
                }
            }

            /* Asiento contable */
            $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'PAG')->first();
            $accountEntry = \Modules\Accounting\Models\AccountingEntry::create([
                'from_date' => $request->paid_at,
                'reference' => $code, //Código de la ejecución de pago como referencia
                'concept' => $request->observations,
                'observations' => $request->observations,
                'accounting_entry_category_id' => $accountingCategory->id,
                'institution_id' => $institution->id,
                'currency_id' => $request->currency_id,
                'tot_debit' => $request->accounting['totDebit'],
                'tot_assets' => $request->accounting['totAssets'],
                'approved' => false,
                'document_status_id' => $documentStatusEL->id,
            ]);

            foreach ($request->accountingItems as $account) {
                /* Se crea la relación de cuenta a ese asiento si ya existe existe lo actualiza,
                de lo contrario crea el nuevo registro de cuenta */
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
                'accounting_entryable_type' => FinancePaymentExecute::class,
                'accounting_entryable_id' => $financePaymentExecute->id,
            ]);
            return $financePaymentExecute;
        });

        $request->session()->flash('message', ['type' => 'store']);

        return response()->json(['record' => $financePaymentExecute, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los detalles de una ejecución de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('finance::show');
    }

    /**
     * Muestra el formulario para la actualización de datos de la ejecución de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $paymentExecute = FinancePaymentExecute::with(['financePayOrders' => function ($query) {
            $query->with([
                'budgetSpecificAction',
                'institution',
                'documentStatus',
                'nameSourceable',
                'documentSourceable',
                'currency',
            ]);
        },
            'currency',
            'financePaymentDeductions.deduction',
            'financePaymentDeductions.deductionable',
            'financePaymentMethod',
            'financeBankAccount' => function ($q) {
                $q->with(['accountingAccount']);
            }])
            ->find($id);

        if (count($paymentExecute['financePayOrders']) > 0) {
            foreach ($paymentExecute['financePayOrders'] as $order) {
                $order['text'] = $order['code'];
            }
        }

        if (count($paymentExecute['financePaymentDeductions']) > 0) {
            foreach ($paymentExecute['financePaymentDeductions'] as $deduction) {
                $deduction['name'] = $deduction['deduction']
                ? $deduction['deduction']['name'] : $deduction['deductionable']['name'];
            }
        }

        $registeredAccounts = \Modules\Accounting\Models\AccountingEntryable::with('accountingEntry.accountingAccounts')
            ->where('accounting_entryable_type', FinancePaymentExecute::class)
            ->where('accounting_entryable_id', $id)
            ->first();
        return view('finance::payments_execute.create-edit-form', compact('paymentExecute', 'registeredAccounts'));
    }

    /**
     * Actualiza información de una ejecución de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $financePaymentExecute = FinancePaymentExecute::find($id);

        if (count($request->accountingItems) > 0) {
            $this->validate_rules['accounting.totDebit'] = ['same:accounting.totAssets'];
            $this->messages['accounting.totDebit.same'] = 'El asiento no esta balanceado, por favor verifique.';
        }

        $this->validate_rules['general_bank_reference'] = ['required', 'integer', 'min:1'];

        $this->validate($request, $this->validate_rules, $this->messages);

        $documentStatus = default_document_status(); // Estatus PR, Por revisar = Por aprobar
        $documentStatusAN = DocumentStatus::where('action', 'AN')->first(); //Status del documento ANulado
        $documentStatusEL = default_document_status_el(); // Estatus EL = Elaborado

        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        DB::transaction(function () use ($request, $financePaymentExecute, $documentStatus, $documentStatusAN, $documentStatusEL, $institution) {
            $amount = (float) $request->paid_amount + (float) $request->deduction_amount;
            $sAmount = (float) $request->source_amount;
            $tolerance = 0.000001; // Margen de tolerancia
            $result = $sAmount - $amount;
            if (abs($result) < $tolerance) {
                $pendingAmount = 0;
            } else {
                $pendingAmount = $sAmount - $amount;
            }

            $is_partial = false;
            if (count($request->reference_selected) == 1) {
                $pay_order = FinancePayOrder::findOrFail($request->reference_selected[0]['id']);
                //Se verifica sí orden de págo pertenece a varias emisones de pago
                $finance_count = $pay_order->financePaymentExecute()
                    ->where('document_status_id', '!=', $documentStatusAN->id)->count();

                if ($finance_count > 0 || $request->is_partial == 'true') {
                    $is_partial = true;
                }
            }

            $financePaymentExecute->paid_at = $request->paid_at;
            $financePaymentExecute->is_partial = $is_partial;
            $financePaymentExecute->source_amount = $request->source_amount;
            $financePaymentExecute->deduction_amount = $request->deduction_amount;
            $financePaymentExecute->paid_amount = $request->paid_amount;
            $financePaymentExecute->payment_number = $request->payment_number;
            $financePaymentExecute->pending_amount = $pendingAmount;
            $financePaymentExecute->completed = ($pendingAmount > 0) ? false : true;
            $financePaymentExecute->observations = $request->observations;
            //El stado de la emisión de pago se cambiará al momento de aprobar dicha emisión
            // $financePaymentExecute->status = ($pendingAmount > 0) ? 'PP' : 'PA';
            $financePaymentExecute->document_status_id = $documentStatus->id;
            $financePaymentExecute->currency_id = $request->currency_id;
            $financePaymentExecute->finance_payment_method_id = $request->finance_payment_method_id;
            $financePaymentExecute->finance_bank_account_id = $request->finance_bank_account_id;
            $financePaymentExecute->general_bank_reference = $request->general_bank_reference;
            $financePaymentExecute->save();

            /* se eliminan las retenciones asociadas a la emisión de pago */
            $financePaymentExecute->financePaymentDeductions()->delete();

            foreach ($request->deductions as $deduction) {
                /* se registran las nuevas retenciones asociadas a la emisión de pago */
                $financePaymentExecute->financePaymentDeductions()->create([
                    'amount' => $deduction['deduction']['amount'] ?? $deduction['amount'],
                    'mor' => $deduction['deduction']['mor'] ?? $deduction['mor'],
                    'deduction_id' => $deduction['deduction']['id'] ?? $deduction['id'],
                    'finance_payment_execute_id' => $financePaymentExecute->id,
                    'deducted_at' => $financePaymentExecute->paid_at,
                ]);
            }

            $financePayOrderFinancePaymentExecute = FinancePayOrderFinancePaymentExecute::where(
                'finance_payment_execute_id',
                $financePaymentExecute->id
            )->forceDelete();

            foreach ($request->reference_selected as $reference) {
                $payOrder = FinancePayOrder::find($reference['id']);

                FinancePayOrderFinancePaymentExecute::create([
                    'finance_pay_order_id' => $payOrder->id,
                    'finance_payment_execute_id' => $financePaymentExecute->id,
                ]);

                if (Module::has('Budget') && Module::isEnabled('Budget')) {
                    $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');
                    /* se eliminan las etapas anteriores pagagas, de los compromisos*/
                    \Modules\Budget\Models\BudgetStage::where('stageable_type', FinancePaymentExecute::class)
                        ->where('stageable_id', $financePaymentExecute->id)
                        ->where('type', 'PAG')->delete();

                    if ($payOrder->document_sourceable_type == \Modules\Budget\Models\BudgetCompromise::class) {
                        $compromise = \Modules\Budget\Models\BudgetCompromise::find($payOrder->document_sourceable_id);
                    }

                    if (isset($compromise)) {
                        foreach ($compromise->budgetCompromiseDetails as $detail) {
                            $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');

                            $compromise->budgetStages()->create([
                                'code' => $codeStage,
                                'registered_at' => $request->paid_at,
                                'type' => 'PAG',
                                'amount' => $detail->amount,
                                'stageable_type' => FinancePaymentExecute::class,
                                'stageable_id' => $financePaymentExecute->id,
                            ]);
                        }
                    }
                }
            }

            if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                /* Asiento contable */
                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePaymentExecute->code)->first();
                $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'PAG')->first();

                $accountEntry->from_date = $request->paid_at;
                $accountEntry->concept = $request->observations;
                $accountEntry->observations = $request->observations;
                $accountEntry->accounting_entry_category_id = $accountingCategory->id;
                $accountEntry->institution_id = $institution->id;
                $accountEntry->currency_id = $request->currency_id;
                $accountEntry->tot_debit = $request->accounting['totDebit'];
                $accountEntry->tot_assets = $request->accounting['totAssets'];
                $accountEntry->document_status_id = $documentStatusEL->id;
                $accountEntry->save();

                /* Se eliminan las cuentas anteriores del asiento contable */
                \Modules\Accounting\Models\AccountingEntryAccount::where('accounting_entry_id', $accountEntry->id)->delete();

                foreach ($request->accountingItems as $account) {
                    /* Se crea la relación con las nuevas cuentas para ese asiento contable */
                    \Modules\Accounting\Models\AccountingEntryAccount::create([
                        'accounting_entry_id' => $accountEntry->id,
                        'accounting_account_id' => $account['id'],
                        'debit' => $account['debit'],
                        'assets' => $account['assets'],
                    ]);
                }

                /* Crea la relación entre el asiento contable y el registro de orden de pago */
                \Modules\Accounting\Models\AccountingEntryable::create([
                    'accounting_entry_id' => $accountEntry->id,
                    'accounting_entryable_type' => FinancePaymentExecute::class,
                    'accounting_entryable_id' => $financePaymentExecute->id,
                ]);
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['record' => $financePaymentExecute, 'message' => 'Success'], 200);
    }

    /**
     * Elimina una ejecución de pago
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $financePaymentExecute = FinancePaymentExecute::find($id);

        if ($financePaymentExecute) {
            $financePaymentExecute->delete();
        }

        return response()->json(['record' => $financePaymentExecute, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene un listado de receptores de las órdenes de pago que aún están pendientes por cancelar
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */

    public function getPayOrderReceivers()
    {
        // Intentamos obtener los datos de la caché
        $cachedData = Cache::get('pay_order_receivers');
        if ($cachedData) {
            return response()->json(['records' => $cachedData], 200);
        }

        // Consulta para obtener los nombres de las fuentes
        $nameSources = FinancePayOrder::whereIn('status', ['PE', 'PP'])
            ->groupBy('name_sourceable_type', 'name_sourceable_id')
            ->pluck('name_sourceable_type', 'name_sourceable_id');

        $data = [['id' => '', 'text' => 'Seleccione...']];
        $groups = [];

        // Iteramos sobre los nombres de las fuentes para obtener los grupos de receptores
        foreach ($nameSources as $sourceableId => $sourceableType) {
            $groupId = $sourceableType === Receiver::class
            ? Receiver::find($sourceableId)->group
            : Receiver::where('receiverable_type', $sourceableType)
                ->where('receiverable_id', $sourceableId)
                ->value('group');

            if ($groupId) {
                $groups[] = $groupId;
            }
        }

        // Obtenemos los valores únicos de los grupos
        $groups = array_unique($groups);

        // Obtenemos los datos de los receptores para cada grupo
        foreach ($groups as $group) {
            $childrens = Receiver::select(
                'id',
                'description AS text',
                'receiverable_type',
                'receiverable_id',
                'associateable_id',
                'associateable_type'
            )->where('group', $group)->get();

            $childrenValue = [];

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
                    $text = $children->text . (' - ' . $accounting_account->code ?? '');
                }

                $existingIndex = collect($childrenValue)->search(function ($child) use ($text, $accounting_account) {
                    return $child['text'] === $text && $child['accounting_account_id'] === $accounting_account?->id;
                });

                if ($existingIndex !== false) {
                    $childrenValue[$existingIndex]['associateables'][] = $children->id;
                } elseif ($accounting_account) {
                    $childrenValue[] = [
                        'id' => $children->id,
                        'text' => $text,
                        'accounting_account_id' => $accounting_account->id,
                        'associateables' => [$children->id],
                    ];
                }
            }

            if (!collect($data)->contains('text', $group)) {
                $data[] = ['text' => $group, 'children' => $childrenValue];
            }
        }

        // Almacenamos los datos en caché por 5 minutos (300 segundos)
        Cache::put('pay_order_receivers', $data, 300);

        return response()->json(['records' => $data], 200);
    }

    /**
     * Obtiene los registros a mostrar en listados de componente Vue
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse Devuelve un JSON con la información de las formulaciones
     */
    public function vueList(Request $request)
    {
        $records = FinancePaymentExecute::query()->with([
            'financePayOrders' => function ($q) {
                $q->with(['institution', 'documentSourceable']);
            },
            'financePaymentDeductions' => function ($q) {
                $q->with(['deduction', 'deductionable']);
            },
            'documentStatus',
            'financePaymentExecuteIva',
            'currency',
            'financeBankAccount' => function ($q) {
                $q->with(['financeBankingAgency' => function ($qq) {
                    $qq->with('financeBank');
                }]);
            },
            'financePaymentMethod',
        ])
            ->orderBy('id')
            ->search($request->query('query'))
            ->paginate($request->limit ?? 10);

        foreach ($records->items() as $record) {
            $record->financePayOrders->each(function ($order) {
                if ($order->documentSourceable && method_exists($order->documentSourceable, 'budgetCompromiseDetails')) {
                    $order->documentSourceable->load('budgetCompromiseDetails');
                }
            });
        }

        return response()->json([
            'data' => $records->items(),
            'count' => $records->total(),
            'cancelPaymentExecutedPermission' => auth()->user()->hasPermission('finance.paymentexecute.cancel'),
            'approvePaymentExecutedPermission' => auth()->user()->hasPermission('finance.paymentexecute.approve'),
        ], 200);
    }

    /**
     * Genera el reporte de la ejecución de pago
     *
     * @param integer $id ID de la ejecución de pago
     *
     * @return void
     */
    public function pdf($id)
    {
        $financePaymentExecute = FinancePaymentExecute::with([
            'financePaymentDeductions' => function ($q) {
                $q->with(['deduction', 'deductionable']);
            },
        ])->find($id);
        if ($financePaymentExecute) {
            $accountingEntry = \Modules\Accounting\Models\AccountingEntry::with(['accountingAccounts' => function ($q) {
                $q->with('account');
            }])->where('reference', $financePaymentExecute->code)->first();

            $payOrder = [];
            foreach (
                FinancePayOrderFinancePaymentExecute::query()
                ->where([
                    'finance_payment_execute_id' => $financePaymentExecute->id,
                ])->get() as $payOrderPaymentExecute
            ) {
                array_push($payOrder, $payOrderPaymentExecute->financePayOrder()
                        ->with([
                            'currency',
                            'institution',
                            'budgetSpecificAction.subSpecificFormulations.accountOpens',
                        ])->first());
            }
            $pdf = new ReportRepository();
            $filename = "payment-execute-$financePaymentExecute->code.pdf";
            $file = storage_path() . '/reports/' . $filename;
            list($year, $month, $day) = explode("-", $financePaymentExecute->paid_at);
            $pdf->setConfig(
                [
                    'institution' => $payOrder[0]->institution,
                    'reportDate' => date("d-m-Y", strtotime(now())),
                    'urlVerify' => url(''),
                    'orientation' => 'P',
                    'filename' => $filename,
                ]
            );
            $pdf->setHeader(
                "COMPROBANTE DE EMISIÓN Nº $financePaymentExecute->code",
                "ACUSE DE PAGO RECIBIDO",
                true,
                false,
                '',
                'C',
                'C'
            );
            $pdf->setFooter();
            $pdf->setBody(
                'finance::payments_execute.report',
                true,
                compact('payOrder', 'financePaymentExecute', 'accountingEntry')
            );
        }
    }
    /**
     * Genera el reporte del IVA
     *
     * @param Request $request Datos de la petición
     * @param integer $id ID de la ejecución de pago
     *
     * @return BinaryFileResponse
     */
    public function pdfIva(Request $request, $id)
    {

        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $financePaymentExecute = FinancePaymentExecute::with([
            'financePaymentDeductions' => function ($q) {
                $q->with(['deduction' => function ($qq) {
                    return $qq->where('name', 'like', "IVA");
                }, 'deductionable']);
            },
        ])->find($id);
        $deduction = FinancePaymentExecuteIva::with([
            'financePaymentDeductions', 'FinancePaymentExecute',
        ])->where('finance_payment_execute_id', $id)->get();

        list($rifProvider, $nameProvider) = explode("-", $financePaymentExecute->getReceiverNameAttribute());
        $financePaymentExecute->name_provider = $nameProvider;
        $financePaymentExecute->rif_provider = $rifProvider;
        return Excel::download(new FinanceIvaExport($financePaymentExecute, $deduction), 'comprobante_retencion.xlsx');
    }

    /**
     * Genera el reporte del IVA registrado
     *
     * @param Request $request Datos de la petición
     * @param integer $id ID de la ejecución de pago
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pdfIvaRegister(Request $request, $id)
    {

        $financePaymentExecute = FinancePaymentExecute::with([
            'financePaymentDeductions' => function ($q) {
                $q->with(['deduction', 'deductionable']);
            },
        ])->find($id);
        list($year, $month, $day) = explode("-", $financePaymentExecute->paid_at);
        $newCode = 1;
        $targetModel = FinancePaymentExecuteIva::class;
        $targetModel = FinancePaymentExecuteIva::withTrashed()->orderBy('id', 'desc')->first();
        if ($targetModel) {
            $newCode += (int) explode('-', $targetModel->code)[2];
            $newCode = Str::padLeft($newCode, 8, '0');
        } else {
            $newCode = "00000001";
        }
        // Verificar si ya existe un registro con el mismo finance_payment_execute_id
        $existingRecord = FinancePaymentExecuteIva::where('finance_payment_execute_id', $id)->first();

        if ($existingRecord) {
            // Si existe, eliminar el registro existente
            $existingRecord->delete();
        }
        $code = $existingRecord ? $existingRecord->code : "{$year}-{$month}-{$newCode}";
        foreach ($request->request as $key => $value) {
            FinancePaymentExecuteIva::create([
                'code' => $code,
                'percentage' => $value["percentage"],
                'finance_payment_execute_id' => $id,
                'finance_payment_deductions_id' => $value["id"],
                'total_purchases_iva' => $value["total_purchases_iva"],
                'total_purchases_without_iva' => $value["total_purchases_without_iva"],
                'percentage_retained' => $value["iva"],
            ]);
        }
        return response()->json(['records' => $financePaymentExecute], 200);
    }

    /**
     * Método para cambiar el estado de una emisión de pago a Pagado 'PA'
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeDocumentStatus(Request $request)
    {
        $validate_rules = [
            'approved_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de aprobación')],
        ];
        $messages = [
            'approved_at.required' => 'El campo fecha de aprobación es obligatorio.',
        ];
        $this->validate($request, $validate_rules, $messages);

        try {
            $financePaymentExecute = FinancePaymentExecute::findOrFail($request->id);
            // Se verifica que la fecha de aporbación no sea menor a las fecha del resgistro
            if ($request->approved_at < date_format($financePaymentExecute->paid_at, 'Y-m-d')) {
                $errors[0] = ["La fecha de aprobación no puede ser menor a la fecha de creación ("
                    . date_format($financePaymentExecute->paid_at, 'd/m/Y') . ")"];
                return response()->json(['result' => true, 'errors' => $errors], 422);
            }

            $accountingEntryable = \Modules\Accounting\Models\AccountingEntryable::query()
            ->where([
                'accounting_entryable_type' => FinancePaymentExecute::class,
                'accounting_entryable_id'   => $financePaymentExecute->id
            ])->firstOrFail();

            DB::transaction(function () use ($financePaymentExecute, $request, $accountingEntryable) {
                $payOrdersPaymentExecute = FinancePayOrderFinancePaymentExecute::where(
                    'finance_payment_execute_id',
                    $financePaymentExecute->id
                )->get();

                //Estatus del documento Aprobado
                $documentStatus = DocumentStatus::query()->where('action', 'AP')->firstOrFail();
                //Estatus del documento PR, Por revisar = Por aprobar
                $documentStatusPR = default_document_status();

                $financePaymentExecute->status = ($financePaymentExecute->is_partial) ? 'PP' : 'PA';
                $financePaymentExecute->paid_at = $request->approved_at;
                $financePaymentExecute->document_status_id = $documentStatus->id;
                $financePaymentExecute->save();

                if ($payOrdersPaymentExecute) {
                    foreach ($payOrdersPaymentExecute as $payOrder) {
                        $pay_order = FinancePayOrder::find($payOrder->finance_pay_order_id);
                        $pay_order->status = (($financePaymentExecute->pending_amount > 0)) ? 'PP' : 'PA';
                        $pay_order->save();
                    }
                    /* Se crea el movimiento bancario */
                    $this->createBankingMovement(
                        $financePaymentExecute->toArray(),
                        $payOrdersPaymentExecute[0]->finance_pay_order_id,
                        $request->approved_at
                    );

                    \Modules\Accounting\Models\AccountingEntry::query()
                    ->orWhere([
                        'id'                 => $accountingEntryable->accounting_entry_id,
                        'reference'          => $financePaymentExecute->code,
                    ])->firstOrFail()
                    ->update(['document_status_id' => $documentStatusPR->id]);
                }
            });
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
            Log::error($e->getMessage());
            return response()->json(
                ['message' =>
                    [
                        'type' => 'custom',
                        'title' => 'Alerta',
                        'icon' => 'screen-error',
                        'class' => 'growl-danger',
                        'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage),
                    ]],
                500
            );
        }
    }

    /**
     * Método que permite anular una emisión de pago, de manera parcial o total
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancelPaymentExecute(Request $request)
    {
        $validate_rules = [
            'cancel_payment_execute_option_id' => ['required'],
            'description' => ['required'],
            'canceled_at' => ['required', 'date', new DateBeforeFiscalYear('fecha de anulación')],
        ];
        $messages = [
            'cancel_payment_execute_option_id.required' => 'El campo ¿Anulación? es obligatorio.',
            'description.required' => 'El campo descripción del motivo de la anulación es obligatorio.',
            'canceled_at.required' => 'El campo fecha de anulación es obligatorio.',
        ];
        if ($request->is_payroll && $request->cancel_payment_execute_option_id == 2) {
            $errors[0] = ["Esta opción no puede ser procesada por el sistema."];
            return response()->json(['result' => true, 'errors' => $errors], 422);
        }
        $this->validate($request, $validate_rules, $messages);

        try {
            $financePaymentExecute = FinancePaymentExecute::findOrFail($request->id);
            // Se verifica que la fecha de anulación no sea menor a las fecha del aprobación
            if ($request->canceled_at < date_format($financePaymentExecute->paid_at, 'Y-m-d')) {
                $errors[0] = ["La fecha de anulación no puede ser menor a la fecha de aprobación ("
                    . date_format($financePaymentExecute->paid_at, 'd/m/Y') . ")"];
                return response()->json(['result' => true, 'errors' => $errors], 422);
            }
            /* La opción sin remisión no puede ser porcesada por el sistema */
            if (
                ($financePaymentExecute->is_partial)
                && $request->cancel_payment_execute_option_id == 1
            ) {
                $errors[0] = ["Esta opción no puede ser procesada por el sistema."];
                return response()->json(['result' => true, 'errors' => $errors], 422);
            }

            DB::transaction(function () use ($financePaymentExecute, $request) {

                if ($financePaymentExecute) {
                    $documentStatus = DocumentStatus::where('action', 'AN')->first(); //Status del documento ANulado
                    /* Estado Comprometido del compromiso establecido a PROCESADO */
                    $documentStatusPR = DocumentStatus::where('action', 'PR')->first();

                    $financePaymentExecute->status = 'AN';
                    $financePaymentExecute->description = $request->description;
                    $financePaymentExecute->document_status_id = $documentStatus->id;

                    /* se eliminan las retenciones asociadas a la emisión de pago */
                    $financePaymentExecute->financePaymentDeductions()->delete();
                    /*Se guadan los cambios en la emisión de pago */
                    $financePaymentExecute->save();

                    $isBudget = Module::has('Budget') && Module::isEnabled('Budget');
                    $isAccounting = Module::has('Accounting') && Module::isEnabled('Accounting');
                    $isPayroll = Module::has('Payroll') && Module::isEnabled('Payroll');
                    if ($isAccounting) {
                        /* Reverso de Asiento contable de la emisión de pago */
                        $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePaymentExecute->code)->first();
                        $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
                            'from_date' => $request->canceled_at,
                            // Código de la ejecución de pago como referencia
                            'reference' => $financePaymentExecute->code,
                            'concept' => 'Anulación: ' . $accountEntry->concept,
                            'observations' => $financePaymentExecute->description,
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

                    /* Buscar los movimientos bancarios, actualizar el concepto del movimiento,
                    y cambiar el estatus a anulado */
                    $bankingMovementPaymentExecute = FinanceBankingMovement::query()
                        ->where(
                            'reference',
                            $financePaymentExecute->code
                        )->where(
                            'document_status_id',
                            '!=',
                            $documentStatus->id
                        )
                        ->first();

                    if ($bankingMovementPaymentExecute) {
                        $bankingMovementPaymentExecute->concept = 'Anulado: '
                        . $bankingMovementPaymentExecute->concept
                        . '. (' . $financePaymentExecute->description . ')';
                        $bankingMovementPaymentExecute->document_status_id = $documentStatus->id;
                        $bankingMovementPaymentExecute->save();
                    }

                    /* Anulación sin remisión */
                    if ($request->cancel_payment_execute_option_id == 1) {
                        // Anulación Sin Remisión, Se anulan las ordenes de pago
                        // y los compromisos

                        $payOrdersPaymentExecute = FinancePayOrderFinancePaymentExecute::query()
                            ->where(
                                'finance_payment_execute_id',
                                $financePaymentExecute->id
                            )->get();

                        if (isset($payOrdersPaymentExecute)) {
                            foreach ($payOrdersPaymentExecute as $payOrderPayExecute) {
                                // Se buscan todas las órdenes de pago asociadas a esta emisión y se cambia su estatus
                                $pay_order = FinancePayOrder::query()->find($payOrderPayExecute->finance_pay_order_id);

                                if (isset($pay_order)) {
                                    $pay_order->status = 'PE';
                                    $pay_order->document_status_id = $documentStatus->id;
                                    $pay_order->observations = 'ANULADO: ' . $pay_order->observations
                                    . '. (' . $financePaymentExecute->description . ')';
                                    $pay_order->save();

                                    if ($pay_order->document_sourceable_type == \Modules\Budget\Models\BudgetCompromise::class) {
                                        //se busca el compromiso asociado a la orden de pago
                                        $compromise = $isBudget ? \Modules\Budget\Models\BudgetCompromise::query()
                                            ->find($pay_order->document_sourceable_id) : null;
                                        /*
                                         | Se buscan todas las BudgetStage (etapas presupuestarias)
                                         | pertenecintes al compromiso relacionado con las ordenes de pago de esta
                                         | emisión para ser eliminadas
                                         */
                                        if (isset($compromise)) {
                                            $compromisedYear = explode("-", $compromise->compromised_at)[0];

                                            \Modules\Budget\Models\BudgetStage::query()
                                                ->where([
                                                    'budget_compromise_id' => $compromise->id,
                                                    'stageable_type' => FinancePaymentExecute::class,
                                                    'stageable_id' => $financePaymentExecute->id,
                                                ])
                                                ->where('type', 'PAG')->delete();

                                            \Modules\Budget\Models\BudgetStage::query()
                                                ->where([
                                                    'budget_compromise_id' => $compromise->id,
                                                    'stageable_type' => FinancePayOrder::class,
                                                    'stageable_id' => $pay_order->id,
                                                ])
                                                ->where('type', 'CAU')->delete();

                                            //Se cambia el status del documento del compromiso a PRocesado//por Aprobar
                                            $compromise->document_status_id = $documentStatusPR->id;
                                            $compromise->save();
                                            /*
                                             | Se verifica que el compromiso no sea un aporte de nómina
                                             | de lo contrario solo se anularán las etapa presuspuestarias
                                             | PAGado y CAUsado y se mantiene COMprometido
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

                                                /* Se buscan los ítems del compromiso */
                                                $budgetCompromiseDetails = \Modules\Budget\Models\BudgetCompromiseDetail::query()
                                                    ->where([
                                                        'budget_compromise_id' => $compromise->id,
                                                        'document_status_id' => null,
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
                                                            'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + $total,
                                                        ]);
                                                    }

                                                    //Se anulan los items del compromiso
                                                    $budgetCompromiseDetail['document_status_id'] = $documentStatus->id;
                                                    $budgetCompromiseDetail->save();
                                                }

                                                //Se cambia el status del documento del compromiso a 'AN'ulado
                                                $compromise->document_status_id = $documentStatus->id;
                                                $compromise->description = "Proceso Anulado: "
                                                . $compromise->description . ". "
                                                . "(" . $financePaymentExecute->description . ")";
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
                                                        'code' => $compromise->document_number,
                                                    ])->first() : null;

                                                if ($purchaseOrder) {
                                                    $purchaseOrder->status = 'WAIT';
                                                    $purchaseOrder->save();
                                                }
                                            }

                                            //Se Cambia el estatus de la Nómina sí existe
                                            if ($isPayroll) {
                                                $payroll = \Modules\Payroll\Models\Payroll::query()
                                                    ->where([
                                                        'id' => $compromise->sourceable_id,
                                                        'code' => $compromise->document_number,
                                                    ])->first() ?? null;

                                                if (isset($payroll)) {
                                                    $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                                                    $payrollPaymentPeriod->payment_status = 'pending';
                                                    $payrollPaymentPeriod->availability_status = 'AN';
                                                    $payrollPaymentPeriod->save();

                                                    /* Buscar los movimientos bancarios, actualizar el concepto del
                                                    movimiento, y eliminar el registro */
                                                    $bankingMovement =
                                                    FinanceBankingMovement::query()
                                                        ->where(
                                                            'reference',
                                                            $payroll->code
                                                        )->where(
                                                            'document_status_id',
                                                            '!=',
                                                            $documentStatus->id
                                                        )->first();

                                                    if (isset($bankingMovement)) {
                                                        $bankingMovement->concept = 'Anulado: '
                                                        . $bankingMovement->concept
                                                        . '. (' . $financePaymentExecute->description . ')';
                                                        $bankingMovement->document_status_id = $documentStatus->id;
                                                        $bankingMovement->save();
                                                    }

                                                    // Se procede a realizar todo el proceso de anulación
                                                    // de los aportes de nómina
                                                    $this->cancelContribution(
                                                        $payroll->code,
                                                        $request->canceled_at,
                                                        $financePaymentExecute->description,
                                                        true
                                                    );
                                                }
                                            }
                                        }
                                    }

                                    if ($isAccounting) {
                                        /* Reverso de Asiento contable de la orden de pago */
                                        $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $pay_order->code)->first();
                                        $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
                                            'from_date' => $request->canceled_at,
                                            // Código de la ejecución de pago como referencia
                                            'reference' => $pay_order->code,
                                            'concept' => 'Anulación: ' . $accountEntry->concept,
                                            'observations' => $financePaymentExecute->description,
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
                                            'accounting_entryable_id' => $pay_order->id,
                                        ]);
                                    }
                                }
                            }
                        }
                    } elseif ($request->cancel_payment_execute_option_id == 2) {
                        // Anulación Con remisión, se libera la orden pargo
                        $payOrdersPaymentExecute = FinancePayOrderFinancePaymentExecute::query()
                            ->where(
                                'finance_payment_execute_id',
                                $financePaymentExecute->id
                            );

                        // Estatus Por revisar = Por aprobar
                        $documentStatusPR = DocumentStatus::where('action', 'PR')->first();

                        if ($payOrdersPaymentExecute) {
                            /* Si la emisión de pago es parcial se detiene el resto del proceso de anulación */
                            if ($financePaymentExecute->is_partial) {
                                $pay_order = FinancePayOrder::findOrFail($payOrdersPaymentExecute->first()->finance_pay_order_id);

                                //Se verifica que la orden de págo pertenezca a varias emisones de pago
                                $finance_count = $pay_order->financePaymentExecute()
                                    ->where('document_status_id', '!=', $documentStatus->id)->count();

                                if ($finance_count > 0) {
                                    $pay_order->status = 'PP';
                                    $pay_order->save();
                                } else {
                                    $pay_order->status = 'PE';
                                    $pay_order->document_status_id = $documentStatusPR->id;
                                    $pay_order->save();
                                }
                                /*
                                 | Se buscan todas las BudgetStage (etapas presupuestarias)
                                 | pertenecintes aesta emisión de pago para ser eliminadas
                                 */
                                \Modules\Budget\Models\BudgetStage::query()
                                    ->where([
                                        'stageable_type' => FinancePaymentExecute::class,
                                        'stageable_id' => $financePaymentExecute->id,
                                    ])
                                    ->where('type', 'PAG')->delete();
                            } else {
                                foreach ($payOrdersPaymentExecute->get() as $payOrderPayExecute) {
                                    //Se buscan todas las órdenes de pago asociadas a esta emisión y se cambia su estatus
                                    $pay_order = FinancePayOrder::find($payOrderPayExecute->finance_pay_order_id);

                                    if ($pay_order) {
                                        $pay_order->status = 'PE';
                                        $pay_order->document_status_id = $documentStatusPR->id;
                                        $pay_order->save();
                                        if ($pay_order->document_sourceable_type == \Modules\Budget\Models\BudgetCompromise::class) {
                                            $compromise = $isBudget ? \Modules\Budget\Models\BudgetCompromise::find(
                                                $pay_order->document_sourceable_id
                                            ) : null;
                                            /*
                                             | Se buscan todas las BudgetStage (etapas presupuestarias)
                                             | pertenecintes al compromiso relacionado con las ordenes de
                                             | pago de esta emisión para ser eliminadas
                                             */
                                            if (isset($compromise)) {
                                                \Modules\Budget\Models\BudgetStage::query()
                                                    ->where([
                                                        'budget_compromise_id' => $compromise->id,
                                                        'stageable_type' => FinancePaymentExecute::class,
                                                        'stageable_id' => $financePaymentExecute->id,
                                                    ])
                                                    ->where('type', 'PAG')->delete();

                                                if ($isPayroll) {
                                                    $payroll = \Modules\Payroll\Models\Payroll::query()
                                                        ->where([
                                                            'id' => $compromise->sourceable_id,
                                                            'code' => $compromise->document_number,
                                                        ])->first() ?? null;

                                                    if (isset($payroll)) {
                                                        // Se procede a realizar todo el proceso de anulación
                                                        // de los aportes de nómina
                                                        $this->cancelContribution(
                                                            $payroll->code,
                                                            $request->canceled_at,
                                                            $financePaymentExecute->description,
                                                        );
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            return response()->json(['message' => 'Success'], 200);
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
                        'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage),
                    ]],
                500
            );
        }
    }

    /**
     * Método que permite anular las emisiones, las ordenes
     * de pago y los compromisios de los aportes de nómina
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param  string $code Código o número de documento a buscar
     * @param  string $date Fecha del registro
     * @param  string $description Descripción del registro
     * @param  boolean $option (true = Anulación de todo el proceso, false = Anula solo el proceso de emision de pago)
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
                        'budget_compromise_id' => $compContribution->id,
                        'stageable_type' => FinancePaymentExecute::class,
                    ])
                    ->where('type', 'PAG')->get() ?? null;

                //Se realiza todo el proceso de anulación para las emisiones de pago
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
                                    'concept' => 'Anulación: ' . $accountEntry->concept,
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
                                    'budget_compromise_id' => $compContribution->id,
                                    'stageable_type' => FinancePaymentExecute::class,
                                    'stageable_id' => $financePaymentExecute->id,
                                ])
                                ->where('type', 'PAG')->delete();

                            /* Buscar los movimientos bancarios, actualizar el concepto del movimiento,
                            y cambiar el estatus a anulado el registro */
                            $bankingMovementPaymentExecute = FinanceBankingMovement::query()
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
                        'budget_compromise_id' => $compContribution->id,
                        'stageable_type' => FinancePayOrder::class,
                    ])
                    ->where('type', 'CAU')->get();

                //Se realiza todo el proceso de anulación para las ordenes de pago
                if (isset($payOrderBugetStages)) {
                    foreach ($payOrderBugetStages as $payOrderBugetStage) {
                        // Se buscan todas las órdenes de pago asociadas a este compromiso
                        $financePayOrder = FinancePayOrder::query()
                            ->find($payOrderBugetStage->stageable_id);

                        if (isset($financePayOrder)) {
                            $financePayOrder->status = 'PE';
                            $financePayOrder->document_status_id = $option
                            ? $documentStatusAN->id : DocumentStatus::where('action', 'PR')->first()->id;
                            $financePayOrder->observations = $option
                            ? 'ANULADO: ' . $financePayOrder->observations
                            . '. (' . $description . ')'
                            : $financePayOrder->observations;
                            $financePayOrder->save();

                            if ($option && $isAccounting) {
                                /* Reverso de Asiento contable de la orden de pago */
                                $accountEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePayOrder->code)->first();
                                $accountEntryNew = \Modules\Accounting\Models\AccountingEntry::create([
                                    'from_date' => $date,
                                    // Código de la ejecución de pago como referencia
                                    'reference' => $financePayOrder->code,
                                    'concept' => 'Anulación: ' . $accountEntry->concept,
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
                            if ($option) {
                                \Modules\Budget\Models\BudgetStage::query()
                                    ->where([
                                        'budget_compromise_id' => $compContribution->id,
                                        'stageable_type' => FinancePayOrder::class,
                                        'stageable_id' => $financePayOrder->id,
                                    ])
                                    ->where('type', 'CAU')->delete();
                            }
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
                            'document_status_id' => null,
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
                                'total_year_amount_m' => $budgetAccountOpen->total_year_amount_m + $total,
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
                }
            }
        }
    }

    /**
     * Método que permite crear los movimientos bancarios a partir
     * de una emisión de pago
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> | <javierrupe19@gmail.com>
     *
     * @param  FinancePaymentExecute|array $financePaymentExecute datos de la emisión de pago
     * @param  integer $pay_order_id identificador de la orden de pago
     * @param  string $date fecha en que se crea el movimiento bancario
     *
     * @return void
     */
    private function createBankingMovement($financePaymentExecute, $pay_order_id, $date)
    {
        $financePayOrder = FinancePayOrder::query()->find($pay_order_id);
        if (isset($financePayOrder)) {
            $currentFiscalYear = FiscalYear::query()
                ->where([
                    'active' => true,
                    'closed' => false,
                    'institution_id' => $financePayOrder['institution_id'],
                ])->orderBy('year', 'desc')->first();

            $codeSetting = CodeSetting::where('table', 'finance_movements_code')->first();

            list($year, $month, $day) = explode("-", $date);

            $codeMovement = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : $year),
                $codeSetting->model,
                $codeSetting->field
            );
            $bankingMovement = FinanceBankingMovement::create([
                'code' => $codeMovement,
                'payment_date' => $date,
                'transaction_type' => 'Nota de débito',
                'reference' => $financePaymentExecute['code'],
                'concept' => $financePayOrder['concept'],
                'amount' => $financePaymentExecute['paid_amount'],
                'currency_id' => $financePaymentExecute['currency_id'],
                'finance_bank_account_id' => $financePayOrder['finance_bank_account_id'],
                'institution_id' => $financePayOrder['institution_id'],
            ]);
            $accountingEntry = \Modules\Accounting\Models\AccountingEntry::where('reference', $financePaymentExecute["code"])->first();

            \Modules\Accounting\Models\AccountingEntryable::create([
                'accounting_entry_id' => $accountingEntry->id,
                'accounting_entryable_type' => FinanceBankingMovement::class,
                'accounting_entryable_id' => $bankingMovement->id,
            ]);
        }
    }

    /**
     * Obtiene el identificador de la cuenta contable asociada al banco
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return integer|void
     */
    public function getBankAccountingAccountId(Request $request)
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $accountingAccountId = FinanceBankAccount::find($request->finance_bank_account_id);
            return $accountingAccountId?->accounting_account_id;
        }
    }
}
