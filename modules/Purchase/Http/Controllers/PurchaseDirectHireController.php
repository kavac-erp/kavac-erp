<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use App\Models\HistoryTax as ModelsHistoryTax;
use App\Models\Profile;
use App\Models\Receiver;
use App\Models\Tax;
use App\Repositories\UploadDocRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Purchase\Models\DocumentStatus;
use Modules\Purchase\Models\HistoryTax;
use Modules\Purchase\Models\Pivot;
use Modules\Purchase\Models\PurchaseDirectHire;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\PurchaseSupplier;
use Modules\Purchase\Models\PurchaseSupplierObject;
use Modules\Purchase\Models\TaxUnit;
use Nwidart\Modules\Facades\Module;
use App\Rules\DateBeforeFiscalYear;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @class PurchaseDirectHireController
 * @brief Clase para gestionar las contrationes directas
 *
 * Clase para gestionar las contrationes directas
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseDirectHireController extends Controller
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
        $this->middleware('permission:purchase.directhire.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:purchase.directhire.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase.directhire.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase.directhire.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra el listado de órdenes de compra
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('purchase::purchase_order.index', [
            'records' => PurchaseOrder::with(
                'purchaseSupplier',
                'currency',
                'relatable',
                'purchaseType'
            )
            ->orderBy('id', 'ASC')
            ->get(),
        ]);
    }

    /**
     * Muestra el formulario para crear una orden de compra
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     * @author Francisco Escala <fjescala@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        $user_profile = Profile::with('institution')
            ->where('user_id', auth()
            ->user()
            ->id)
            ->first();

        $suppliers = template_choices('Modules\Purchase\Models\PurchaseSupplier', [
            'rif', '-', 'name'
        ], [], true);

        $department_list = template_choices('App\Models\Department', 'name', [], true);

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))
            ->orderBy('operation_date', 'DESC')->first();

        $taxUnit = TaxUnit::where('active', true)->first();

        $purchase_supplier_objects = [];

        array_push(
            $purchase_supplier_objects,
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        );

        foreach (PurchaseSupplierObject::all() as $record) {
            $type = $record->type;
            if ($type == 'B') {
                $type = 'Bienes';
            } elseif ($type == 'O') {
                $type = 'Obras';
            } elseif ($type == 'S') {
                $type = 'Servivios';
            }
            array_push(
                $purchase_supplier_objects,
                [
                    'id' => $record->id,
                    'text' => $type . ' - ' . $record->name,
                ],
            );
        }

        $quotations = PurchaseQuotation::with(
            'currency',
            // Requirement
            'pivotRecordable.relatable.purchaseRequirement.contratingDepartment',
            'pivotRecordable.relatable.purchaseRequirement.userDepartment',
            // RequirementItems
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'relatable.purchaseRequirementItem.pivotPurchase',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            // BaseBudget
            'pivotRecordable.relatable.tax.histories',
        )->where(['orderable_id' => null, 'status' => 'APPROVED'])
            ->orderBy('id', 'ASC')->get();

        /* Se obtienen los datos laborales */
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

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
        return view('purchase::purchase_order.direct_hire_form', [
            'quotations' => $quotations,
            'tax' => json_encode($historyTax),
            'tax_unit' => json_encode($taxUnit),
            'department_list' => json_encode($department_list),
            'employments' => json_encode($employments),
            'purchase_supplier_objects' => json_encode($purchase_supplier_objects),
            'suppliers' => json_encode($suppliers),
        ]);
    }

    /**
     * Almacena un nuevo registro de contratación directa
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UploadDocRepository $upDoc)
    {
        $this->validate(
            $request,
            [
            'institution_id' => 'required|integer',
            'is_order' => 'required',
            'contracting_department_id' => 'required|integer',
            'user_department_id' => 'required|integer',
            'purchase_supplier_id' => 'required|integer',
            'purchase_supplier_object_id' => 'required|integer',
            'fiscal_year_id' => 'required|integer',
            'currency_id' => 'required|integer',
            'funding_source' => 'required',
            'description' => 'required',
            'quotation_list' => 'required',
            // Factura
            /*'receiver.invoice_to' => 'required',
            'receiver.send_to' => 'required',
            'receiver.rif' => 'required', */
            // Firmas
            'prepared_by_id' => 'required',
            'purchase_type_id' => 'required',
            'due_date' => 'required_unless:time_frame,"delivery"',
            'time_frame' => 'required',
            'date' => ['required', new DateBeforeFiscalYear('fecha de generación')],
            ],
            [
            'institution_id.required' => 'El campo institución es obligatorio',
            'contracting_department_id.required' => 'El campo unidad contratante es obligatorio',
            'user_department_id.required' => 'El campo unidad usuaria es obligatorio',
            'purchase_supplier_id.required' => 'El campo proveedor es obligatorio',
            'purchase_supplier_object_id.required' => 'El campo denominación del requerimiento es obligatorio',
            'fiscal_year_id.required' => 'El campo año de ejercicio económico es obligatorio',
            'currency_id.required' => 'El campo tipo de moneda es obligatorio',
            'funding_source.required' => 'El campo fuente de financiamiento es obligatorio',
            'description.required' => 'El campo denominación especifica del requerimiento es obligatorio',
            'quotation_list.required' => 'Debe seleccionar al menos un presupuesto base.',
            // Factura
            'receiver.invoice_to.required' => 'El campo facturar a de factura es obligatorio',
            'receiver.send_to.required' => 'El campo enviar a de factura es obligatorio',
            'receiver.rif.required' => 'El campo RIF de factura es obligatorio',
            // firmas
            'prepared_by_id.required' => 'El campo preparado por es obligatorio',
            'purchase_type_id.required' => 'El campo modalidad de compra es obligatorio',
            'due_date.required_unless' => 'El campo plazo de entrega es obligatorio',
            'time_frame.required' => 'El campo período es obligatorio',
            'is_order.required' => 'El campo ¿es una orden de compra? es obligatorio',
            ]
        );
        //Ya no se usará la  variable $has_budget
        //$has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        $year = $request->fiscal_year ?? date("Y");
        if ($request->is_order == 'compra') {
            $codeSetting = CodeSetting::where("model", PurchaseDirectHire::class)->first();
        } elseif ($request->is_order == 'servicio') {
            $codeSetting = CodeSetting::where('table', 'purchase_service_orders')->first();
        } else {
            $codeSetting = false;
        }
        if (!$codeSetting) {
            return response()->json(['result' => false, 'message' => [
                'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]], 500);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $codeDirectHire = generate_registration_code_budget(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
            substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
            $currentFiscalYear->year : $year),
            PurchaseDirectHire::class,
            'code'
        );

        $data = $request->all();
        $data['code'] = $codeDirectHire;
        $data['due_date'] = json_encode([$request->time_frame => $request->due_date]);
        //$data['receiver'] = json_encode($request->receiver);
        $data['hiring_number'] = $data['hiring_number'] ?? '';
        $purchaseDirectHire = PurchaseDirectHire::create($data);

        /* Registro y asociación de documentos */
        $documentFormat = ['pdf'];

        if ($request->files_purchase_type) {
            foreach ($request->files_purchase_type as $key => $file) {
                $extensionFile = $file->getClientOriginalExtension();
                if (in_array($extensionFile, $documentFormat)) {
                    /* Se guarda el archivo y se almacena */
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        PurchaseDirectHire::class,
                        $purchaseDirectHire->id,
                        $code = null,
                        $sign = false,
                        $public_url = false,
                        $originalName = true,
                        $checkAllowed = false,
                        $archive_number = $key,
                    );
                }
            }
        }

        /* Se relaciona los presupuestos base con la orden de contratación directa */
        $quotation_list = json_decode(json_encode($request->all()['quotation_list']));

        $purchaseQuotationsID = [];
        // $tax = null;
        foreach ($quotation_list as $quotation) {
            $req = json_decode($quotation, true);
            $baseBudget = PurchaseQuotation::find($req['id']);
            $baseBudget->orderable_type = PurchaseDirectHire::class;
            $baseBudget->orderable_id = $purchaseDirectHire->id;
            $baseBudget->save();
            array_push($purchaseQuotationsID, $req['id']);
        }

        $supplier = PurchaseSupplier::find($request->purchase_supplier_id);
        Receiver::firstOrCreate(
            [
                'receiverable_id' => $request->purchase_supplier_id,
                'receiverable_type' => PurchaseSupplier::class
            ],
            [
                'group' => 'Proveedores',
                'description' => $supplier->referential_name
            ]
        );
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Muestra información de una contratación directa
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $records = PurchaseDirectHire::with([
            'contratingDepartment',
            'currency',
            'documents',
            'firstSignature.payrollStaff',
            'fiscalYear',
            'institution',
            'preparedBy.payrollStaff',
            'purchaseSupplier',
            'purchaseSupplierObject',
            'reviewedBy.payrollStaff',
            'secondSignature.payrollStaff',
            'userDepartment',
            'verifiedBy.payrollStaff',
            'purchaseType',
            'quatations' =>  function ($query) {
                $query->with(['relatable' => function ($query) {
                    $query->with(['purchaseRequirementItem' => function ($query) {
                        $query->with('purchaseRequirement')->get();
                        $query->with('historyTax')->get();
                    }])->get();
                }])->get();
            },
        ])->find($id);

        $due_date = json_decode($records->due_date, true);
        $time_frame = array_keys($due_date);
        $records->due_date = $due_date[$time_frame[0]];
        $records->time_frame = $time_frame[0];

        foreach ($records['quatations'] as $x) {
            $records['base_budget'] = Pivot::where(
                'recordable_type',
                PurchaseQuotation::class
            )
            ->where('recordable_id', $x->id)
            ->with('relatable')
            ->get();
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Muestra el formulario para editar una contratación directa
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve | pedrobui@gmail.com>
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        $record_edit = PurchaseDirectHire::where('id', $id)->with([
            'documents',
            'fiscalYear',
            'contratingDepartment',
            'userDepartment',
            'purchaseBaseBudgets',
            'purchaseSupplierObject',
            'purchaseSupplier'
        ])->get();

        foreach ($record_edit as $record) {
            $code = explode('-', $record->code);
            $codeSettingOrder = CodeSetting::where("model", PurchaseDirectHire::class)->first();
            $codeSettingService = CodeSetting::where('table', 'purchase_service_orders')->first();
            if ($code[0] == $codeSettingService->format_prefix) {
                $record->is_order = 'servicio';
            } elseif ($code[0] == $codeSettingOrder->format_prefix) {
                $record->is_order = 'compra';
            } else {
                $record->is_order = '';
            }

            $due_date = json_decode($record->due_date, true);
            $time_frame = array_keys($due_date);
            $record->due_date = $due_date[$time_frame[0]];
            $record->time_frame = $time_frame[0] ?? '';
        }

        //información requerida en el vista
        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $suppliers = template_choices('Modules\Purchase\Models\PurchaseSupplier', ['rif', '-', 'name'], [], true);

        $department_list = template_choices('App\Models\Department', 'name', [], true);

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))->orderBy('operation_date', 'DESC')->first();

        $taxUnit = TaxUnit::where('active', true)->first();

        $purchase_supplier_objects = [];

        array_push(
            $purchase_supplier_objects,
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        );

        foreach (PurchaseSupplierObject::all() as $record) {
            $type = $record->type;
            if ($type == 'B') {
                $type = 'Bienes';
            } elseif ($type == 'O') {
                $type = 'Obras';
            } elseif ($type == 'S') {
                $type = 'Servivios';
            }
            array_push(
                $purchase_supplier_objects,
                [
                    'id' => $record->id,
                    'text' => $type . ' - ' . $record->name,
                ],
            );
        }

        $quotations_orderable = PurchaseQuotation::with(
            'currency',
            // Requirement
            'pivotRecordable.relatable.purchaseRequirement.contratingDepartment',
            'pivotRecordable.relatable.purchaseRequirement.userDepartment',
            // RequirementItems
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'relatable.purchaseRequirementItem.pivotPurchase',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.purchaseRequirement.purchaseBaseBudget',
            // BaseBudget
            'pivotRecordable.relatable.tax.histories',
        )
        ->where(['orderable_id' => null, 'status' => 'APPROVED'])
        ->orderBy('id', 'ASC')
        ->get()->toArray();

        $quotation_id = PurchaseQuotation::with(
            'currency',
            // Requirement
            'pivotRecordable.relatable.purchaseRequirement.contratingDepartment',
            'pivotRecordable.relatable.purchaseRequirement.userDepartment',
            // RequirementItems
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'relatable.purchaseRequirementItem.pivotPurchase',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.purchaseRequirement.purchaseBaseBudget',
            // BaseBudget
            'pivotRecordable.relatable.tax.histories',
        )
        ->where('orderable_id', $id)
        ->orderBy('id', 'ASC')
        ->first();

        array_push($quotations_orderable, $quotation_id);

        /* Se obtienen los datos laborales */
        $employments = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ],
        ];

        if (Module::has('Payroll') && Module::isEnabled('Payroll')) {
            if ($user_profile && $user_profile->institution !== null) {
                foreach (
                    \Modules\Payroll\Models\PayrollEmployment::with('payrollStaff', 'profile')
                    ->whereHas('profile', function ($query) use ($user_profile) {
                        $query->where('institution_id', $user_profile->institution->id);
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
        return view('purchase::purchase_order.direct_hire_form', [
            'requirements' => json_encode($quotations_orderable),
            'tax' => json_encode($historyTax),
            'tax_unit' => json_encode($taxUnit),
            'department_list' => json_encode($department_list),
            'employments' => json_encode($employments),
            'purchase_supplier_objects' => json_encode($purchase_supplier_objects),
            'suppliers' => json_encode($suppliers),
            'record_edit' => json_encode($record_edit),
        ]);
    }

    /**
     * Actualiza los datos de una contratación directa
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
                'institution_id' => 'required|integer',
                'contracting_department_id' => 'required|integer',
                'user_department_id' => 'required|integer',
                'purchase_supplier_id' => 'required|integer',
                'purchase_supplier_object_id' => 'required|integer',
                'fiscal_year_id' => 'required|integer',
                'currency_id' => 'required|integer',
                'funding_source' => 'required',
                'description' => 'required',
                'presupuesto_base_estimado' => 'required|mimes:pdf',
                'disponibilidad_presupuestaria' => 'required|mimes:pdf',
                'date' => [
                    'required', new DateBeforeFiscalYear('fecha de generación')
                ],
            ],
            [
                'presupuesto_base_estimado.required' => 'El archivo de presupuesto base estimado es obligatorio.',
                'presupuesto_base_estimado.mimes'
                    => 'El archivo de presupuesto base estimado debe estar en formato pdf.',
                'disponibilidad_presupuestaria.required'
                    => 'El archivo de disponibilidad presupuestaria es obligatorio.',
                'disponibilidad_presupuestaria.mimes'
                    => 'El archivo de disponibilidad presupuestaria debe estar en ' . 'formato pdf.',
                'institution_id.required' => 'El campo institución es obligatorio',
                'contracting_department_id.required' => 'El campo unidad contratante es obligatorio',
                'user_department_id.required' => 'El campo unidad usuaria es obligatorio',
                'purchase_supplier_id.required' => 'El campo proveedor es obligatorio',
                'purchase_supplier_object_id.required' => 'El campo denominación del requerimiento es obligatorio',
                'fiscal_year_id.required' => 'El campo año de ejercicio económico es obligatorio',
                'currency_id.required' => 'El campo tipo de moneda es obligatorio',
                'funding_source.required' => 'El campo fuente de financiamiento es obligatorio',
                'description.required' => 'El campo denominación especifica del requerimiento es obligatorio',
            ]
        );

        /* determina si esta instalado y habilitado el modulo Budget */
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        if (!Module::has('Budget') || !Module::isEnabled('Budget')) {
        }

        $supplier = PurchaseSupplier::find($request->purchase_supplier_id);
        Receiver::firstOrCreate(
            [
                'receiverable_id' => $request->purchase_supplier_id,
                'receiverable_type' => PurchaseSupplier::class
            ],
            [
                'group' => 'Proveedores',
                'description' => $supplier->referential_name
            ]
        );
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina una contratación directa
     *
     * @author   Franscisco Escala <Fjescala@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy(UploadDocRepository $upDoc, $id)
    {
        $record = PurchaseDirectHire::find($id);
        if ($record) {
            //Se busca los viejos requerimientos asociados contratación directa y limpiarlo
            $searchPurchaseQuotation = PurchaseQuotation::where('orderable_id', $id)->get();
            foreach ($searchPurchaseQuotation as $sPurchaseQuotation) {
                $sPurchaseQuotation->orderable_type = null;
                $sPurchaseQuotation->orderable_id = null;
                $sPurchaseQuotation->save();
            }
            /* Se elimina la relacion y los documentos previos **/
            $supp_docs = $record->documents()->get();
            if (count($supp_docs) > 0) {
                foreach ($supp_docs as $doc) {
                    $upDoc->deleteDoc($doc->file, 'documents');
                    $doc->delete();
                }
            }
            $record->delete();
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Obtiene listado de registros de contratación directa
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        $records = PurchaseDirectHire::query()->with(
            'fiscalYear',
            'preparedBy',
            'reviewedBy',
            'verifiedBy',
            'firstSignature',
            'secondSignature'
        )
        ->orderBy('id')
        ->search($request->query('query'))
        ->paginate($request->limit ?? 10);

        return response()->json([
            'data' => $records->items(),
            'count' => $records->total(),
        ], 200);
    }

    /**
     * Realiza la actualización de registro de orden de compra directa
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve | pedrobui@gmail.com>
     * @author   Franscisco Escala <Fjescala@gmail.com>
     *
     * @param     \Illuminate\Http\Request    $request    Datos de la petición
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse|void
     */
    public function updatePurchaseOrder(Request $request, $id, UploadDocRepository $upDoc)
    {
        try {
            foreach ($request->record_items as $product) {
                $prod = json_decode($product, true);
            }
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['message' => 'Error actualizando el registro'], 500);
        }

        $this->validate(
            $request,
            [
                'institution_id' => 'required|integer',
                'contracting_department_id' => 'required|integer',
                'user_department_id' => 'required|integer',
                'purchase_supplier_id' => 'required|integer',
                'purchase_supplier_object_id' => 'required|integer',
                'fiscal_year_id' => 'required|integer',
                'currency_id' => 'required|integer',
                'funding_source' => 'required',
                'description' => 'required',
                'quotation_list' => 'required',
                'due_date' => 'required_unless:time_frame,"delivery"',
                'time_frame' => 'required',
                'date' => ['required', new DateBeforeFiscalYear('fecha de generación')],
                // Firmas
                'prepared_by_id' => 'required',
                'purchase_type_id' => 'required',
            ],
            [
                'institution_id.required' => 'El campo institución es obligatorio',
                'contracting_department_id.required' => 'El campo unidad contratante es obligatorio',
                'user_department_id.required' => 'El campo unidad usuaria es obligatorio',
                'purchase_supplier_id.required' => 'El campo proveedor es obligatorio',
                'purchase_supplier_object_id.required' => 'El campo denominación del requerimiento es obligatorio',
                'fiscal_year_id.required' => 'El campo año de ejercicio económico es obligatorio',
                'currency_id.required' => 'El campo tipo de moneda es obligatorio',
                'funding_source.required' => 'El campo fuente de financiamiento es obligatorio',
                'description.required' => 'El campo denominación especifica del requerimiento es obligatorio',
                'quotation_list.required' => 'Debe seleccionar al menos un presupuesto base.',
                'due_date.required_unless' => 'El campo plazo de entrega es obligatorio',
                'time_frame.required' => 'El campo período es obligatorio',
                // firmas
                'prepared_by_id.required' => 'El campo preparado por es obligatorio',
                'purchase_type_id.required' => 'El campo modalidad de compra es obligatorio',
            ]
        );

        //Actualiza los campos del registro del modelo PurchaseDirectHire
        $purchaseDirectHire = PurchaseDirectHire::find($id);
        try {
            DB::transaction(function () use ($request, $purchaseDirectHire, $upDoc) {
                if ($purchaseDirectHire->purchase_type_id != $request->purchase_type_id) {
                    $supp_docs = $purchaseDirectHire->documents()->get();
                    if (count($supp_docs) > 0) {
                        foreach ($supp_docs as $doc) {
                            $upDoc->deleteDoc($doc->file, 'documents');
                            $doc->delete();
                        }
                    }
                }

                $code = explode('-', $purchaseDirectHire->code);
                $codeSettingOrder = CodeSetting::where("model", PurchaseDirectHire::class)->first();
                $codeSettingService = CodeSetting::where('table', 'purchase_service_orders')->first();

                //Se verifica si la orden es de servicio o de compra
                $is_order = '';
                if ($code[0] == $codeSettingService->format_prefix) {
                    $is_order = 'servicio';
                } elseif ($code[0] == $codeSettingOrder->format_prefix) {
                    $is_order = 'compra';
                }

                //Si el tipo de orden actual es diferente al que se se está actualizando se crea un nuevo código
                if ($is_order != $request->is_order) {
                    $year = $request->fiscal_year ?? date("Y");
                    if ($request->is_order == 'compra') {
                        $codeSetting = CodeSetting::where("model", PurchaseDirectHire::class)->first();
                    } elseif ($request->is_order == 'servicio') {
                        $codeSetting = CodeSetting::where('table', 'purchase_service_orders')->first();
                    }

                    if (isset($codeSetting)) {
                        $currentFiscalYear = FiscalYear::select('year')
                        ->where(['active' => true, 'closed' => false])
                        ->orderBy('year', 'desc')->first();

                        $codeDirectHire = generate_registration_code_budget(
                            $codeSetting->format_prefix,
                            strlen($codeSetting->format_digits),
                            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                            substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                            $currentFiscalYear->year : $year),
                            PurchaseDirectHire::class,
                            'code'
                        );
                    }
                }

                $purchaseDirectHire->code = isset($codeDirectHire)
                ? $codeDirectHire : $request->code ?? $purchaseDirectHire->code;
                $purchaseDirectHire->date = $request->date;
                $purchaseDirectHire->institution_id = $request->institution_id;
                $purchaseDirectHire->contracting_department_id = $request->contracting_department_id;
                $purchaseDirectHire->user_department_id = $request->user_department_id;
                $purchaseDirectHire->fiscal_year_id = $request->fiscal_year_id;
                $purchaseDirectHire->purchase_supplier_id = $request->purchase_supplier_id;
                $purchaseDirectHire->purchase_supplier_object_id = $request->purchase_supplier_object_id;
                $purchaseDirectHire->currency_id = $request->currency_id;
                $purchaseDirectHire->funding_source = $request->funding_source;
                $purchaseDirectHire->description = $request->description;
                $purchaseDirectHire->payment_methods = $request->payment_methods;
                $purchaseDirectHire->prepared_by_id = $request->prepared_by_id;
                $purchaseDirectHire->reviewed_by_id = $request->reviewed_by_id
                    == "null" ? null : $request->reviewed_by_id;
                $purchaseDirectHire->verified_by_id = $request->verified_by_id
                    == "null" ? null : $request->verified_by_id;
                $purchaseDirectHire->first_signature_id = $request->first_signature_id
                    == "null" ? null : $request->first_signature_id;
                $purchaseDirectHire->second_signature_id = $request->second_signature_id
                    == "null" ? null : $request->second_signature_id;
                $purchaseDirectHire->purchase_type_id = $request->purchase_type_id;
                $purchaseDirectHire->due_date = json_encode([$request->time_frame => $request->due_date]);
                $purchaseDirectHire->hiring_number = $request->hiring_number ?? '';
                $purchaseDirectHire->save();
                $documentFormat = ['pdf'];

                /* Registro y asociación de documentos */
                if ($request->files_purchase_type) {
                    foreach ($request->files_purchase_type as $key => $file) {
                        $extensionFile = $file->getClientOriginalExtension();
                        if (in_array($extensionFile, $documentFormat)) {
                            $supp_docs = $purchaseDirectHire->documents()->get();
                            foreach ($supp_docs as $oldFile) {
                                if ($oldFile["archive_number"] == $key) {
                                    $upDoc->deleteDoc($oldFile->file, 'documents');
                                    $oldFile->delete();
                                }
                            }
                            /* Se guarda el archivo y se almacena */
                            $upDoc->uploadDoc(
                                $file,
                                'documents',
                                PurchaseDirectHire::class,
                                $purchaseDirectHire->id,
                                $code = null,
                                $sign = false,
                                $public_url = false,
                                $originalName = true,
                                $checkAllowed = false,
                                $archive_number = $key,
                            );
                        }
                    }
                }

                /* Se relaciona los presupuestos base con la orden de contratación directa */
                //Se busca los viejos requerimientos asociados contratación directa y limpiarlo
                $searchPurchaseQuotation = PurchaseQuotation::where('orderable_id', $purchaseDirectHire->id)->get();
                foreach ($searchPurchaseQuotation as $sPurchaseQuotation) {
                    $sPurchaseQuotation->orderable_type = null;
                    $sPurchaseQuotation->orderable_id = null;
                    $sPurchaseQuotation->save();
                }

                // Se busca los nuevos requerimientos para asociarlos a contratación directa
                $quotation_list = json_decode(json_encode($request->all()['quotation_list']));

                $purchaseBaseBudgetsID = [];
                $tax = null;
                foreach ($quotation_list as $requirement) {
                    $req = json_decode($requirement, true);
                    $baseBudget = PurchaseQuotation::find($req['id']);
                    $baseBudget->orderable_type = PurchaseDirectHire::class;
                    $baseBudget->orderable_id = $purchaseDirectHire->id;
                    $baseBudget->save();
                }
                /* Datos del compromiso */
                return response()->json(['message' => 'Success'], 200);
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

            return response()->json(['message' => 'Error'], 500);
        }
    }

    /**
     * Genera el código disponible a asignar
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return string
     */
    public function generateCodeAvailable()
    {
        $codeSetting = CodeSetting::where('table', 'minutes_code')
            ->first();

        if (!$codeSetting) {
            $codeSetting = CodeSetting::where('table', 'minutes_code')->first();
        }

        if ($codeSetting) {
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])
                ->orderBy('year', 'desc')
                ->first();
            $code  = generate_registration_code(
                $codeSetting->format_prefix,
                strlen($codeSetting->format_digits),
                (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
                $codeSetting->model,
                $codeSetting->field
            );
        } else {
            $code = 'Error al generar código de la contratación directa';
        }
        return $code;
    }

    /**
     * Método para cambiar el estado de una orden de compra a aprobado
     *
     * @author Angelo Osorio <danielking.321 at gmail.com> | <adosorio at cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $purchaseOrder = PurchaseDirectHire::with([
            'fiscalYear',
            'purchaseType',
            'quatations' =>  function ($query) {
                $query->with(['relatable' => function ($query) {
                    $query->with(['purchaseRequirementItem' => function ($query) {
                        $query->with('purchaseRequirement')->get();
                        $query->with('historyTax')->get();
                    }])->get();
                }])->get();
            },
        ])->find($request->id);

        //Se pregunta si el módulo 'Budget' (Presupuesto) está habilitado
        $has_budget = (Module::has('Budget') && Module::isEnabled('Budget'));
        $created_compromise = false;

        //Se procede a crear el compromiso relacionado a la orden de compra que se está aprobando
        if ($has_budget && $purchaseOrder) {
            $currentFiscalYear = FiscalYear::select('year')
                ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

            $year = $purchaseOrder->fiscal_year->year ?? date("Y");

            $codeSettingCompromise = CodeSetting::where(
                "model",
                \Modules\Budget\Models\BudgetCompromise::class
            )->first();
            $codeCompromise = generate_registration_code(
                $codeSettingCompromise->format_prefix,
                strlen($codeSettingCompromise->format_digits),
                (strlen($codeSettingCompromise->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : substr($year, 0, 2)) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : $year),
                \Modules\Budget\Models\BudgetCompromise::class,
                'code'
            );

            $documentStatus = DocumentStatus::where('action', 'EL')->first();
            $compromise = \Modules\Budget\Models\BudgetCompromise::create([
                'sourceable_id' => $purchaseOrder->id,
                'document_number' => $purchaseOrder->code,
                'institution_id' => $purchaseOrder->institution_id,
                'compromised_at' => null,
                'sourceable_type' => PurchaseDirectHire::class,
                'description' => $purchaseOrder->description,
                'code' => $codeCompromise,
                'document_status_id' => $documentStatus->id,
            ]);

            $total = 0;

            /* Gestiona los ítems del compromiso */
            foreach ($purchaseOrder->quatations[0]->relatable as $product) {
                $prod = json_decode($product, true);
                $amount = 0;

                $amount = $prod['unit_price'] * ($prod['quantity'] > 0
                    ? $prod['quantity']
                    : $prod['purchase_requirement_item']['quantity']);

                $warehouseProduct = $prod['purchase_requirement_item'];
                $tax_id = $warehouseProduct && $warehouseProduct['history_tax_id']
                    ? $warehouseProduct['history_tax']['tax_id'] : false;
                $tax = $tax_id ? Tax::find($tax_id) : false;
                $taxHistory = ($tax) ? $tax->histories()->orderBy('operation_date', 'desc')->first() : new HistoryTax();
                $taxAmount = ($amount * (($taxHistory) ? $taxHistory->percentage : 0)) / 100;

                $compromise->budgetCompromiseDetails()->Create([
                    'description' => $prod['purchase_requirement_item']['description']
                    ?? $prod['purchase_requirement_item']['technical_specifications']
                    ?? 'N/A',
                    'amount' => $amount,
                    'tax_amount' => $taxAmount,
                    'tax_id' => $tax ? $tax->id : null,
                ]);

                $total += ($amount + $taxAmount);

                $compromise->budgetStages()->updateOrCreate([
                    'code' => $codeCompromise,
                ], [ 'registered_at' => now(),
                    'type' => 'PRE',
                    'amount' => $total
                ]);
            }
            $created_compromise = true;
        }

        if ($created_compromise) {
            //Se Aprueba la orden de compra
            $purchaseOrder->status = "APPROVED";
            $purchaseOrder->save();
            return response()->json(['message' => 'Success'], 200);
        }

        return response()->json(['result' => false, 'message' => [
            'type' => 'custom', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'danger',
            'text' => 'Error al intentar aprobar la orden de compra',
        ]], 500);
    }

    /**
     * Método que buusca el registro por el campo 'code' en lugar de 'id'
     *
     * @author Ing. Argenis Osorio | <aosorio@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function showDirectHireCurrency($code)
    {
        $record = PurchaseDirectHire::with('currency')->where('code', $code)->first();

        if (!$record) {
            return response()->json(['error' => 'Record not found'], 404);
        }

        return response()->json(['record' => $record], 200);
    }
}
