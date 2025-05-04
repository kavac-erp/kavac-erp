<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\CodeSetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Models\BudgetCompromise;
use Modules\Purchase\Models\BudgetCompromiseDetail;
use Modules\Purchase\Models\BudgetStage;
use Modules\Purchase\Models\FiscalYear;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Modules\Purchase\Models\PurchaseCompromise;
use Modules\Purchase\Models\PurchaseCompromiseDetail;
use Modules\Purchase\Models\PurchaseStage;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Http\Resources\PurchaseBudgetAvailabilityResource;
use Modules\Purchase\Http\Resources\PurchaseBudgetAvailabilityPayrollResource;

class PurchaseBudgetaryAvailabilityController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */

        $this->middleware(['role:admin|purchase|budget']);
        $this->middleware('permission:purchase.availability.request', ['only' => ['requestAvailability']]);
    }

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        $mergeRecords = null;
        $purchaseRecords = PurchaseBaseBudget::with(['currency', 'relatable' =>
            function ($query) {
                $query->with(['purchaseRequirementItem' => function ($query) {
                    $query->with('purchaseRequirement')->get();
                }])->get();
            },
        ])
        ->where('send_notify', true)
        ->orderBy('id', 'ASC')->get();

        $formatedPurchaseRecords = PurchaseBudgetAvailabilityResource::collection($purchaseRecords);

        if (Module::has('Payroll') && Module::isEnabled('Payroll') && Module::has('Budget') && Module::isEnabled('Budget')) {
            $payrollRecords = \Modules\Payroll\Models\Payroll::with(['payrollPaymentPeriod.payrollPaymentType.payrollConcepts'])
                ->whereHas('payrollPaymentPeriod', function ($query) {
                    $query->where('availability_status', 'send')
                        ->orWhere('availability_status', 'available')
                        ->orWhere('availability_status', 'not_available')
                        ->orWhere('availability_status', 'AN');
                })
                ->get();
            $formatedPayrollRecords = PurchaseBudgetAvailabilityPayrollResource::collection($payrollRecords);
            $mergeRecords = array_merge($formatedPurchaseRecords->toArray($formatedPurchaseRecords), $formatedPayrollRecords->toArray($formatedPayrollRecords) ?? []);
        }

        return view('purchase::budgetary_availability.index', [
            'records' => $mergeRecords != null ? json_encode($mergeRecords) : json_encode($formatedPurchaseRecords),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
            'description' => 'required',
            'date' => 'required',
            ],
            [
            'description' => 'El campo descripción es un campo obligatorio.',
            'date' => 'El campo fecha es un campo obligatorio.',
            ]
        );

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $model_compromise = BudgetCompromise::class;
            $model_compromise_detail = BudgetCompromiseDetail::class;
            $model_state = BudgetStage::class;
        } else {
            $model_compromise = PurchaseCompromise::class;
            $model_compromise_detail = PurchaseCompromiseDetail::class;
            $model_state = PurchaseStage::class;
        }
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        $model = PurchaseBudgetaryAvailability::class;
        if ($request->availability == 1) {
            $model::where('purchase_base_budgets_id', $request->id)->delete();
            foreach ($request->accounts as $accounts) {
                $model::create([
                    'item_code' => $accounts['code'],
                    'description' => $request->description,
                    'date' => $request->date,
                    'spac_description' => $accounts['spac_description'],
                    'budget_account_id' => $accounts['account_id'],
                    'budget_specific_action_id' => $accounts['specific_action_id'],
                    'item_name' => $accounts['description'],
                    'amount' => $accounts["amount"],
                    'availability' => $request->availability,
                    'purchase_base_budgets_id' => $request->id,
                ]);
            }
        } else {
            $model::create([
                'item_code' => "none",
                'description' => $request->description,
                'date' => $request->date,
                'spac_description' => "none",
                'budget_account_id' => "none",
                'budget_specific_action_id' => "none",
                'item_name' => "none",
                'amount' => "none",
                'availability' => $request->availability,
                'purchase_base_budgets_id' => $request->id,
            ]);
        }
        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Show the specified resource.
     * @return Renderable
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit($id)
    {
        $purchase_quotation = PurchaseBaseBudget::with([
            'currency',
            'tax',
            'tax.histories',
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'relatable' => function ($query) {
                $query->with(['purchaseRequirementItem' => function ($query) {
                    $query->with(['purchaseRequirement' => function ($query) {
                        $query->with('userDepartment')->get();
                        //$query->with('purchaseRequirementItems')->get();
                        $query->with(['purchaseBaseBudget' => function ($query) {
                            $query->with('currency')->get();
                            $query->with('purchaseRequirement.contratingDepartment')->get();
                            $query->with('purchaseRequirement.userDepartment')->get();
                            $query->with('relatable.purchaseRequirementItem.purchaseRequirement')->get();
                        }])->get();
                    }])->get();
                }])->get();
            },
        ])->orderBy('id', 'ASC')->find($id);

        if (!$purchase_quotation) {
            return view('errors.404');
        }
        $currency = $purchase_quotation->currency;
        $supplier = "noodles";
        $record_items = [];

        /**
         * [$has_budget determina si esta instalado el modulo Budget]
         * @var [boolean]
         */
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        if ($has_budget) {
            $budget_items = template_choices(
                'Modules\Budget\Models\BudgetAccount',
                ['code', '-', 'denomination'],
                [],
                true
            );
            $specific_actions = template_choices(
                'Modules\Budget\Models\BudgetSpecificAction',
                ['code', '-', 'name'],
                [],
                true
            );
            return view('purchase::budgetary_availability.form', [
                'has_budget' => $has_budget,
                'record_items' => $purchase_quotation,
                'currency' => $currency,
                'budget_items' => json_encode($budget_items),
                'specific_actions' => json_encode($specific_actions),
            ]);
        } else {
            return view('purchase::budgetary_availability.form', [
                'record_items' => $purchase_quotation,
                'currency' => $currency,
                'supplier' => $supplier,
                'budget_items' => json_encode([[
                    'id' => '',
                    'text' => 'Seleccione...',
                ]]),
                'specific_actions' => json_encode([[
                    'id' => '',
                    'text' => 'Seleccione...',
                ]]),
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Renderable
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Renderable
     */
    public function destroy($id)
    {
        $availability = PurchaseBudgetaryAvailability::where('purchase_quotation_id', $id)->delete();
    }

    public function getBudgetAvailable($specific_action_id, $account_id)
    {
        return response()->json([
            'amount' => budget_available(
                FiscalYear::where('active', true)->first(),
                $specific_action_id,
                $account_id
            ),
        ], 200);
    }

    /**
     * [generateCodeAvailable genera el código disponible]
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @return string|null [código que se asignara]
     */
    public function generateCodeAvailable($table)
    {
        $codeSetting = CodeSetting::where('table', $table)
            ->first();

        if (!$codeSetting) {
            $codeSetting = CodeSetting::where('table', $table)
                ->first();
        }

        if ($codeSetting) {
            $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

            $code = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                    substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                    $currentFiscalYear->year : date('Y')),
                PurchaseBudgetaryAvailability::class,
                $codeSetting->field
            );
        } else {
            $code = null;
        }
        return $code;
    }
}
