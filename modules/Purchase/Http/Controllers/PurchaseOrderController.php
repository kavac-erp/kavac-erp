<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseRequirement;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;
use Modules\Purchase\Models\HistoryTax;
use Modules\Purchase\Models\TaxUnit;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Models\ExchangeRate;

/**
 * @class PurchaseOrderController
 * @brief Controlador para la gestión de las órdenes de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseOrderController extends Controller
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
     * Muestra una lista de órdenes de compra o servicios
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('purchase::purchase_order.index', [
            'records' => PurchaseOrder::with('purchaseSupplier', 'currency', 'relatable')->orderBy('id', 'ASC')->get(),
        ]);
    }

    /**
     * Muestra el formulario para crear una orden de compra o servicio
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $suppliers  = template_choices('Modules\Purchase\Models\PurchaseSupplier', ['rif','-', 'name'], [], true);

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))->orderBy('operation_date', 'DESC')->first();

        $taxUnit    = TaxUnit::where('active', true)->first();

        $requirements = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'purchaseRequirementItems.warehouseProduct.measurementUnit',
            'purchaseBaseBudget.currency'
        )->where('requirement_status', 'PROCESSED')
        ->orderBy('id', 'ASC')->get();
        return view('purchase::purchase_order.form', [
            'requirements' => $requirements,
            // 'currencies'   => json_encode($currencies),
            'tax'          => json_encode($historyTax),
            'tax_unit'     => json_encode($taxUnit),
            'suppliers'    => json_encode($suppliers),
        ]);
    }

    /**
     * Almacena la orden de compra o servicio
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'purchase_supplier_id' => 'required|integer',
            'currency_id'          => 'required|integer',
        ], [
            'purchase_supplier_id.required' => 'El campo proveedor es obligatorio.',
            'purchase_supplier_id.integer'  => 'El campo proveedor debe ser numerico.',
            'currency_id.required'          => 'El campo de tipo de moneda es obligatorio.',
            'currency_id.integer'           => 'El campo de tipo de moneda debe ser numerico.',
        ]);

        $purchase_order = PurchaseOrder::create([
            'purchase_supplier_id' => $request->purchase_supplier_id,
            'currency_id'          => $request->currency_id,
            'subtotal'             => $request->subtotal,
        ]);

        foreach (json_decode($request['requirement_list'], true) as $req) {
            $requirement = PurchaseRequirement::find($req['id']);
            $requirement->requirement_status = 'BOUGHT';
            $requirement->purchase_order_id = $purchase_order->id;
            $requirement->save();

            foreach ($req['purchase_requirement_items'] as $item) {
                PurchasePivotModelsToRequirementItem::create([
                    'purchase_requirement_item_id' => $item['id'],
                    'relatable_type'               => PurchaseOrder::class,
                    'relatable_id'                 => $purchase_order->id,
                    'unit_price'                   => $item['unit_price']
                ]);
            }
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Muestra información de una orden de compra o servicio
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar una orden de compra o servicio
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $record_edit = PurchaseOrder::with(
            'purchaseSupplier',
            'currency',
            'purchaseRequirement.purchaseRequirementItems.measurementUnit',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'purchaseRequirement.purchaseBaseBudget.currency',
            'relatable'
        )->find($id);

        $suppliers  = template_choices('Modules\Purchase\Models\PurchaseSupplier', ['rif','-', 'name'], [], true);

        $historyTax = HistoryTax::with('tax')->whereHas('tax', function ($query) {
            $query->where('active', true);
        })->where('operation_date', '<=', date('Y-m-d'))->orderBy('operation_date', 'DESC')->first();

        $taxUnit    = TaxUnit::where('active', true)->first();

        $requirements = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'purchaseRequirementItems.measurementUnit',
            'purchaseBaseBudget.currency'
        )->where('requirement_status', 'PROCESSED')
        ->orderBy('id', 'ASC')->get();
        $requirements = $requirements->concat($record_edit->purchaseRequirement);

        return view('purchase::purchase_order.form', [
            'requirements' => $requirements,
            // 'currencies'   => json_encode($currencies),
            'tax'          => json_encode($historyTax),
            'tax_unit'     => json_encode($taxUnit),
            'suppliers'    => json_encode($suppliers),
            'record_edit'  => $record_edit
        ]);
    }

    /**
     * Actualiza la orden de compra o servicio
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  Request $request  Datos de la petición
     * @param  integer  $id      Identificador de la orden de compra
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePurchaseOrder(Request $request, $id)
    {
        $this->validate($request, [
            'purchase_supplier_id' => 'required|integer',
            'currency_id'          => 'required|integer',
        ], [
            'purchase_supplier_id.required' => 'El campo proveedor es obligatorio.',
            'purchase_supplier_id.integer'  => 'El campo proveedor debe ser numerico.',
            'currency_id.required'          => 'El campo de tipo de moneda es obligatorio.',
            'currency_id.integer'           => 'El campo de tipo de moneda debe ser numerico.',
        ]);

        $purchase_order = PurchaseOrder::find($id);

        if ($purchase_order) {
            $purchase_order->purchase_supplier_id = $request->purchase_supplier_id;
            $purchase_order->currency_id          = $request->currency_id;
            $purchase_order->subtotal             = $request->subtotal;
            $purchase_order->save();

            foreach (json_decode($request['list_to_delete'], true) as $requirement) {
                // trae requerimiento
                $rq = PurchaseRequirement::find($requirement['id']);

                if ($rq) {
                    $rq->requirement_status      = 'PROCESSED';
                    $rq->purchase_base_budget_id = null;
                    $rq->save();

                    foreach ($requirement['purchase_requirement_items'] as $item) {
                        $r = PurchasePivotModelsToRequirementItem::where('purchase_requirement_item_id', $item['id'])
                                                                    ->fisrt();
                        if ($r) {
                            $r->delete();
                        }
                    }
                }
            }

            foreach (json_decode($request['requirement_list'], true) as $requirement) {
                $rq = PurchaseRequirement::find($requirement['id']);
                if ($rq) {
                    $rq->requirement_status = 'BOUGHT';
                    $rq->purchase_order_id = $purchase_order->id;
                    $rq->save();

                    foreach ($requirement['purchase_requirement_items'] as $item) {
                        PurchasePivotModelsToRequirementItem::create([
                            'purchase_requirement_item_id' => $item['id'],
                            'relatable_type'               => PurchaseOrder::class,
                            'relatable_id'                 => $purchase_order->id,
                            'unit_price'                   => $item['unit_price']
                        ]);
                    }
                }
            }
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina una orden de compra o servicio
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $purchase_order = PurchaseOrder::find($id);
        if ($purchase_order) {
            foreach (PurchaseRequirement::where('purchase_order_id', $id)->orderBy('id', 'ASC')->get() as $record) {
                $record->requirement_status = 'PROCESSED';
                $record->purchase_order_id = null;
                $record->save();
            }
            foreach (
                PurchasePivotModelsToRequirementItem::where('relatable_id', $id)
                        ->orderBy('id', 'ASC')->get() as $record
            ) {
                $record->delete();
            }
            $purchase_order->delete();
        }
        return response()->json([
            'message' => 'Success',
        ], 200);
    }

    /**
     * Obtiene la conversión entre monedas
     *
     * @param integer $currency_id Identificador de la moneda a convertir
     * @param integer $base_budget_currency_id Identificador de la moneda base
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConvertion($currency_id, $base_budget_currency_id)
    {
        $record = ExchangeRate::where('active', true)
                        ->where('start_at', '>=', date('Y-m-d'))
                        ->where('end_at', '<=', date('Y-m-d'))
                        ->whereIn('to_currency_id', [$base_budget_currency_id, $currency_id])
                        ->whereIn('from_currency_id', [$base_budget_currency_id, $currency_id])
                         ->orderBy('end_at', 'DESC')->first();

        return response()->json(['record' => $record]);
    }
}
