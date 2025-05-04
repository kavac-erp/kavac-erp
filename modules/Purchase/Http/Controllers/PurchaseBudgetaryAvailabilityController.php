<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Rules\DateBeforeFiscalYear;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Models\FiscalYear;
use Modules\Purchase\Models\BudgetStage;
use Modules\Purchase\Models\PurchaseStage;
use Illuminate\Contracts\Support\Renderable;
use Modules\Purchase\Models\BudgetCompromise;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchaseCompromise;
use Modules\Purchase\Models\BudgetCompromiseDetail;
use Modules\Purchase\Models\PurchaseCompromiseDetail;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Log;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Modules\Purchase\Http\Resources\PurchaseBudgetAvailabilityResource;
use Modules\Purchase\Http\Resources\PurchaseBudgetAvailabilityPayrollResource;
use Modules\Purchase\Models\PurchaseStates;

/**
 * @class PurchaseBudgetaryAvailabilityController
 * @brief Controlador para la gestión de la disponibilidad presupuestaria
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBudgetaryAvailabilityController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador

        $this->middleware(['role:admin|purchase|budget']);
        $this->middleware('permission:purchase.availability.request', ['only' => ['requestAvailability']]);
    }

    /**
     * Listado de disponibilidades presupuestarias
     *
     * @return \Illuminate\View\View
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
                        ->orWhere('availability_status', 'AP')
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
     * Muestra el formulario para crear un nuevo registro de disponibilidad presupuestaria
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo registro de disponibilidad presupuestaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $relatables = $request->input('relatable');
        $date = null;

        // Get the date of the purchase requirement item if exists
        foreach ($relatables as $relateble) {
            if ($relateble['purchase_requirement_item']) {
                $date = $relateble['purchase_requirement_item']['purchase_requirement']['date'];
                break;
            }
        }

        $this->validate(
            $request,
            [
                'description' => 'required',
                'date' => ['required', new DateBeforeFiscalYear('Fecha'), 'after_or_equal:' . $date],
            ],
            [
                'description.required' => 'El campo descripción es obligatorio.',
                'date.required' => 'El campo fecha es obligatorio.',
                'date.after_or_equal' => 'El campo fecha debe ser igual o posterior a la fecha del presupuesto base.' . ' ' . $date,
            ]
        );

        if (Module::has('Budget') && Module::isEnabled('Budget')) {
            $model_compromise = BudgetCompromise::class;
            $model_compromise_detail = BudgetCompromiseDetail::class;
            $model_state = BudgetStage::class;
        } else {
            $model_compromise = PurchaseCompromise::class;
            $model_compromise_detail = PurchaseCompromiseDetail::class;
            $model_state = PurchaseStates::class;
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
        }

        if ($request->documentFiles) {
            // Elimina cualquier documento previamente cargado a la disponibilidad presupuestaria
            Document::where(['documentable_type' => PurchaseBudgetaryAvailability::class, 'documentable_id' => $request->id])->delete();
            //Verifica si tiene documentos para establecer la relación
            foreach ($request->documentFiles as $file) {
                $doc = Document::find($file);
                $doc->documentable_id = $request->id;
                $doc->documentable_type = PurchaseBudgetaryAvailability::class;
                $doc->save();
            }
        }

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Muestra información de una disponibilidad presupuestaria
     *
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Muestra el formulario para editar una disponibilidad presupuestaria
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $document_file = Document::where(['documentable_type' => PurchaseBudgetaryAvailability::class, 'documentable_id' => $id])->get();
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

        /* determina si esta instalado el modulo Budget */
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
                'document_file' => $document_file,
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
     * Actualiza la disponibilidad presupuestaria
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina una disponibilidad presupuestaria
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $availability = PurchaseBudgetaryAvailability::where('purchase_quotation_id', $id)->delete();

        return response()->json(['message' => 'success'], 200);
    }

    /**
     * Obtiene la disponibilidad presupuestaria
     *
     * @param integer $specific_action_id ID de la acción específica
     * @param integer $account_id ID de la cuenta presupuestaria
     * @return \Illuminate\Http\JsonResponse
     */
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
     * Genera el código disponible a ser asignado
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string|null
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

    /**
     * Aprobar disponibilida presupuestaria
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function approveBudgetaryAvailability(Request $request)
    {
        try {
            if ($request->module == 'Purchase') {
                $PurchaseBaseBudget = PurchaseBaseBudget::query()->where('id', $request->id)->firstOrFail();
                $BudgetaryAvailable = PurchaseBudgetaryAvailability::query()
                ->where('purchase_base_budgets_id', $PurchaseBaseBudget->id)->get();

                if ($PurchaseBaseBudget->availability == 'Disponible') {
                    DB::transaction(
                        function () use ($BudgetaryAvailable, $PurchaseBaseBudget, $request) {
                            foreach ($BudgetaryAvailable as $budgetaryAvailable) {
                                //cambiar el estatus a aprobado
                                $budgetaryAvailable['availability'] = 2;
                                $budgetaryAvailable->save();
                            }
                        }
                    );
                    return response()->json(['message' => 'Success'], 200);
                } elseif ($PurchaseBaseBudget->availability == 'AP') {
                    new \Exception('Esta disponibilidad presupuestaria ya fue aprobada');
                } elseif ($PurchaseBaseBudget->availability == 'No_Disponible') {
                    new \Exception("Esta disponibilidad presupuestaria no puede ser aprobada. Su estatus es 'No Disponible'.");
                } else {
                    new \Exception('Esta disponibilidad presupuestaria no puede ser aprobada, debido a  que su estatus es: ' . $PurchaseBaseBudget->available);
                }
            } elseif ($request->module == 'Payroll' && Module::has('Payroll') && Module::isEnabled('Payroll')) {
                $BudgetaryAvailable = \Modules\Payroll\Models\Payroll::query()
                ->where('id', $request->id)->firstOrFail();
                $payrollPaymentPeriod = $BudgetaryAvailable->payrollPaymentPeriod;

                if ($payrollPaymentPeriod) {
                    if ($payrollPaymentPeriod->availability_status == 'AP') {
                        new \Exception('Esta disponibilidad presupuestaria ya fue aprobada');
                    } elseif ($payrollPaymentPeriod->availability_status == 'available') {
                        DB::transaction(
                            function () use ($BudgetaryAvailable) {
                                //cambiar el estatus a aprobado
                                $BudgetaryAvailable->payrollPaymentPeriod->availability_status = 'AP';
                                $BudgetaryAvailable->payrollPaymentPeriod->save();
                            }
                        );
                        return response()->json(['message' => 'Success'], 200);
                    }
                } else {
                    new \Exception('Esta disponibilidad presupuestaria no puede ser aprobada');
                }
            }
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
                ['message' => [
                    'type' => 'other',
                    'title' => 'Alerta',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger',
                    'text' => 'No se pudo completar la operación. ' . ucfirst($errorMessage)
                    ]
                ],
                500
            );
        }
    }
}
