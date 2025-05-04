<?php

namespace Modules\Payroll\Http\Controllers;

use DateTime;
use App\Models\Source;
use App\Models\Profile;
use App\Models\Currency;
use App\Models\Receiver;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use App\Models\Institution;
use Illuminate\Http\Request;
use App\Models\DocumentStatus;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Nwidart\Modules\Facades\Module;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Exports\PayrollExport;
use Modules\Payroll\Models\PayrollConcept;
use Illuminate\Contracts\Support\Renderable;
use App\Exceptions\ClosedFiscalYearException;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Modules\Payroll\Http\Resources\PayrollResource;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Jobs\PayrollCreatePaymentRelationship;
use Modules\Payroll\Jobs\PayrollUpdatePaymentRelationship;

/**
 * @class      PayrollController
 * @brief      Controlador de registros de nómina
 *
 * Clase que gestiona los registros de nómina
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollController extends Controller
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
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.registers.list', ['only' => ['index', 'vueList']]);
        $this->middleware('permission:payroll.registers.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:payroll.registers.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:payroll.registers.close', ['only' => 'close']);
        $this->middleware('permission:payroll.registers.report', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'created_at'                => ['required'],
            'name'                      => ['required'],
            'payroll_payment_type_id'   => ['required'],
            'payroll_payment_period_id' => ['required'],
            'payroll_concepts'          => ['required']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'created_at.required'                => 'El campo fecha de generación es obligatorio.',
            'payroll_payment_type_id.required'   => 'El campo tipo de pago de nómina es obligatorio.',
            'payroll_payment_period_id.required' => 'El campo período de pago es obligatorio.',
            'payroll_concepts.required'          => 'El campo concepto es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de las nóminas de sueldos registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        /* datos de los empleados que tiene asociado un perfil de usuario */
        $employments_users = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        $profileUser = auth()->user()->profile;
        if ($profileUser && $profileUser->institution_id !== null) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $employment_with_users = PayrollEmployment::with('profile', 'payrollStaff')
            ->whereHas('profile', function ($query) use ($institution) {
                $query->where('institution_id', $institution->id);
            })->get();

        if ($employment_with_users) {
            foreach ($employment_with_users as $emp_user) {
                if ($emp_user->profile && $emp_user->profile->user_id) {
                    $text = '';

                    if ($emp_user->payrollStaff->id_number) {
                        $text = $emp_user->payrollStaff->id_number . ' - ' .
                            $emp_user->payrollStaff->first_name . ' ' . $emp_user->payrollStaff->last_name;
                    } else {
                        $text = $emp_user->payrollStaff->passport . ' - ' .
                            $emp_user->payrollStaff->first_name . ' ' . $emp_user->payrollStaff->last_name;
                    }

                    array_push($employments_users, [
                        'id' => $emp_user->profile->user_id,
                        'text' => $text,
                    ]);
                }
            }
        }

        $momentClosePermission = auth()->user()->hasPermission('payroll.registers.moment.close');

        $availabilityRequestPermission = auth()->user()->hasPermission('payroll.availability.request');

        return view(
            'payroll::registers.index',
            [
                'employments' => json_encode($employments_users),
                'momentClosePermission' => json_encode($momentClosePermission),
                'availabilityRequestPermission' => json_encode($availabilityRequestPermission)
            ]
        );
    }

    /**
     * Muestra el formulario para registrar una nueva nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('payroll::registers.create-edit');
    }

    /**
     * Valida y registra una nueva nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $period = PayrollPaymentPeriod::find($request->payroll_payment_period_id);

        if (isset($period)) {
            $date = new DateTime($period->start_date);
            $formatedDate = $date->format('Y');

            if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                $institution = Institution::query()
                    ->where(['id' => auth()->user()->profile->institution_id])
                    ->first();
            } else {
                $institution = Institution::query()
                    ->where(['active' => true, 'default' => true])
                    ->first();
            }

            $currentFiscalYear = FiscalYear::query()
                ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                ->orderBy('year', 'desc')
                ->first();

            if (isset($currentFiscalYear->entries)) {
                return throw new ClosedFiscalYearException(
                    __('No puede registrar, actualizar o eliminar ' .
                        'registros debido a que se está realizando el cierre de año fiscal')
                );
            }

            $closedFiscalYear = FiscalYear::query()
                ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
                ->orderBy('year', 'desc')
                ->first();

            if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
                return throw new ClosedFiscalYearException(
                    __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
                );
            }

            $this->validateRules['created_at'] = [
                'required',
                'before_or_equal:' . $period->end_date,
                'after_or_equal:' . $period->start_date
            ];

            $formatedStartDate = $date->format('d/m/Y');
            $endDate = new DateTime($period->end_date);
            $formatedEndDate = $endDate->format('d/m/Y');

            $this->messages['created_at.after_or_equal'] = 'El campo fecha de generación debe ser posterior o igual a '
                . $formatedStartDate;
            $this->messages['created_at.before_or_equal'] = 'El campo fecha de generación debe ser anterior o igual a '
                . $formatedEndDate;
        }

        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where(['model' => Payroll::class, 'table' => 'payrolls'])->first();

        if (!$codeSetting) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]], 422);
        }

        list($year, $month, $day) = explode("-", $request->created_at);

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? substr($year, 0, 2) : $year,
            Payroll::class,
            'code'
        );
        $params = $request->all();
        $user = auth()->user();
        $params['code'] = $code;
        $params['user_id'] = $user->id;
        $params['institution_id'] = $user->profile?->institution_id ?? null;

        PayrollCreatePaymentRelationship::dispatch($params);

        $request->session()->flash('message', ['type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
            'minutos. Se le notificará al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return response()->json(['redirect' => route('payroll.registers.index')], 200);
    }

    /**
     * Muestra el formulario con la información de una nómina de sueldos registrada
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        /* Objeto asociado al modelo Payroll */
        $payroll = Payroll::with("payrollPaymentPeriod.payrollPaymentType.payrollConcepts")->find($id);
        return view('payroll::registers.show', compact('payroll'));
    }

    /**
     * Muestra el formulario para actualizar la información de una nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        /* Objeto asociado al modelo Payroll */
        $payroll = Payroll::find($id);
        return view('payroll::registers.create-edit', compact('payroll'));
    }

    /**
     * Actualiza la información de una nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $period = PayrollPaymentPeriod::find($request->payroll_payment_period_id);

        if (isset($period)) {
            $date = new DateTime($period->start_date);
            $formatedDate = $date->format('Y');

            if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                $institution = Institution::query()
                    ->where(['id' => auth()->user()->profile->institution_id])
                    ->first();
            } else {
                $institution = Institution::query()
                    ->where(['active' => true, 'default' => true])
                    ->first();
            }

            $currentFiscalYear = FiscalYear::query()
                ->where(['active' => true, 'closed' => false, 'institution_id' => $institution->id])
                ->orderBy('year', 'desc')
                ->first();

            if (isset($currentFiscalYear->entries)) {
                return throw new ClosedFiscalYearException(
                    __('No puede registrar, actualizar o eliminar ' .
                        'registros debido a que se está realizando el cierre de año fiscal')
                );
            }

            $closedFiscalYear = FiscalYear::query()
                ->where(['active' => false, 'closed' => true, 'institution_id' => $institution->id])
                ->orderBy('year', 'desc')
                ->first();

            if (isset($closedFiscalYear) && $formatedDate == $closedFiscalYear->year) {
                return throw new ClosedFiscalYearException(
                    __('No puede registrar, actualizar o eliminar registros de un año fiscal cerrado')
                );
            }
        }

        $this->validate($request, $this->validateRules, $this->messages);
        $user = auth()->user();
        $params = $request->all();
        $params['user_id'] = $user->id;
        $params['institution_id'] = $user->profile?->institution_id ?? null;
        PayrollUpdatePaymentRelationship::dispatch($params);

        $request->session()->flash('message', ['type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
            'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return response()->json(['redirect' => route('payroll.registers.index')], 200);
    }

    /**
     * Elimina una nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        /* Objeto asociado al modelo Payroll */
        $payroll = Payroll::find($id);
        $payroll->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene la información de una nómina de sueldos registrada
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer  $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function vueInfo($id)
    {
        /* Objeto asociado al modelo Payroll */
        $payroll = Payroll::with(['payrollStaffPayrolls', 'payrollPaymentPeriod.payrollPaymentType.payrollConcepts'])->find($id);
        return response()->json(['record' => $payroll], 200);
    }

    /**
     * Obtiene un listado de los registros de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList()
    {
        return response()->json(
            [
                'records' => PayrollResource::collection(Payroll::query()
                    ->with(['payrollPaymentPeriod.payrollPaymentType.payrollConcepts'])->get())
            ],
            200
        );
    }

    /**
     * Actualiza el estado de una nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function close(Request $request, $id)
    {
        try {
            DB::transaction(function () use ($request, $id) {
                if (auth()->user()->hasPermission('payroll.registers.moment.close')) {
                    $payroll = Payroll::find($id);
                    $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                    $payrollPaymentPeriod->payment_status = 'generated';
                    $payrollPaymentPeriod->save();
                } else {
                    $payroll = Payroll::find($id);

                    if ($payroll?->payrollPaymentPeriod?->payrollPaymentType?->skip_moments == true) {
                        $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                        $payrollPaymentPeriod->payment_status = 'generated';
                        $payrollPaymentPeriod->save();

                        $request->session()->flash('message', ['type' => 'update']);
                        return response()->json(['redirect' => route('payroll.registers.index')], 200);
                    }

                    $currentFiscalYear = FiscalYear::select('year')
                        ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
                    /** @todo Se valida la información de las cuentas asociadas */
                    $dataPostValidate = $this->payrollValidateAccounts($payroll);
                    $idInstitutionAccount = $dataPostValidate['idInstitutionAccount'];
                    $accountingAccountsA = $dataPostValidate['accountingAccountsA'];
                    $totalDebit = $dataPostValidate['totalDebit'];
                    $totalAsset = $dataPostValidate['totalAsset'];
                    $totalDeduction = $dataPostValidate['totalDeduction'];
                    $totals = $dataPostValidate['totals'];
                    $model = $dataPostValidate['model'];
                    $nameDecimalFunction = $dataPostValidate['nameDecimalFunction'];
                    $number_decimals = $dataPostValidate['number_decimals'];
                    $deductionToPayOrder = $dataPostValidate['deductionToPayOrder'];

                    $accountingAccounts = array_merge(
                        array(
                            [
                                'id' => $idInstitutionAccount->p_value,
                                'debit' => $totalDebit,
                                'assets' => 0,
                            ]
                        ),
                        array(
                            [
                                'id' => $model->payrollPaymentPeriod->payrollPaymentType->financeBankAccount->accounting_account_id,
                                'debit' => 0,
                                'assets' => $totalDebit,
                            ]
                        ),
                    );

                    $accountingAccountsOrder = array_merge(
                        $accountingAccountsA,
                        array(
                            [
                                'id' => $idInstitutionAccount->p_value,
                                'debit' => 0,
                                'assets' => $totalDebit,
                            ]
                        )
                    );

                    // Se registra en el período de pago el cambio
                    $payroll = Payroll::find($id);
                    $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
                    $payrollPaymentPeriod->payment_status = 'generated';
                    $payrollPaymentPeriod->save();

                    /** @todo Se generan los asientos contables */
                    if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                        if ($accountingAccounts && !empty($accountingAccounts)) {
                            $is_admin = auth()->user()->level == 1 ? true : false;
                            if ($is_admin) {
                                $institution = Institution::where('default', true)->first();
                            } else {
                                $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

                                $institution = $user_profile['institution'];
                            }

                            $category = \Modules\Accounting\Models\AccountingEntryCategory::where('acronym', 'ND')->first();
                            $date = $payroll->created_at;
                            list($year, $month, $day) = explode("-", $date);
                            $reference = $payroll->code;
                            $currency = Currency::where('default', true)->first();
                        }

                        if (Module::has('Budget') && Module::isEnabled('Budget')) {
                            /* Estado inicial del compromiso establecido a elaborado */
                            $documentStatusEL = DocumentStatus::where('action', 'EL')->first();
                            /* Estado Comprometido del compromiso establecido a PROCESADO */
                            $documentStatusPR = DocumentStatus::where('action', 'PR')->first();
                            /* Estado Causado del compromiso establecido a Aprobado */
                            $documentStatus = DocumentStatus::where('action', 'AP')->first();

                            /* Datos del compromiso */
                            $compromise = \Modules\Budget\Models\BudgetCompromise::query()
                                ->where([
                                    'sourceable_type' => Payroll::class,
                                    'sourceable_id' => $payroll->id,
                                    'document_number' => $reference,
                                    'document_status_id' => $documentStatusEL->id,
                                ])
                                ->with('budgetStages', function ($query) {
                                    $query->where([
                                        'type' => 'PRE',
                                    ]);
                                })
                                ->first();
                            $compromise->compromised_at = $date;
                            $compromise->description = "Pago de nómina $reference correspondiente al período " .
                                $payrollPaymentPeriod->start_date . ' - ' .
                                $payrollPaymentPeriod->end_date;
                            $compromise->document_status_id = $documentStatusPR->id;
                            $compromise->save();

                            $receiver = Receiver::updateOrCreate(
                                [
                                    'receiverable_type' => Institution::class,
                                    'receiverable_id' => $institution->id,
                                    'associateable_type' => \Modules\Accounting\Models\AccountingAccount::class,
                                    'associateable_id' => $idInstitutionAccount->p_value ??
                                        $institution->pivot_accounting_account_id ??
                                        \Modules\Accounting\Models\AccountingAccount::first()->id
                                ],
                                [
                                    'group' => 'Institución',
                                    'description' => $institution->name
                                ]
                            );

                            Source::create(
                                [
                                    'receiver_id' => $receiver->id,
                                    'sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class,
                                    'sourceable_id' => $compromise->id,
                                ]
                            );

                            $total = $totalDebit;

                            $compromiseTotal = $compromise->budgetStages[0]['amount'];

                            $compromise->budgetStages()->update([
                                'type' => 'COM',
                                'amount' => $compromiseTotal,
                            ]);

                            /* Se agrega el compromiso de aportes */
                            if (count($totals['NA']) > 0) {
                                $countAP = 0;
                                foreach ($totals['NA'] ?? [] as $keyNA => $valuesNA) {
                                    $countAP++;
                                    foreach ($valuesNA as $valNA) {
                                        if (array_key_exists('id', $valNA)) {
                                            $compromiseContribution = \Modules\Budget\Models\BudgetCompromise::query()
                                                ->where('sourceable_type', Payroll::class)
                                                ->where('sourceable_id', $id)
                                                ->where('compromiseable_type', PayrollConcept::class)
                                                ->where('compromiseable_id', $valNA['id'])
                                                ->with('budgetStages', function ($query) {
                                                    $query->where([
                                                        'type' => 'PRE',
                                                    ]);
                                                })
                                                ->first();
                                            if ($compromiseContribution != null) {
                                                break;
                                            }
                                        }
                                    }

                                    $compromiseContributionTotal = $compromiseContribution->budgetStages[0]['amount'];

                                    if (isset($compromiseContribution)) {
                                        $compromiseContribution->compromised_at = $date;
                                        $compromiseContribution->description = "Pago de aportes nómina AP - $countAP$reference correspondiente al período " .
                                            $payrollPaymentPeriod->start_date . ' - ' .
                                            $payrollPaymentPeriod->end_date;
                                        $compromiseContribution->document_status_id = $documentStatus->id;
                                        $compromiseContribution->save();
                                    }

                                    $totalContributions = 0;
                                    $spac = null;
                                    $rec = null;

                                    $source = (
                                        $compromiseContribution
                                    ) ? Source::query()
                                        ->with('receiver')
                                        ->where('sourceable_id', $compromiseContribution->id)
                                        ->where('sourceable_type', \Modules\Budget\Models\BudgetCompromise::class)
                                        ->first() : null;

                                    foreach ($valuesNA as $key => $value) {
                                        $rec = $value['receiver'];
                                        if ($source->receiver->description == $rec->description) {
                                            $rec = $source->receiver;
                                        }
                                        $totalContributions += $value['valueTotal'];
                                    }

                                    $compromiseContribution->budgetStages()->update([
                                        'type' => 'COM',
                                        'amount' => $compromiseContributionTotal
                                    ]);

                                    /** @todo Se registra la orden de pago de los aportes si existe el módulo de finanzas */
                                    if (Module::has('Finance') && Module::isEnabled('Finance')) {
                                        /**
                                         *  @todo Se debe obtener el método de pago por defecto
                                         */
                                        $financePaymentMethod = \Modules\Finance\Models\FinancePaymentMethods::findOrFail($payrollPaymentPeriod->payrollPaymentType->finance_payment_method_id);
                                        if (count($totals['NA']) > 0) {
                                            if ($rec != null) { // Probar obtener receiver por el key
                                                $codeSettingOrder = CodeSetting::where("model", \Modules\Finance\Models\FinancePayOrder::class)->first();
                                                $newCode = generate_registration_code(
                                                    $codeSettingOrder->format_prefix,
                                                    strlen($codeSettingOrder->format_digits),
                                                    (strlen($codeSettingOrder->format_year) == 2) ? (isset($currentFiscalYear) ?
                                                        substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                                                        $currentFiscalYear->year : $year),
                                                    \Modules\Finance\Models\FinancePayOrder::class,
                                                    'code'
                                                );

                                                /* Se obtiene la acción específica desde el compromiso de aportes */
                                                $contributionSpecificActionId = null;
                                                if ($compromiseContribution) {
                                                    foreach ($compromiseContribution->budgetCompromiseDetails as $compromiseDetail) {
                                                        $contributionSpecificActionId = $compromiseDetail->budgetSubSpecificFormulation->specificAction->id;
                                                        break;
                                                    }
                                                }

                                                /* @todo Se registra la orden de pago */
                                                $financePayOrderContribution = \Modules\Finance\Models\FinancePayOrder::create([
                                                    'code' => $newCode,
                                                    'ordered_at' => $date,
                                                    'type' => 'PR',
                                                    'is_partial' => false,
                                                    'pending_amount' => 0,
                                                    'completed' => true,
                                                    'document_type' => 'O',
                                                    'document_number' => null,
                                                    'source_amount' => $totalContributions,
                                                    'amount' => $totalContributions,
                                                    'concept' => "Pago de aportes de nómina AP - $countAP$reference correspondiente al período " .
                                                        $payrollPaymentPeriod->start_date . ' - ' .
                                                        $payrollPaymentPeriod->end_date,
                                                    'observations' => '',
                                                    'status' => 'PE',
                                                    'budget_specific_action_id' => $contributionSpecificActionId,
                                                    'institution_id' => $institution->id,
                                                    'document_status_id' => $documentStatusPR->id,
                                                    'currency_id' => $currency->id,
                                                    'name_sourceable_type' => str_replace("modules", "Modules", Receiver::class),
                                                    'name_sourceable_id' => $rec->id,
                                                    'document_sourceable_id' => $compromiseContribution->id ?? null,
                                                    'document_sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class ?? null
                                                ]);

                                                /** @todo Validar segundo estado financiero */
                                                $newCodeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');

                                                if (isset($compromiseContribution) && isset($newCodeStage)) {
                                                    $compromiseContribution->budgetStages()->create([
                                                        'code' => $newCodeStage,
                                                        'registered_at' => $date,
                                                        'type' => 'CAU',
                                                        'amount' => $compromiseContributionTotal,
                                                        'stageable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                                        'stageable_id' => $financePayOrderContribution->id,
                                                    ]);
                                                }

                                                /** @todo Se registra el asiento contable de la orden de pago de los aportes si existe el módulo de contabilidad */
                                                if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
                                                    $accountingAccountsContributions = [];

                                                    foreach ($valuesNA as $key => $value) {
                                                        if ($value['accounting_account_id']) {
                                                            array_push($accountingAccountsContributions, [
                                                                'id' => $value['accounting_account_id'],
                                                                'debit' => $nameDecimalFunction($value['value'], $number_decimals->p_value),
                                                                'assets' => 0,
                                                            ]);
                                                        }
                                                    }

                                                    if ($value['receiver'] && $value['receiver']['associateable_id']) {
                                                        array_push($accountingAccountsContributions, [
                                                            'id' => $value['receiver']['associateable_id'],
                                                            'debit' => 0,
                                                            'assets' => $nameDecimalFunction($totalContributions, $number_decimals->p_value),
                                                        ]);
                                                    }

                                                    /* Asiento contable */
                                                    $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::findOrFail($payrollPaymentPeriod->payrollPaymentType->accounting_entry_category_id);

                                                    \Modules\Accounting\Jobs\AccountingManageEntries::dispatch(
                                                        [
                                                            'date' => $date,
                                                            'reference' => $newCode,
                                                            'concept' => "Orden de pago de aportes AP - $countAP$reference de nómina correspondiente al período " .
                                                                $payrollPaymentPeriod->start_date . ' - ' .
                                                                $payrollPaymentPeriod->end_date,
                                                            'observations' => '',
                                                            'category' => $accountingCategory->id,
                                                            'currency_id' => $currency->id,
                                                            'totDebit' => $totalContributions,
                                                            'totAssets' => $totalContributions,
                                                            'module' => 'Finance',
                                                            'model' => \Modules\Finance\Models\FinancePayOrder::class,
                                                            'relatable_id' => $financePayOrderContribution->id,
                                                            'accountingAccounts' => $accountingAccountsContributions
                                                        ],
                                                        $institution->id,
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        /* Se obtiene la acción específica desde el compromiso */
                        $specificActionId = null;
                        if ($compromise) {
                            foreach ($compromise->budgetCompromiseDetails as $compromiseDetail) {
                                $specificActionId = $compromiseDetail->budgetSubSpecificFormulation->specificAction->id;
                                break;
                            }
                        }

                        if ($payrollPaymentPeriod->payrollPaymentType->order) {
                            /* Se aprueba el compromiso */
                            $compromise->document_status_id = $documentStatus->id;
                            $compromise->save();

                            /** @todo Validar pendingAmount */
                            $codeSetting = CodeSetting::where("model", \Modules\Finance\Models\FinancePayOrder::class)->first();

                            $code = generate_registration_code(
                                $codeSetting->format_prefix,
                                strlen($codeSetting->format_digits),
                                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                                    substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                                    $currentFiscalYear->year : $year),
                                \Modules\Finance\Models\FinancePayOrder::class,
                                'code'
                            );

                            /** @todo Se debe obtener el método de pago por defecto */
                            $financePaymentMethod = \Modules\Finance\Models\FinancePaymentMethods::query()
                                ->findOrFail($payrollPaymentPeriod->payrollPaymentType->finance_payment_method_id);

                            /** @todo Se registra la orden de pago */
                            $financePayOrder = \Modules\Finance\Models\FinancePayOrder::create([
                                'code' => $code,
                                'ordered_at' => $date,
                                'type' => 'PR',
                                'is_partial' => false,
                                'pending_amount' => 0,
                                'completed' => true,
                                'document_type' => 'O',
                                'document_number' => null,
                                'source_amount' => $total,
                                'amount' => $total,
                                'concept' => "Pago de nómina $reference correspondiente al período " .
                                    $payrollPaymentPeriod->start_date . ' - ' .
                                    $payrollPaymentPeriod->end_date,
                                'observations' => '',
                                'status' => 'PE',
                                'budget_specific_action_id' => $specificActionId,
                                'institution_id' => $institution->id,
                                'document_status_id' => $documentStatus->id,
                                'currency_id' => $currency->id,
                                'name_sourceable_type' => str_replace("modules", "Modules", Receiver::class),
                                'name_sourceable_id' => $receiver->id,
                                'document_sourceable_id' => $compromise->id ?? null,
                                'document_sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class ?? null
                            ]);

                            /** @todo Validar segundo estado financiero */
                            $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');

                            if (isset($compromise) && isset($codeStage)) {
                                $compromise->budgetStages()->create([
                                    'code' => $codeStage,
                                    'registered_at' => $date,
                                    'type' => 'CAU',
                                    'amount' => $compromiseTotal - $totalDeduction,
                                    'stageable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                    'stageable_id' => $financePayOrder->id,
                                ]);
                            }

                            /* Asiento contable de la orden de pago de nómina */
                            $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::findOrFail($payrollPaymentPeriod->payrollPaymentType->accounting_entry_category_id);

                            $accountingAccountsToOrder = [];

                            foreach ($compromise->budgetCompromiseDetails as $compromiseDetail) {
                                $accountable = \Modules\Accounting\Models\Accountable::query()
                                    ->where('accountable_type', \Modules\Accounting\Models\BudgetAccount::class)
                                    ->where('accountable_id', $compromiseDetail->budget_account_id)
                                    ->first();

                                $accountingAccountsToOrder[] = [
                                    'id' => $accountable->accounting_account_id,
                                    'debit' => $compromiseDetail->amount,
                                    'assets' => 0,
                                ];
                            }

                            $accountingAccountsToOrder = array_merge(
                                $accountingAccountsToOrder,
                                [
                                    [
                                        'id' => (int)$idInstitutionAccount->p_value,
                                        'debit' => 0,
                                        'assets' => (string)$totalDebit,
                                    ]
                                ]
                            );

                            \Modules\Accounting\Jobs\AccountingManageEntries::dispatch(
                                [
                                    'date' => $date,
                                    'reference' => $code,
                                    'concept' => "Orden de pago de nómina $reference correspondiente al período " .
                                        $payrollPaymentPeriod->start_date . ' - ' .
                                        $payrollPaymentPeriod->end_date,
                                    'observations' => '',
                                    'category' => $accountingCategory->id,
                                    'currency_id' => $currency->id,
                                    'totDebit' => $totalDebit,
                                    'totAssets' => $totalAsset + ($totalDebit - $totalAsset),
                                    'module' => 'Finance',
                                    'model' => \Modules\Finance\Models\FinancePayOrder::class,
                                    'relatable_id' => $financePayOrder->id,
                                    'accountingAccounts' => $accountingAccountsToOrder
                                ],
                                $institution->id,
                            );

                            $codeSetting = CodeSetting::where("model", \Modules\Finance\Models\FinancePaymentExecute::class)->first();
                            $codePayment = generate_registration_code(
                                $codeSetting->format_prefix,
                                strlen($codeSetting->format_digits),
                                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                                    $currentFiscalYear->year : date('Y')),
                                \Modules\Finance\Models\FinancePaymentExecute::class,
                                $codeSetting->field
                            );

                            $financePaymentExecute = \Modules\Finance\Models\FinancePaymentExecute::create([
                                'code' => $codePayment,
                                'paid_at' => $date,
                                'has_budget' => true,
                                'is_partial' => false,
                                'source_amount' => $total,
                                'deduction_amount' => 0,
                                'paid_amount' => $total,
                                'pending_amount' => 0,
                                'completed' => true,
                                'finance_payment_method_id' => $financePaymentMethod->id,
                                'finance_bank_account_id' => $payrollPaymentPeriod->payrollPaymentType->finance_bank_account_id,
                                'observations' => "Orden de pago de nómina $reference correspondiente al período " .
                                    $payrollPaymentPeriod->start_date . ' - ' .
                                    $payrollPaymentPeriod->end_date,
                                //El stado de la emisión de pago se cambiará al momento de aprobar dicha emisión
                                // 'status' => 'PA',
                                'document_status_id' => $documentStatusPR->id,
                                'currency_id' => $currency->id,
                            ]);

                            $totalDeductions = 0;

                            /** @todo Crear una retención al registrar concepto de deducciones */
                            foreach ($totals['-'] as $deduction) {
                                if ($deduction['pay_order'] != true) {
                                    $totalDeductions += $deduction['value'] ?? 0;
                                    $financePaymentExecute->financePaymentDeductions()->create([
                                        'amount' => $deduction['value'] ?? 0,
                                        'deduction_id' => null,
                                        'finance_payment_execute_id' => $financePaymentExecute->id,
                                        'deductionable_id' => $deduction['id'],
                                        'deductionable_type' => PayrollConcept::class,
                                    ]);
                                }
                            }

                            $financePaymentExecute->deduction_amount = $totalDeductions;
                            $financePaymentExecute->save();

                            \Modules\Finance\Models\FinancePayOrderFinancePaymentExecute::create([
                                'finance_pay_order_id' => $financePayOrder->id,
                                'finance_payment_execute_id' => $financePaymentExecute->id,
                            ]);

                            if (isset($compromise)) {
                                $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');

                                $compromise->budgetStages()->create([
                                    'code' => $codeStage,
                                    'registered_at' => $date,
                                    'type' => 'PAG',
                                    'amount' => $compromiseTotal - $totalDeduction,
                                    'stageable_type' => \Modules\Finance\Models\FinancePaymentExecute::class,
                                    'stageable_id' => $financePaymentExecute->id,
                                ]);
                            }

                            /* Asiento contable */
                            $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::findOrFail($payrollPaymentPeriod->payrollPaymentType->accounting_entry_category_id);

                            \Modules\Accounting\Jobs\AccountingManageEntries::dispatch(
                                [
                                    'date' => $date,
                                    'reference' => $codePayment,
                                    'concept' => "Ejecución de pago de nómina $reference correspondiente al período " .
                                        $payrollPaymentPeriod->start_date . ' - ' .
                                        $payrollPaymentPeriod->end_date,
                                    'observations' => '',
                                    'category' => $accountingCategory->id,
                                    'currency_id' => $currency->id,
                                    'totDebit' => $totalDebit,
                                    'totAssets' => $totalAsset + ($totalDebit - $totalAsset),
                                    'module' => 'Finance',
                                    'model' => \Modules\Finance\Models\FinancePaymentExecute::class,
                                    'relatable_id' => $financePaymentExecute->id,
                                    'accountingAccounts' => $accountingAccounts
                                ],
                                $institution->id,
                            );
                        }

                        foreach ($deductionToPayOrder as $dPayOrder) {
                            $codeSetting = CodeSetting::where("model", \Modules\Finance\Models\FinancePayOrder::class)->first();
                            $codeD = generate_registration_code(
                                $codeSetting->format_prefix,
                                strlen($codeSetting->format_digits),
                                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                                    substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                                    $currentFiscalYear->year : $year),
                                \Modules\Finance\Models\FinancePayOrder::class,
                                'code'
                            );

                            /** @todo Se registra la orden de pago de las deducciones */
                            $financePayOrderDeducction = \Modules\Finance\Models\FinancePayOrder::create([
                                'code' => $codeD,
                                'ordered_at' => $date,
                                'type' => 'PR',
                                'is_partial' => false,
                                'pending_amount' => 0,
                                'completed' => true,
                                'document_type' => 'O',
                                'document_number' => null,
                                'source_amount' => $dPayOrder['amount'],
                                'amount' => $dPayOrder['amount'],
                                'concept' => "Pago de deducción de nómina $reference correspondiente al período " .
                                    $payrollPaymentPeriod->start_date . ' - ' .
                                    $payrollPaymentPeriod->end_date,
                                'observations' => '',
                                'status' => 'PE',
                                'budget_specific_action_id' => $specificActionId,
                                'institution_id' => $institution->id,
                                'document_status_id' => $documentStatusPR->id,
                                'currency_id' => $currency->id,
                                'name_sourceable_type' => str_replace("modules", "Modules", Receiver::class),
                                'name_sourceable_id' => $dPayOrder['receiver_id'],
                                'document_sourceable_id' => $dPayOrder['compromise_id'] ?? null,
                                'document_sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class ?? null
                            ]);

                            /** @todo Validar segundo estado financiero */
                            $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');
                            $compromiseDeduction = \Modules\Budget\Models\BudgetCompromise::find($dPayOrder['compromise_id']);
                            if (isset($compromiseDeduction) && isset($codeStage)) {
                                $documentStatusApproved = DocumentStatus::where('action', 'AP')->first();
                                $compromiseDeduction->compromised_at = $date;
                                $compromiseDeduction->document_status_id = $documentStatusApproved->id;
                                $compromiseDeduction->save();
                                $compromiseDeduction->budgetStages()->where('type', 'PRE')->delete();
                                $compromiseDeduction->budgetStages()->create([
                                    'code' => $codeStage,
                                    'registered_at' => $date,
                                    'type' => 'COM',
                                    'amount' => $dPayOrder['amount'],
                                    'stageable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                    'stageable_id' => $financePayOrderDeducction->id,
                                ]);

                                $codeStage = generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code');

                                $compromiseDeduction->budgetStages()->create([
                                    'code' => $codeStage,
                                    'registered_at' => $date,
                                    'type' => 'CAU',
                                    'amount' => $dPayOrder['amount'],
                                    'stageable_type' => \Modules\Finance\Models\FinancePayOrder::class,
                                    'stageable_id' => $financePayOrderDeducction->id,
                                ]);

                                $source = Source::query()
                                    ->where('sourceable_type', PayrollConcept::class)
                                    ->where('sourceable_id', $compromiseDeduction->compromiseable_id)
                                    ->first();

                                if ($source) {
                                    Source::create(
                                        [
                                            'receiver_id' => $source->receiver_id,
                                            'sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class,
                                            'sourceable_id' => $compromiseDeduction->id,
                                        ]
                                    );
                                }
                            }

                            /* Asiento contable de la orden de pago de nómina */
                            $accountingCategory = \Modules\Accounting\Models\AccountingEntryCategory::findOrFail($payrollPaymentPeriod->payrollPaymentType->accounting_entry_category_id);

                            \Modules\Accounting\Jobs\AccountingManageEntries::dispatch(
                                [
                                    'date' => $date,
                                    'reference' => $codeD,
                                    'concept' => "Orden de pago de deducción de nómina $reference correspondiente al período " .
                                        $payrollPaymentPeriod->start_date . ' - ' .
                                        $payrollPaymentPeriod->end_date,
                                    'observations' => '',
                                    'category' => $accountingCategory->id,
                                    'currency_id' => $currency->id,
                                    'totDebit' => $dPayOrder['amount'],
                                    'totAssets' => $dPayOrder['amount'],
                                    'module' => 'Finance',
                                    'model' => \Modules\Finance\Models\FinancePayOrder::class,
                                    'relatable_id' => $financePayOrderDeducction->id,
                                    'accountingAccounts' => $dPayOrder['accounts']
                                ],
                                $institution->id,
                            );
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
            return response()->json(['redirect' => route('payroll.registers.index')], 200);
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.registers.index')], 200);
    }

    /**
     * Traduce la formula de conceptos
     *
     * @param mixed $form Formula a traducir
     *
     * @return string
     */
    public function translateFormConcept($form)
    {
        $formula = $form;
        /* Se hace la busqueda de los parámetros globales */
        $parameters = Parameter::where(
            [
                'required_by' => 'payroll',
                'active'      => true,
            ]
        )->where('p_key', 'like', 'global_parameter_%')->get();
        foreach ($parameters as $parameter) {
            $jsonValue = json_decode($parameter->p_value);
            if ($jsonValue->parameter_type == 'resettable_variable' || $jsonValue->parameter_type == 'processed_variable') {
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->name, $formula);
            } else {
                $formula = str_replace('parameter(' . $jsonValue->id . ')', $jsonValue->value, $formula);
            }
        }
        /* Se hace la busqueda de los conceptos */
        $matchs = [];
        preg_match_all("/concept\([0-9]+\)/", $formula, $matchs);

        foreach ($matchs[0] as $match) {
            $id = substr($match, (strpos($match, "(") + 1), strpos($match, ")") - (strpos($match, "(") + 1));
            $concept = PayrollConcept::find($id);
            $formula = str_replace('concept(' . $id . ')', $this->translateFormConcept($concept['formula']), $formula);
        }
        return '(' . $formula . ')';
    }

    /**
     * Realiza la acción necesaria para exportar los datos del registro de nómina
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param     integer   $id    Identificador único del registro de nómina
     *
     * @return    object    Objeto que permite descargar el archivo con la información a ser exportada
     */
    public function export($id)
    {
        ini_set('max_execution_time', 300); /** 5min */
        try {
            $payroll = Payroll::where('id', $id)->first();
            //$export = new PayrollExport(Payroll::class);
            $export = new PayrollExport();
            $export->setPayrollId($payroll->id);
            return Excel::download($export, 'payroll_register' . $payroll->created_at . '.xlsx');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            request()->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'No se puede generar el archivo porque se ha presentando un error en la generación de la nómina.',
            ]);
            return redirect()->route('payroll.registers.index');
        }
    }

    /**
     * Muestra el formulario para registrar la disponibilidad presupuestaría
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function availability($id)
    {
        ini_set('max_execution_time', 600); /** 10min */
        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            /* Objeto asociado al modelo Payroll */
            $payroll = Payroll::with([
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.currency',
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.budgetAccount'
            ])->find($id);

            $round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
            $number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
            $nameDecimalFunction = $round->p_value == 'false' ? 'currency_format' : 'round';
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
            $records = $payroll->payrollStaffPayrolls()
                ->select('concept_type', 'payroll_staff_id')
                ->get()
                ->map(function ($record) {
                    return [
                        'payroll_staff' => [
                            'id' => $record->payrollStaff->id,
                            'name' => $record->payrollStaff->fullName,
                        ],
                        'concept_type' => $record->concept_type,
                    ];
                })
                ->toArray();

            $accounts = [];
            $totalAmount = 0;
            $budgetSpecificActionController = new \Modules\Budget\Http\Controllers\BudgetSpecificActionController();

            $itemIds = [];
            $items = [];
            $allBudgetAccounts = [];
            $receivers = [];
            $budgetAccounts = [];
            $budgetSpecificActions = [];

            array_map(function ($record) use (&$itemIds, &$items) {
                return array_map(function ($conceptType) use (&$itemIds, &$items) {
                    return array_map(function ($item) use (&$itemIds, &$items) {
                        $itemIds[] = $item['id'];
                        $item['deducted'] = false;
                        $items[] = $item;
                    }, $conceptType);
                }, $record['concept_type']);
            }, $records);

            $concepts = PayrollConcept::query()
                ->whereIn('id', $itemIds)
                ->toBase()
                ->get();

            foreach ($concepts as $concept) {
                $accs = $budgetSpecificActionController->getOpenedAccounts($concept->budget_specific_action_id, $currentFiscalYear->year . '-12-31');
                $accountFiltered = array_filter($accs->getData()->records, function ($account) use ($concept) {
                    return $account->id != '' && $account->id == $concept->budget_account_id;
                });
                $allBudgetAccounts[$concept->name] = reset($accountFiltered)->amount ?? 0.00;

                $receivers[$concept->name] = Receiver::query()
                    ->with('sources')
                    ->whereHas('sources', function ($query) use ($concept) {
                        $query
                            ->where('sourceable_id', $concept->id)
                            ->where('sourceable_type', PayrollConcept::class);
                    })
                    ->first();
                $budgetAccounts[$concept->name] = $concept->budget_account_id ? \Modules\Budget\Models\BudgetAccount::find($concept->budget_account_id) : null;
                $budgetSpecificActions[$concept->name] = $concept->budget_specific_action_id ? \Modules\Budget\Models\BudgetSpecificAction::find($concept->budget_specific_action_id) : null;
            }

            $compromises = \Modules\Budget\Models\BudgetCompromise::where('document_number', 'LIKE', '%' . $payroll->code . '%')
                ->with(['budgetCompromiseDetails', 'budgetStages' => function ($query) {
                    $query->where('type', 'PRE');
                }])
                ->get();

            foreach ($items as $value) {
                $concept = $concepts->where('id', $value['id'])->first();

                if ($budgetAccounts[$concept->name] && $concept->budget_specific_action_id) {
                    if ($value['value'] > 0) {
                        if (!isset($accounts[$value['name']])) {
                            $accounts[$value['name']]['id'] = $concept->id;
                            $accounts[$value['name']]['type'] = $value['sign'];
                            $accounts[$value['name']]['value'] = 0;
                            $accounts[$value['name']]['budget_account_code'] = $budgetAccounts[$concept->name]->code;
                            $accounts[$value['name']]['budget_specific_action_id'] = $concept->budget_specific_action_id;
                            $accounts[$value['name']]['budget_specific_action_desc'] = $budgetSpecificActions[$concept->name]->description;
                            $accounts[$value['name']]['budget_account_id'] = $concept->budget_account_id;
                            $accounts[$value['name']]['budget_account_amount'] = $allBudgetAccounts[$concept->name];
                            $accounts[$value['name']]['receiver'] = $receivers[$concept->name] ?? null;

                            if ($compromises != null) {
                                foreach ($compromises as $compromise) {
                                    foreach ($compromise->budgetCompromiseDetails as $details) {
                                        if ($concept->budget_account_id == $details->budget_account_id) {
                                            $accounts[$value['name']]['budget_account_amount'] += (float)$details->amount;
                                        }
                                    }
                                }
                            }
                        }

                        $accounts[$value['name']]['value'] += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                        $totalAmount += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                    }
                }
            }

            foreach ($items as $keyV => $value) {
                $concept = $concepts->where('id', $value['id'])->first();
                if ($value['sign'] == '-' && $budgetAccounts[$concept->name] && $concept->budget_specific_action_id) {
                    if ($value['value'] > 0 && $concept->pay_order == true) {
                        foreach ($accounts as $key => $account) {
                            if ($account['budget_account_id'] == $concept->budget_account_id) {
                                if ($value['deducted'] == false) {
                                    $value['deducted'] = true;
                                    $newValue = $nameDecimalFunction($value['value'], $number_decimals->p_value);
                                    $totalAmount -= $nameDecimalFunction($value['value'], $number_decimals->p_value);

                                    if ($accounts[$key]['value'] <= 0) {
                                        $excess = abs($newValue);

                                        // Restar el excedente a otra cuenta disponible con el mismo budget_account_id
                                        foreach ($accounts as $otherKey => $otherAccount) {
                                            if ($otherAccount['budget_account_id'] == $concept->budget_account_id) {
                                                if ($excess <= $otherAccount['value']) {
                                                    $accounts[$otherKey]['value'] -= $excess;
                                                    $newValue = 0;
                                                    break;
                                                } else {
                                                    $excess -= $otherAccount['value'];
                                                    $accounts[$otherKey]['value'] = 0;
                                                }
                                            }
                                        }
                                        $accounts[$key]['value'] = $newValue;
                                    } else {
                                        $accounts[$key]['value'] -= $newValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return view('payroll::registers.availability', [
                'payroll' => json_encode($payroll),
                'budgetAccounts' => json_encode($accounts),
                'totalAmount' => json_encode($totalAmount),
            ]);
        } else {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe tener instalado el módulo de presupuesto para acceder a esta funcionalidad',
            ]], 403);
        }
    }

    /**
     * Muestra el formulario para registrar la disponibilidad presupuestaría
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function availabilityShow($id)
    {
        ini_set('max_execution_time', 600); /** 10min */
        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            /* Objeto asociado al modelo Payroll */
            $payroll = Payroll::with([
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.currency',
                'payrollPaymentPeriod.payrollPaymentType.payrollConcepts.budgetAccount'
            ])->find($id);

            $round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
            $number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
            $nameDecimalFunction = $round->p_value == 'false' ? 'currency_format' : 'round';
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
            $records = $payroll->payrollStaffPayrolls()
                ->select('concept_type', 'payroll_staff_id')
                ->get()
                ->map(function ($record) {
                    return [
                        'payroll_staff' => [
                            'id' => $record->payrollStaff->id,
                            'name' => $record->payrollStaff->fullName,
                        ],
                        'concept_type' => $record->concept_type,
                    ];
                })
                ->toArray();

            $accounts = [];
            $totalAmount = 0;
            $budgetSpecificActionController = new \Modules\Budget\Http\Controllers\BudgetSpecificActionController();

            $itemIds = [];
            $items = [];
            $allBudgetAccounts = [];
            $budgetAccounts = [];
            $budgetSpecificActions = [];

            array_map(function ($record) use (&$itemIds, &$items) {
                return array_map(function ($conceptType) use (&$itemIds, &$items) {
                    return array_map(function ($item) use (&$itemIds, &$items) {
                        $itemIds[] = $item['id'];
                        $item['deducted'] = false;
                        $items[] = $item;
                    }, $conceptType);
                }, $record['concept_type']);
            }, $records);

            $concepts = PayrollConcept::query()
                ->whereIn('id', $itemIds)
                ->toBase()
                ->get();

            foreach ($concepts as $concept) {
                $accs = $budgetSpecificActionController->getOpenedAccounts($concept->budget_specific_action_id, $currentFiscalYear->year . '-12-31');
                $accountFiltered = array_filter($accs->getData()->records, function ($account) use ($concept) {
                    return $account->id != '' && $account->id == $concept->budget_account_id;
                });
                $allBudgetAccounts[$concept->name] = reset($accountFiltered)->amount ?? 0.00;

                $receivers[$concept->name] = Receiver::query()
                    ->with('sources')
                    ->whereHas('sources', function ($query) use ($concept) {
                        $query
                            ->where('sourceable_id', $concept->id)
                            ->where('sourceable_type', PayrollConcept::class);
                    })
                    ->first();
                $budgetAccounts[$concept->name] = $concept->budget_account_id ? \Modules\Budget\Models\BudgetAccount::find($concept->budget_account_id) : null;
                $budgetSpecificActions[$concept->name] = $concept->budget_specific_action_id ? \Modules\Budget\Models\BudgetSpecificAction::find($concept->budget_specific_action_id) : null;
            }

            $compromises = \Modules\Budget\Models\BudgetCompromise::where('document_number', 'LIKE', '%' . $payroll->code . '%')
                ->with(['budgetCompromiseDetails', 'budgetStages' => function ($query) {
                    $query->where('type', 'PRE');
                }])
                ->get();

            foreach ($items as $value) {
                $concept = $concepts->where('id', $value['id'])->first();

                if ($budgetAccounts[$concept->name] && $concept->budget_specific_action_id) {
                    if ($value['value'] > 0) {
                        if (!isset($accounts[$value['name']])) {
                            $accounts[$value['name']]['id'] = $concept->id;
                            $accounts[$value['name']]['type'] = $value['sign'];
                            $accounts[$value['name']]['value'] = 0;
                            $accounts[$value['name']]['budget_account_code'] = $budgetAccounts[$concept->name]->code;
                            $accounts[$value['name']]['budget_specific_action_id'] = $concept->budget_specific_action_id;
                            $accounts[$value['name']]['budget_specific_action_desc'] = $budgetSpecificActions[$concept->name]->description;
                            $accounts[$value['name']]['budget_account_id'] = $concept->budget_account_id;
                            $accounts[$value['name']]['budget_account_amount'] = $allBudgetAccounts[$concept->name];
                            $accounts[$value['name']]['receiver'] = $receivers[$concept->name] ?? null;

                            if ($compromises != null) {
                                foreach ($compromises as $compromise) {
                                    foreach ($compromise->budgetCompromiseDetails as $details) {
                                        if ($concept->budget_account_id == $details->budget_account_id) {
                                            $accounts[$value['name']]['budget_account_amount'] += (float)$details->amount;
                                        }
                                    }
                                }
                            }
                        }

                        $accounts[$value['name']]['value'] += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                        $totalAmount += $nameDecimalFunction($value['value'], $number_decimals->p_value);
                    }
                }
            }

            foreach ($items as $keyV => $value) {
                $concept = $concepts->where('id', $value['id'])->first();
                if ($value['sign'] == '-' && $budgetAccounts[$concept->name] && $concept->budget_specific_action_id) {
                    if ($value['value'] > 0 && $concept->pay_order == true) {
                        foreach ($accounts as $key => $account) {
                            if ($account['budget_account_id'] == $concept->budget_account_id) {
                                if ($value['deducted'] == false) {
                                    $value['deducted'] = true;
                                    $newValue = $nameDecimalFunction($value['value'], $number_decimals->p_value);
                                    $totalAmount -= $nameDecimalFunction($value['value'], $number_decimals->p_value);

                                    if ($accounts[$key]['value'] <= 0) {
                                        $excess = abs($newValue);

                                        // Restar el excedente a otra cuenta disponible con el mismo budget_account_id
                                        foreach ($accounts as $otherKey => $otherAccount) {
                                            if ($otherAccount['budget_account_id'] == $concept->budget_account_id) {
                                                if ($excess <= $otherAccount['value']) {
                                                    $accounts[$otherKey]['value'] -= $excess;
                                                    $newValue = 0;
                                                    break;
                                                } else {
                                                    $excess -= $otherAccount['value'];
                                                    $accounts[$otherKey]['value'] = 0;
                                                }
                                            }
                                        }
                                        $accounts[$key]['value'] = $newValue;
                                    } else {
                                        $accounts[$key]['value'] -= $newValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return response()->json(['records' => [
                'payroll' => $payroll,
                'budgetAccounts' => $accounts,
                'totalAmount' => $totalAmount,
            ]]);
        } else {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe tener instalado el módulo de presupuesto para acceder a esta funcionalidad',
            ]], 403);
        }
    }

    /**
     * Valida y registra una disponibilidad nueva para la nómina de sueldos
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse|void           Objeto con los registros a mostrar
     */
    public function availabilityStore(Request $request)
    {
        $errors = [];
        $accountsA = [];
        $accountsB = [];
        $accountsC = [];

        foreach ($request->budget_accounts as $key => $budgetAccount) {
            if ($request->availability == 1) {
                if (
                    $budgetAccount['budget_account_amount']
                    == 0 || $budgetAccount['value']
                    > $budgetAccount['budget_account_amount']
                ) {
                    $errors['error' . $key] = [
                        0 => 'El monto (Concepto) de la cuenta ' . $budgetAccount['budget_account_code']
                            . ' es mayor al monto disponible.',
                    ];
                }
            }

            if ($budgetAccount['type'] == '+') {
                array_push($accountsA, $budgetAccount);
            } elseif ($budgetAccount['type'] == '-') {
                if ($budgetAccount['receiver'] != null) {
                    $accountsC[$budgetAccount['receiver']['description']][] = $budgetAccount;
                }
            } elseif ($budgetAccount['type'] == 'NA') {
                if ($budgetAccount['receiver'] != null) {
                    $accountsB[$budgetAccount['receiver']['description']][] = $budgetAccount;
                }
            }
        }

        if (count($errors) > 0) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $errors], 422);
        }

        DB::transaction(function () use ($request, $accountsA, $accountsB, $accountsC) {
            if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
                $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
            } else {
                $institution = Institution::where(['active' => true, 'default' => true])->first();
            }
            $payroll = Payroll::with('payrollPaymentPeriod')->find($request->payroll_id);
            $availability = $request->availability == 1 ? 'available' : 'not_available';
            $payroll->payrollPaymentPeriod->availability_status = $availability;
            $payroll->payrollPaymentPeriod->save();

            //Se pregunta si el módulo 'Budget' (Presupuesto) está habilitado
            $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

            //Se procede a crear el compromiso relacionado a la orden de compra que se está aprobando
            if ($has_budget && $request->availability == 1) {
                $currentFiscalYear = FiscalYear::select('year')
                    ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

                $codeSettingCompromise = CodeSetting::where(
                    "model",
                    \Modules\Budget\Models\BudgetCompromise::class
                )->first();
                $codeCompromise = generate_registration_code(
                    $codeSettingCompromise->format_prefix,
                    strlen($codeSettingCompromise->format_digits),
                    (strlen($codeSettingCompromise->format_year) == 2) ?
                        substr($currentFiscalYear->year, 2, 2) : $currentFiscalYear->year,
                    \Modules\Budget\Models\BudgetCompromise::class,
                    'code'
                );

                $documentStatusAN = DocumentStatus::where('action', 'AN')->first();
                $documentStatusEl = DocumentStatus::where('action', 'EL')->first();
                $compromise = \Modules\Budget\Models\BudgetCompromise::where('document_number', $payroll->code)
                    ->get()
                    ->last();

                if ($compromise != null && $compromise->document_status_id != $documentStatusAN->id) {
                    \Modules\Budget\Models\BudgetCompromiseDetail::where('budget_compromise_id', $compromise->id)->delete();
                    \Modules\Budget\Models\BudgetStage::where('budget_compromise_id', $compromise->id)->delete();
                } else {
                    $compromise = \Modules\Budget\Models\BudgetCompromise::create([
                        'sourceable_id' => $payroll->id,
                        'document_number' => $payroll->code,
                        'institution_id' => $institution->id,
                        'compromised_at' => null,
                        'sourceable_type' => Payroll::class,
                        'description' => $payroll->name,
                        'code' => $codeCompromise,
                        'document_status_id' => $documentStatusEl->id,
                    ]);
                }

                $total = 0;
                foreach ($accountsA as $budgetAccount) {
                    $formulation = \Modules\Budget\Models\BudgetSubSpecificFormulation::where(
                        'budget_specific_action_id',
                        $budgetAccount['budget_specific_action_id']
                    )->first();

                    $compromise->budgetCompromiseDetails()->Create([
                        'description' => $budgetAccount['budget_specific_action_desc'],
                        'amount' => $budgetAccount['value'],
                        'tax_amount' => 0,
                        'tax_id' => null,
                        'budget_account_id' => $budgetAccount['budget_account_id'],
                        'budget_sub_specific_formulation_id' => $formulation->id,
                    ]);
                    $total += $budgetAccount['value'];

                    $budgetAccountOpen = \Modules\Budget\Models\BudgetAccountOpen::where(
                        'budget_sub_specific_formulation_id',
                        $formulation->id
                    )
                        ->where('budget_account_id', $budgetAccount['budget_account_id'])
                        ->first();

                    if ($budgetAccountOpen != null) {
                        $budgetAccountOpen->total_year_amount_m
                            = $budgetAccountOpen->total_year_amount_m - $budgetAccount['value'];
                        $budgetAccountOpen->save();
                    }
                }

                $compromise->budgetStages()->updateOrCreate([
                    'code' => generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code'),
                ], [
                    'registered_at' => now(),
                    'type' => 'PRE',
                    'amount' => $total
                ]);

                $countAP = 0;

                foreach ($accountsB as $budgetAccounts) {
                    $countAP++;
                    $codeSettingCompromise = CodeSetting::where(
                        "model",
                        \Modules\Budget\Models\BudgetCompromise::class
                    )->first();
                    $codeCompromise = generate_registration_code(
                        $codeSettingCompromise->format_prefix,
                        strlen($codeSettingCompromise->format_digits),
                        (strlen($codeSettingCompromise->format_year) == 2) ?
                            substr($currentFiscalYear->year, 2, 2) : $currentFiscalYear->year,
                        \Modules\Budget\Models\BudgetCompromise::class,
                        'code'
                    );
                    $compromise = \Modules\Budget\Models\BudgetCompromise::where('document_number', 'AP - ' . $countAP . $payroll->code)
                    ->get()
                    ->last();

                    if ($compromise != null && $compromise->document_status_id != $documentStatusAN->id) {
                        \Modules\Budget\Models\BudgetCompromiseDetail::where('budget_compromise_id', $compromise->id)->delete();
                        \Modules\Budget\Models\BudgetStage::where('budget_compromise_id', $compromise->id)->delete();
                    } else {
                        $compromise = \Modules\Budget\Models\BudgetCompromise::create([
                            'sourceable_id' => $payroll->id,
                            'document_number' => 'AP - ' . $countAP . $payroll->code,
                            'institution_id' => $institution->id,
                            'compromised_at' => null,
                            'sourceable_type' => Payroll::class,
                            'description' => $payroll->name,
                            'code' => $codeCompromise,
                            'document_status_id' => $documentStatusEl->id,
                            'compromiseable_id' => $budgetAccounts[0]['id'],
                            'compromiseable_type' => PayrollConcept::class
                        ]);
                    }
                    $total = 0;

                    foreach ($budgetAccounts as $budgetAccount) {
                        $source = Source::query()
                            ->where('sourceable_id', $compromise->id)
                            ->where('sourceable_type', \Modules\Budget\Models\BudgetCompromise::class)
                            ->first();

                        if ($source == null) {
                            Source::create(
                                [
                                    'receiver_id' => $budgetAccount['receiver']['id'],
                                    'sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class,
                                    'sourceable_id' => $compromise->id,
                                ]
                            );
                        }

                        $formulation = \Modules\Budget\Models\BudgetSubSpecificFormulation::query()
                            ->where('budget_specific_action_id', $budgetAccount['budget_specific_action_id'])
                            ->first();

                        $compromise->budgetCompromiseDetails()->Create([
                            'description' => $budgetAccount['budget_specific_action_desc'],
                            'amount' => $budgetAccount['value'],
                            'tax_amount' => 0,
                            'tax_id' => null,
                            'budget_account_id' => $budgetAccount['budget_account_id'],
                            'budget_sub_specific_formulation_id' => $formulation->id,
                        ]);

                        $budgetAccountOpen = \Modules\Budget\Models\BudgetAccountOpen::query()
                            ->where('budget_sub_specific_formulation_id', $formulation->id)
                            ->where('budget_account_id', $budgetAccount['budget_account_id'])
                            ->first();

                        if ($budgetAccountOpen != null) {
                            $budgetAccountOpen->total_year_amount_m
                                = $budgetAccountOpen->total_year_amount_m - $budgetAccount['value'];
                            $budgetAccountOpen->save();
                        }

                        $total += $budgetAccount['value'];
                    }

                    $compromise->budgetStages()->updateOrCreate([
                        'code' => generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code'),
                    ], [
                        'registered_at' => now(),
                        'type' => 'PRE',
                        'amount' => $total
                    ]);
                }

                $countD = 0;

                foreach ($accountsC as $budgetAccounts) {
                    $countD++;
                    $codeSettingCompromise = CodeSetting::where(
                        "model",
                        \Modules\Budget\Models\BudgetCompromise::class
                    )->first();
                    $codeCompromise = generate_registration_code(
                        $codeSettingCompromise->format_prefix,
                        strlen($codeSettingCompromise->format_digits),
                        (strlen($codeSettingCompromise->format_year) == 2) ?
                            substr($currentFiscalYear->year, 2, 2) : $currentFiscalYear->year,
                        \Modules\Budget\Models\BudgetCompromise::class,
                        'code'
                    );
                    $compromise = \Modules\Budget\Models\BudgetCompromise::where('document_number', 'DE - ' . $countD . $payroll->code)
                    ->get()
                    ->last();

                    if ($compromise != null && $compromise->document_status_id != $documentStatusAN->id) {
                        \Modules\Budget\Models\BudgetCompromiseDetail::where('budget_compromise_id', $compromise->id)->delete();
                        \Modules\Budget\Models\BudgetStage::where('budget_compromise_id', $compromise->id)->delete();
                    } else {
                        $compromise = \Modules\Budget\Models\BudgetCompromise::create([
                            'sourceable_id' => $payroll->id,
                            'document_number' => 'DE - ' . $countD . $payroll->code,
                            'institution_id' => $institution->id,
                            'compromised_at' => null,
                            'sourceable_type' => Payroll::class,
                            'description' => $payroll->name,
                            'code' => $codeCompromise,
                            'document_status_id' => $documentStatusEl->id,
                            'compromiseable_id' => $budgetAccounts[0]['id'],
                            'compromiseable_type' => PayrollConcept::class
                        ]);
                    }
                    $total = 0;

                    foreach ($budgetAccounts as $budgetAccount) {
                        $source = Source::query()
                            ->where('sourceable_id', $compromise->id)
                            ->where('sourceable_type', \Modules\Budget\Models\BudgetCompromise::class)
                            ->first();

                        if ($source == null) {
                            Source::create(
                                [
                                    'receiver_id' => $budgetAccount['receiver']['id'],
                                    'sourceable_type' => \Modules\Budget\Models\BudgetCompromise::class,
                                    'sourceable_id' => $compromise->id,
                                ]
                            );
                        }

                        $formulation = \Modules\Budget\Models\BudgetSubSpecificFormulation::query()
                            ->where('budget_specific_action_id', $budgetAccount['budget_specific_action_id'])
                            ->first();

                        $compromise->budgetCompromiseDetails()->Create([
                            'description' => $budgetAccount['budget_specific_action_desc'],
                            'amount' => $budgetAccount['value'],
                            'tax_amount' => 0,
                            'tax_id' => null,
                            'budget_account_id' => $budgetAccount['budget_account_id'],
                            'budget_sub_specific_formulation_id' => $formulation->id,
                        ]);

                        $budgetAccountOpen = \Modules\Budget\Models\BudgetAccountOpen::query()
                            ->where('budget_sub_specific_formulation_id', $formulation->id)
                            ->where('budget_account_id', $budgetAccount['budget_account_id'])
                            ->first();

                        if ($budgetAccountOpen != null) {
                            $budgetAccountOpen->total_year_amount_m
                                = $budgetAccountOpen->total_year_amount_m - $budgetAccount['value'];
                            $budgetAccountOpen->save();
                        }

                        $total += $budgetAccount['value'];
                    }

                    $compromise->budgetStages()->updateOrCreate([
                        'code' => generate_registration_code('STG', 8, 4, \Modules\Budget\Models\BudgetStage::class, 'code'),
                    ], [
                        'registered_at' => now(),
                        'type' => 'PRE',
                        'amount' => $total
                    ]);
                }
            }
            return $request->session()->flash('message', ['type' => 'store']);
        });
    }

    /**
     * Aprobar la nómina
     *
     * @author    Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request  $request Datos de la petición
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function approved(Request $request, $id)
    {
        try {
            $payroll = Payroll::find($id);
            /** @todo Se valida la información de las cuentas asociadas */
            $this->payrollValidateAccounts($payroll);

            $payrollPaymentPeriod = $payroll->payrollPaymentPeriod;
            $payrollPaymentPeriod->payment_status = 'approved';
            $payrollPaymentPeriod->save();
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
            return response()->json(['redirect' => route('payroll.registers.index')], 200);
        }

        $request->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Nómina aprobada correctamente',
            'icon' => 'screen-ok',
            'class' => 'growl-success'
        ]);

        return response()->json(['redirect' => route('payroll.registers.index')], 200);
    }

    /**
     * Realiza las validaciones a la información de las cuentas contables asociadas al registro de nómina
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     Payroll   $model    Registro de nómina
     *
     * @return    array    Array con la informacion de los registros post validación
     */
    public function payrollValidateAccounts(Payroll $model)
    {
        $number_decimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();
        $round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
        $nameDecimalFunction = $round->p_value == 'false' ? 'currency_format' : 'round';

        $records = $model->payrollStaffPayrolls()
            ->select('concept_type', 'payroll_staff_id')
            ->get()
            ->map(function ($record) {
                return [
                    'payroll_staff' => [
                        'id' => $record->payrollStaff->id,
                        'name' => $record->payrollStaff->fullName,
                    ],
                    'concept_type' => $record->concept_type,
                ];
            })
            ->toArray();

        $totals = [
            '+' => [],
            '-' => [],
            'NA' => [],
        ];

        // Iterar sobre la lista de trabajadores
        foreach ($records as $concept) {
            // Iterar sobre los conceptos
            foreach ($concept['concept_type'] as $type => $values) {
                // Iterar sobre los tipos de conceptos y acumular el valor correspondiente
                foreach ($values as $value) {
                    $concept = PayrollConcept::where('name', $value['name'])->first();
                    $receiver = Receiver::query()
                        ->with('sources')
                        ->whereHas('sources', function ($query) use ($concept) {
                            $query
                                ->where('sourceable_id', $concept->id)
                                ->where('sourceable_type', PayrollConcept::class);
                        })
                        ->first();

                    if ($value['sign'] != 'NA') {
                        if (!isset($totals[$value['sign']][$value['name']])) {
                            $totals[$value['sign']][$value['name']]['id'] = $concept->id;
                            $totals[$value['sign']][$value['name']]['pay_order'] = $concept->pay_order;
                            $totals[$value['sign']][$value['name']]['value'] = 0;
                            $totals[$value['sign']][$value['name']]['accounting_account_id'] = $concept->accounting_account_id
                                ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una cuenta contable asociada');
                            $totals[$value['sign']][$value['name']]['budget_account_id'] = ($value['sign'] != '-')
                                ? $concept->budget_account_id
                                ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una cuenta presupuestaria asociada')
                                : null;
                            $totals[$value['sign']][$value['name']]['budget_specific_action_id'] = ($value['sign'] != '-')
                                ? $concept->budget_specific_action_id
                                ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una acción específica asociada')
                                : null;
                            $totals[$value['sign']][$value['name']]['receiver'] = $receiver ?? null;
                        }

                        $totals[$value['sign']][$value['name']]['value'] += ($value['sign'] == '+')
                            ? $nameDecimalFunction($value['value'], $number_decimals->p_value)
                            : $nameDecimalFunction($value['value'], $number_decimals->p_value);
                    } elseif ($value['sign'] == 'NA' && $receiver != null) {
                        if (!isset($totals['NA'][$receiver->description][$value['name']])) {
                            $totals['NA'][$receiver->description][$value['name']] = [
                                'id' => $concept->id,
                                'value' => 0,
                                'valueTotal' => 0,
                                'accounting_account_id' => $concept->accounting_account_id
                                    ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una cuenta contable asociada'),
                                'budget_account_id' => $concept->budget_account_id
                                    ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una cuenta presupuestaria asociada'),
                                'budget_specific_action_id' => $concept->budget_specific_action_id
                                    ?? throw new \Exception('El concepto ' . $value['name'] . ' no tiene una acción específica asociada'),
                                'receiver' => $receiver,
                            ];
                        }

                        $totals['NA'][$receiver->description][$value['name']]['value'] +=
                            $nameDecimalFunction($value['value'], $number_decimals->p_value);
                        $totals['NA'][$receiver->description][$value['name']]['valueTotal'] +=
                            $nameDecimalFunction($value['value'], $number_decimals->p_value);
                    }
                }
            }
        }

        $accountingAccountsA = [];
        $accountingAccountsD = [];

        $totalDebit = 0;
        $totalAsset = 0;

        $totalDeduction = 0;
        $deductionToPayOrder = [];

        foreach ($totals['+'] ?? [] as $value) {
            $totalDebit += $nameDecimalFunction($value['value'], $number_decimals->p_value);
            array_push($accountingAccountsA, [
                'id' => $value['accounting_account_id'],
                'debit' => $nameDecimalFunction($value['value'], $number_decimals->p_value),
                'assets' => 0,
            ]);
        }

        foreach ($totals['-'] ?? [] as $value) {
            $value['sum'] = true;
            $totalAsset += $nameDecimalFunction($value['value'], $number_decimals->p_value);
            array_push($accountingAccountsD, [
                'id' => $value['accounting_account_id'],
                'debit' => 0,
                'assets' => $nameDecimalFunction($value['value'], $number_decimals->p_value),
            ]);

            if ($value['receiver'] != null && $value['pay_order'] == true) {
                $compromiseToDeduction = (
                    Module::has('Budget') && Module::isEnabled('Budget')
                ) ? \Modules\Budget\Models\BudgetCompromise::query()
                    ->where('sourceable_type', Payroll::class)
                    ->where('sourceable_id', $model->id)
                    ->where('compromiseable_type', PayrollConcept::class)
                    ->where('compromiseable_id', $value['id'])
                    ->first() : null;

                if ($compromiseToDeduction) {
                    $value['sum'] = false;

                    $accountingAccountsToOrderD = [];
                    $totalAmountCompromiseDeduction = 0;
                    foreach ($compromiseToDeduction->budgetCompromiseDetails as $compromiseDetail) {
                        $accountable = \Modules\Accounting\Models\Accountable::query()
                            ->where('accountable_type', \Modules\Accounting\Models\BudgetAccount::class)
                            ->where('accountable_id', $compromiseDetail->budget_account_id)
                            ->first();

                        $accountingAccountsToOrderD[] = [
                            'id' => $accountable->accounting_account_id,
                            'debit' => $compromiseDetail->amount,
                            'assets' => 0,
                        ];

                        $totalAmountCompromiseDeduction += $compromiseDetail->amount;
                    }

                    $accountingAccountsToOrderD = array_merge(
                        $accountingAccountsToOrderD,
                        [
                            [
                                'id' => (int)$value['receiver']['associateable_id'],
                                'debit' => 0,
                                'assets' => (string)$totalAmountCompromiseDeduction,
                            ]
                        ]
                    );

                    array_push($deductionToPayOrder, [
                        'accounts' => $accountingAccountsToOrderD,
                        'executePaymentAccounts' => [
                            [
                                'id' => $model->payrollPaymentPeriod->payrollPaymentType->financeBankAccount->accounting_account_id,
                                'debit' => 0,
                                'assets' => $nameDecimalFunction($value['value'], $number_decimals->p_value),
                            ],
                            [
                                'id' => $value['receiver']['associateable_id'],
                                'debit' => $nameDecimalFunction($value['value'], $number_decimals->p_value),
                                'assets' => 0,
                            ]
                        ],
                        'amount' => $totalAmountCompromiseDeduction,
                        'receiver_id' => $value['receiver']['id'],
                        'compromise_id' => $compromiseToDeduction->id
                    ]);
                }
            } elseif ($value['receiver'] != null && $value['pay_order'] == false) {
                $totalDeduction += $nameDecimalFunction($value['value'], $number_decimals->p_value);
            }
        }

        $newAccountingAccountsA = array_merge([], $accountingAccountsA);
        $newAccountingAccountsD = array_merge([], $accountingAccountsD);
        $newTotalAsset = 0;
        $newTotalDebit = 0;

        // Recorrer ambos arrays y actualizar registros en el primer array si hay coincidencias de ID
        foreach ($newAccountingAccountsA as &$record1) {
            foreach ($newAccountingAccountsD as $key => $record2) {
                if ($record1['id'] == $record2['id']) {
                    $record1['debit'] = floatval($record1['debit']) - floatval($record2['assets']);
                    $newTotalAsset += $nameDecimalFunction($record2['assets'], $number_decimals->p_value);
                    unset($newAccountingAccountsD[$key]);
                }
            }
            $newTotalDebit += $nameDecimalFunction($record1['debit'], $number_decimals->p_value);
        }
        if ($totalAsset != $newTotalAsset) {
            throw new \Exception('Error, Existen conceptos de deducción cuya cuenta no coincide con ningún concepto de asignación establecido para este periodo de nómina.');
        }
        $totalDebit = $newTotalDebit;
        $totalAsset = $newTotalAsset;
        $accountingAccountsA = $newAccountingAccountsA;

        $idInstitutionAccount = Parameter::where('p_key', 'institution_account')->first();

        if (is_null($idInstitutionAccount) && Module::has('Accounting') && Module::isEnabled('Accounting')) {
            throw new \Exception('Debe configurar la cuenta contable de la insitución para poder continuar');
        }

        return [
            'idInstitutionAccount' => $idInstitutionAccount,
            'accountingAccountsA' => $accountingAccountsA,
            'totalDebit' => $totalDebit,
            'totalAsset' => $totalAsset,
            'totalDeduction' => $totalDeduction,
            'totals' => $totals,
            'model' => $model,
            'nameDecimalFunction' => $nameDecimalFunction,
            'number_decimals' => $number_decimals,
            'deductionToPayOrder' => $deductionToPayOrder,
        ];
    }
}
