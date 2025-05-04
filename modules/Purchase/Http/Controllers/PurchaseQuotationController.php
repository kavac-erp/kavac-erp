<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\Profile;
use App\Repositories\UploadDocRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Models\Document;
use Modules\Purchase\Models\HistoryTax;
use Modules\Purchase\Models\Pivot;
use Modules\Purchase\Models\PurchaseBaseBudget;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchaseRequirementItem;
use Modules\Purchase\Models\TaxUnit;

/**
 * @class PurchaseQuotationController
 * @brief Gestiona los procesos de las cotizaciónes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseQuotationController extends Controller
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
        $this->middleware('permission:purchase.quotations.list', [
            'only' => 'index', 'vueList'
        ]);
        $this->middleware('permission:purchase.quotations.create', [
            'only' => ['create', 'store']
        ]);
        $this->middleware('permission:purchase.quotations.edit', [
            'only' => ['edit', 'update']
        ]);
        $this->middleware('permission:purchase.quotations.delete', [
            'only' => 'destroy'
        ]);
    }

    /**
     * Muestra vista de principal de cotización
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        $user_profile = Profile::with('institution')
            ->where('user_id', auth()->user()->id)->first();

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            if ($user_profile && $user_profile->institution !== null) {
                foreach (
                    \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff', 'profile')
                    ->whereHas('profile', function ($query) use ($user_profile) {
                        $query->where('institution_id', $user_profile->institution_id);
                    })->get() as $key => $employment
                ) {
                    $text = '';
                    if ($employment->payrollStaff !== null) {
                        if ($employment->payrollStaff->id_number) {
                            $text = $employment->payrollStaff->id_number . ' - ' .
                            $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                        } else {
                            $text = $employment->payrollStaff->passport . ' - ' .
                            $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                        }
                        array_push($employments, [
                            'id' => $employment->id,
                            'text' => $text,
                        ]);
                    }
                }
            } else {
                foreach (\Modules\Payroll\Models\PayrollEmployment::with('payrollStaff')->get() as $key => $employment) {
                    $text = '';
                    if ($employment->payrollStaff !== null) {
                        if ($employment->payrollStaff->id_number) {
                            $text = $employment->payrollStaff->id_number . ' - ' .
                            $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                        } else {
                            $text = $employment->payrollStaff->passport . ' - ' .
                            $employment->payrollStaff->first_name . ' ' . $employment->payrollStaff->last_name;
                        }
                        array_push($employments, [
                            'id' => $employment->id,
                            'text' => $text,
                        ]);
                    }
                }
            }
        }

        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));

        return view('purchase::quotation.index', [
            'has_budget' => $has_budget ,'employments' => json_encode($employments),
            'records' => PurchaseQuotation::with(['purchaseSupplier', 'currency', 'relatable' =>
                function ($query) {
                    $query->with(['purchaseRequirementItem' => function ($query) {
                        $query->with('purchaseRequirement')->get();
                    }])->get();
                },
            ])->orderBy('id', 'ASC')->get(),
        ]);
    }

    /**
     * Muestra vista de formulario de cotización
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $suppliers = template_choices(
            'Modules\Purchase\Models\PurchaseSupplier',
            [
                'rif', '-', 'name'
            ],
            [],
            true
        );

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))
            ->orderBy('operation_date', 'DESC')
            ->first();
        $taxUnit = TaxUnit::where('active', true)->first();
        $record_base_budgets = PurchaseBaseBudget::with(
            'currency',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.historyTax',
        )
        ->where('status', 'WAIT_QUOTATION')
        ->orWhere('status', 'PARTIALLY_QUOTED')
        ->orderBy('id', 'ASC')
        ->get();

        return view('purchase::quotation.form', [
            'record_base_budgets' => $record_base_budgets,
            // 'currencies' => json_encode($currencies),
            'tax' => json_encode($historyTax),
            'tax_unit' => json_encode($taxUnit),
            'suppliers' => json_encode($suppliers),
        ]);
    }

    /**
     * Metodo para registrar información de cotización
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'purchase_supplier_id' => 'required|integer',
                'currency_id' => 'required|integer',
                'file_1' => 'mimes:pdf',
                'file_2' => 'mimes:pdf',
                'file_3' => 'mimes:pdf',
                'base_budget_list' => 'required',
                'date' => 'required',
            ],
            [
                'file_1.required' => 'El archivo de acta de inicio es obligatorio.',
                'file_1.mimes' => 'El archivo de acta de inicio debe ser de tipo pdf.',
                'file_2.required' => 'El archivo de invitación de las empresas es obligatorio.',
                'file_2.mimes' => 'El archivo de invitación de las empresas debe ser de tipo pdf.',
                'file_3.required' => 'El archivo de proforma / Cotización es obligatorio.',
                'file_3.mimes' => 'El archivo de proforma / Cotización debe ser de tipo pdf.',
                'purchase_supplier_id.required' => 'El campo proveedor es obligatorio.',
                'purchase_supplier_id.integer' => 'El campo proveedor debe ser numerico.',
                'currency_id.required' => 'El campo de tipo de moneda es obligatorio.',
                'currency_id.integer' => 'El campo de tipo de moneda debe ser numerico.',
                'base_budget_list' => 'Debe seleccionar al menos un requerimiento.',
                'date.required' => 'La fecha de generación es obligatoria.',
            ]
        );

        $code = $this->generateCodeAvailable();

        $purchase_quotation = PurchaseQuotation::create([
            'code' => $code,
            'status' => 'QUOTED',
            'purchase_supplier_id' => $request->purchase_supplier_id,
            'currency_id' => $request->currency_id,
            'subtotal' => $request->total,
            'date' => $request->date,
        ]);

        $names_file = ['file_1', 'file_2', 'file_3'];
        if ($request->hasFile('file_1')) {
            $document = new UploadDocRepository();

            $name = $request['file_1']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();

            $document->uploadDoc(
                $request['file_1'],
                'documents',
                PurchaseQuotation::class,
                $purchase_quotation->id,
                null
            );
        }

        if ($request->hasFile('file_2')) {
            $document = new UploadDocRepository();
            $name = $request['file_2']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();
            $document->uploadDoc(
                $request['file_2'],
                'documents',
                PurchaseQuotation::class,
                $purchase_quotation->id,
                null
            );
        }

        if ($request->hasFile('file_3')) {
            $document = new UploadDocRepository();
            $name = $request['file_3']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();
            $document->uploadDoc(
                $request['file_3'],
                'documents',
                PurchaseQuotation::class,
                $purchase_quotation->id,
                null
            );
        }

        foreach (json_decode($request['base_budget_list'], true) as $record) {
            $base_budget = PurchaseBaseBudget::with('purchaseRequirement')->find($record['id']);
            $base_budget->status = $record['status'];
            $base_budget->save();

            Pivot::create([
                'relatable_type' => PurchaseBaseBudget::class,
                'relatable_id' => $base_budget->id,
                'recordable_type' => PurchaseQuotation::class,
                'recordable_id' => $purchase_quotation->id,
            ]);

            foreach ($record['relatable'] as $item) {
                $asd = PurchasePivotModelsToRequirementItem::create([
                    'purchase_requirement_item_id' => $item['purchase_requirement_item_id'],
                    'relatable_type' => PurchaseQuotation::class,
                    'relatable_id' => $purchase_quotation->id,
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                ]);
            }
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Metodo que muestra información de cotización
     *
     * @param  Request $id ID de la cotización
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $records = PurchaseQuotation::with(
            'purchaseSupplier',
            'currency',
            'documents',
            'relatable.purchaseRequirementItem.purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement.contratingDepartment',
            'relatable.purchaseRequirementItem.historyTax'
        )->find($id);

        $records['base_budget'] = Pivot::where('recordable_type', PurchaseQuotation::class)
            ->where('recordable_id', $id)
            ->with('relatable')
            ->get();
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra el formulario para editar una cotización
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $suppliers = template_choices('Modules\Purchase\Models\PurchaseSupplier', ['rif', '-', 'name'], [], true);

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))->orderBy('operation_date', 'DESC')->first();
        $taxUnit = TaxUnit::where('active', true)->first();
        $record_edit = PurchaseQuotation::with([
            'purchaseSupplier',
            'currency',
            'documents',
            'pivotRecordable' => function ($q) {
                $q->with(['relatable' => function ($query) {
                }])->get();
            },
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
                            $query->with('relatable.purchaseRequirementItem.historyTax')->get();
                        }])->get();
                    }])->get();
                }])->get();
            },
        ])->find($id);

        $record_base_budgets = PurchaseBaseBudget::with(
            'currency',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.historyTax'
        )->where('status', 'WAIT_QUOTATION')->orWhere('status', 'PARTIALLY_QUOTED')->orderBy('id', 'ASC')->get()
        ->filter(function ($budget) {
            return $budget->status_aux === 'WAIT_QUOTATION' || $budget->status_aux === 'PARTIALLY_QUOTED';
        });

        foreach (json_decode($record_edit->pivotRecordable) as $record) {
            if ($record->relatable->status == "QUOTED") {
                $record_base_budgets_Quoted = PurchaseBaseBudget::with(
                    'currency',
                    'purchaseRequirement.contratingDepartment',
                    'purchaseRequirement.userDepartment',
                    'relatable.purchaseRequirementItem.purchaseRequirement',
                    'relatable.purchaseRequirementItem.historyTax'
                )->where('id', $record->relatable->id)->first();
                $record_base_budgets->push($record_base_budgets_Quoted);
            }
        };

        $base_budget_edit = PurchaseBaseBudget::with(
            'currency',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.historyTax'
        )->get();

        return view('purchase::quotation.form', [
            'record_base_budgets' => $record_base_budgets,
            'record_edit' => $record_edit,
            'base_budget_edit' => $base_budget_edit,
            'tax' => json_encode($historyTax),
            'tax_unit' => json_encode($taxUnit),
            'suppliers' => json_encode($suppliers),
        ]);
    }

    /**
     * Metodo para eliminar información de cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $record = PurchaseQuotation::with('relatable')->find($id);
        // En la llave relatable trae los items relacionados con la cotización
        $document = new UploadDocRepository();
        $docs = Document::where('documentable_type', PurchaseQuotation::class)
            ->where('documentable_id', $id)->get();
        foreach ($record['relatable'] as $relatable) {
            $requirementItem = PurchaseRequirementItem::with('purchaseRequirement')->where(
                'id',
                $relatable['purchase_requirement_item_id']
            )->first();
            $requirement = PurchaseRequirement::with(['purchaseBaseBudget', 'purchaseRequirementItems'])->where(
                'id',
                $requirementItem->purchase_requirement_id
            )->first();
            $purchaseBase = $requirement['purchaseBaseBudget'];
            if ($requirement && $purchaseBase && $purchaseBase['status'] == 'QUOTED') {
                $base_budget = PurchaseBaseBudget::find($requirement['purchaseBaseBudget']['id']);
                $base_budget->status = 'WAIT_QUOTATION';
                $base_budget->save();
            }
            if ($purchaseBase['status'] == 'PARTIALLY_QUOTED') {
                //  de la cotización de este item
                $dualQuoted = false;
                foreach ($requirement['purchaseRequirementItems'] as $value) {
                    $pivotItems = PurchasePivotModelsToRequirementItem::where([
                        "relatable_type" => "Modules\Purchase\Models\PurchaseQuotation",
                        "purchase_requirement_item_id" => $value["id"]
                    ])->first();
                    if (isset($pivotItems["relatable_id"])) {
                        if ($pivotItems["relatable_id"] != $id) {
                            $dualQuoted = true;
                        }
                    }
                }
                if ($dualQuoted) {
                    if ($requirement && $purchaseBase && $purchaseBase['status'] == 'PARTIALLY_QUOTED') {
                        $base_budget = PurchaseBaseBudget::find($purchaseBase['id']);
                        $base_budget->status = 'PARTIALLY_QUOTED';
                        $base_budget->save();
                    }
                } else {
                    if ($requirement && $purchaseBase && $purchaseBase['status'] == 'PARTIALLY_QUOTED') {
                        $base_budget = PurchaseBaseBudget::find($purchaseBase['id']);
                        $base_budget->status = 'WAIT_QUOTATION';
                        $base_budget->save();
                    }
                }
            }
            $pivot = PurchasePivotModelsToRequirementItem::find($relatable['id']);
            $pivot->delete();
        }

        foreach ($docs as $doc) {
            $document->deleteDoc($doc->file, 'documents');
            $doc->delete();
        }

        $pivotTable = Pivot::where(
            "recordable_type",
            "Modules\Purchase\Models\PurchaseQuotation"
        )
        ->where("recordable_id", $id)->delete();

        $record->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene listado de registros de cotizaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $records = PurchaseQuotation::query()
        ->with(['purchaseSupplier', 'currency', 'relatable' =>
                function ($query) {
                    $query->with(['purchaseRequirementItem' => function ($query) {
                        $query->with('purchaseRequirement')->get();
                    }])->get();
                },
            ])
        ->orderBy('id')
        ->search($request->query('query'))
        ->paginate($request->limit ?? 10);

        return response()->json([
            'data' => $records->items(),
            'count' => $records->total(),
        ], 200);
    }

    /**
     * Actualiza la informacón de la cotización
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePurchaseQuotation(PurchaseQuotation $id, Request $request)
    {
        $this->validate(
            $request,
            [
                'purchase_supplier_id' => 'required|integer',
                'currency_id' => 'required|integer',
                'file_1' => 'mimes:pdf',
                'file_2' => 'mimes:pdf',
                'file_3' => 'mimes:pdf',
                'base_budget_list' => 'required',
            ],
            [
                'file_1.required' => 'El archivo de acta de inicio es obligatorio.',
                'file_1.mimes' => 'El archivo de acta de inicio debe ser de tipo pdf.',
                'file_2.required' => 'El archivo de invitación de las empresas es obligatorio.',
                'file_2.mimes' => 'El archivo de invitación de las empresas debe ser de tipo pdf.',
                'file_3.required' => 'El archivo de proforma / Cotización es obligatorio.',
                'file_3.mimes' => 'El archivo de proforma / Cotización debe ser de tipo pdf.',
                'purchase_supplier_id.required' => 'El campo proveedor es obligatorio.',
                'purchase_supplier_id.integer' => 'El campo proveedor debe ser numerico.',
                'currency_id.required' => 'El campo de tipo de moneda es obligatorio.',
                'currency_id.integer' => 'El campo de tipo de moneda debe ser numerico.',
                'base_budget_list' => 'Debe seleccionar al menos un requerimiento.',
            ]
        );

        $id->purchase_supplier_id = $request->purchase_supplier_id;
        $id->currency_id = $request->currency_id;
        $id->subtotal = $request->subtotal;
        $id->date = $request->date;
        $id->save();

        $names_file = [
            'file_1',
            'file_2',
            'file_3'
        ];

        if ($request->hasFile('file_1')) {
            $document = new UploadDocRepository();
            $name = $request['file_1']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();
            $document->uploadDoc(
                $request['file_1'],
                'documents',
                PurchaseQuotation::class,
                $id->id,
                null
            );
        }

        if ($request->hasFile('file_2')) {
            $document = new UploadDocRepository();
            $name = $request['file_2']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();
            $document->uploadDoc(
                $request['file_2'],
                'documents',
                PurchaseQuotation::class,
                $id->id,
                null
            );
        }

        if ($request->hasFile('file_3')) {
            $document = new UploadDocRepository();
            $name = $request['file_3']->getClientOriginalName();
            $docs = Document::where('file', ($name))->get()->count();
            $document->uploadDoc(
                $request['file_3'],
                'documents',
                PurchaseQuotation::class,
                $id->id,
                null
            );
        }
        //borramos los registros antiguos
        //borramos los items previamente cotizados que en este momento fueran eliminados
        foreach (json_decode($request['list_to_delete']) as $record) {
            $requirement = PurchaseRequirement::with([
                'purchaseBaseBudget',
                'purchaseRequirementItems'
            ])
            ->where('id', $record->purchase_requirement->id)->first();
            $dualQuoted = false;
            $purchaseBase = $requirement['purchaseBaseBudget'];
            foreach ($requirement['purchaseRequirementItems'] as $value) {
                $pivotItems = PurchasePivotModelsToRequirementItem::where([
                    "relatable_type" => "Modules\Purchase\Models\PurchaseQuotation",
                    "purchase_requirement_item_id" => $value["id"]
                ])->first();
                if (isset($pivotItems["relatable_id"])) {
                    if ($pivotItems["relatable_id"] != $id->id) {
                        $dualQuoted = true;
                    }
                }
            }

            if ($record->status == 'QUOTED') {
                $base_budget = PurchaseBaseBudget::find($purchaseBase['id']);
                if ($dualQuoted) {
                    $base_budget->status = 'PARTIALLY_QUOTED';
                    $base_budget->save();
                } else {
                    $base_budget->status = 'WAIT_QUOTATION';
                    $base_budget->save();
                }
            }
            if ($record->status == 'PARTIALLY_QUOTED') {
                //  de la cotización de este item
                if ($dualQuoted) {
                } else {
                    if ($requirement && $purchaseBase && $purchaseBase['status'] == 'PARTIALLY_QUOTED') {
                        $base_budget = PurchaseBaseBudget::find($purchaseBase['id']);
                        $base_budget->status = 'WAIT_QUOTATION';
                        $base_budget->save();
                    }
                }
            }

            foreach ($record->relatable as $elemet) {
                //borramos items
                $pivot = PurchasePivotModelsToRequirementItem::where([
                    "relatable_type" => "Modules\Purchase\Models\PurchaseQuotation",
                    "relatable_id" => $id->id
                ])->where("purchase_requirement_item_id", $elemet->id)->delete();
                // borramos ingresos en la tabla pivot
            }
            $base = Pivot::where([
                "recordable_type" => "Modules\Purchase\Models\PurchaseQuotation",
                "recordable_id" => $id->id,
                "relatable_type" => "Modules\Purchase\Models\PurchaseBaseBudget",
                "relatable_id" => $record->id
            ])->delete();
        }

        $pit = PurchasePivotModelsToRequirementItem::where([
            "relatable_type" => "Modules\Purchase\Models\PurchaseQuotation",
            "relatable_id" => $id->id
        ])->delete();

        foreach (json_decode($request['base_budget_list'], true) as $record) {
            //cambiamos el estado del presupuestobase
            $base_budget = PurchaseBaseBudget::with('purchaseRequirement')->find($record['id']);
            // $base_budget->status = $record['status'];
            // $base_budget->save();
            $requirement = PurchaseRequirement::with([
                'purchaseBaseBudget',
                'purchaseRequirementItems'
            ])->where('id', $record['purchase_requirement']['id'])->first();

            $dualQuoted = false;
            $purchaseBase = $requirement['purchaseBaseBudget'];
            foreach ($requirement['purchaseRequirementItems'] as $value) {
                $pivotItems = PurchasePivotModelsToRequirementItem::where([
                    "relatable_type" => "Modules\Purchase\Models\PurchaseQuotation",
                    "purchase_requirement_item_id" => $value["id"]
                ])->first();
                if (isset($pivotItems["relatable_id"])) {
                    if ($pivotItems["relatable_id"] != $id->id) {
                        $dualQuoted = true;
                    }
                }
            }
            // si el estado del item era cotizado
            if ($base_budget->status == 'QUOTED') {
            }
            if ($base_budget->status == 'PARTIALLY_QUOTED') {
                //  de la cotización de este item
                if ($dualQuoted) {
                    $base_budget->status = $record['status'];
                    $base_budget->save();
                } else {
                    if ($requirement && $purchaseBase && $purchaseBase['status'] == 'PARTIALLY_QUOTED') {
                        $base_budget = PurchaseBaseBudget::find($requirement['purchaseBaseBudget']['id']);
                        $base_budget->status = 'WAIT_QUOTATION';
                        $base_budget->save();
                    }
                }
            }
            if ($base_budget->status == 'WAIT_QUOTATION') {
                $base_budget->status = $record['status'];
                $base_budget->save();
            }

            $newUser = Pivot::updateOrCreate([
                'relatable_type' => PurchaseBaseBudget::class,
                'relatable_id' => $base_budget->id,
                'recordable_type' => PurchaseQuotation::class,
                'recordable_id' => $id->id,
            ], [
                'deleted_at' => null,
            ]);

            //no se si tiene o no la misma cantidad de item anterior o si alguno es diferente
            //borramos los registros pasados y agreguemos nuevos

            foreach ($record['relatable'] as $item) {
                $asd = PurchasePivotModelsToRequirementItem::updateOrCreate([
                    'purchase_requirement_item_id' => $item['purchase_requirement_item_id'],
                    'relatable_type' => PurchaseQuotation::class,
                    'relatable_id' => $id->id,
                ], [
                    'unit_price' => $item['unit_price'],
                    'quantity' => $item['quantity'],
                    'deleted_at' => null,
                ]);
            }
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Genera el código disponible para la cotización
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string código que se asignara
     */
    public function generateCodeAvailable()
    {
        $codeSetting = CodeSetting::where('table', 'purchase_quotations')->first();

        if (!$codeSetting) {
            $codeSetting = CodeSetting::where('table', 'purchase_quotations')->first();
        }

        if ($codeSetting) {
            $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();
            $code  = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (
                    isset($currentFiscalYear) ? substr($currentFiscalYear->year, 2, 2) : date('y')
                ) : (
                    isset($currentFiscalYear) ? $currentFiscalYear->year : date('Y')
                ),
                PurchaseQuotation::class,
                $codeSetting->field
            );
        } else {
            $code = 'error al generar código de referencia';
        }
        return $code;
    }

    /**
     * Método para cambiar el estado de una cotización a aprobado
     *
     * @author Angelo Osorio <danielking.321 at gmail.com> | <adosorio at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeQuotationStatus(Request $request)
    {
        $quotation = PurchaseQuotation::with([
            'pivotRecordable.relatable.purchaseRequirement'
        ])->find($request->id);

        foreach ($quotation->pivotRecordable as $purcahse_base_buget) {
            $requirement_id = $purcahse_base_buget->relatable->purchaseRequirement->id;
            $requirement = PurchaseRequirement::find($requirement_id);
            //Se cambia el estado a 'Procesado' de los requrimuentos asociados a un presupuesto base
            //que a su vez están asociados a una cotización.
            $requirement->requirement_status = "PROCESSED";
            $requirement->save();
        }
        $documentStatus = "APPROVED";
        $quotation->status = $documentStatus;
        $quotation->save();
        return response()->json(['message' => 'Success'], 200);
    }
}
