<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\FiscalYear;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sale\Models\SaleOrder;
use Modules\Sale\Models\SaleQuote;
use Modules\Sale\Models\SaleSettingProduct;
use Illuminate\Contracts\Support\Renderable;
use Modules\Sale\Models\SaleWarehouseInventoryProduct;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class SaleOrderSettingController
 * @brief Gestiona los datos de la configuración de las ordenes
 *
 * @author jose puentes jpuentes@cenditel.gob.ve
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleOrderSettingController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de estatus de las ordenes
     *
     * @var array $status_list
     */
    protected $status_list = ['rechazado' => 'Cancelado', 'pending' => 'Creado', 'aprobado' => 'Aprobado'];

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
      // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.order.list', ['only' => 'index']);
        $this->middleware('permission:sale.order.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.order.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.order.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra el listado de las ordenes
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::order.list');
    }

    /**
     * Obtiene un listado de las ordenes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListOrder()
    {
        $data = [];
        $records = SaleOrder::all();
        foreach ($records as $key => $record) {
            $records[$key]->tstatus = '';
            if (isset($record->status) && !empty($record->status)) {
                $records[$key]->tstatus = isset($this->status_list[$record->status]) ? $this->status_list[$record->status] : $record->status;
            }
            if (!empty($record->products)) {
                $total = 0;
                $products = json_decode($record->products, true);
                foreach ($products as $id => $row) {
                    $total += $row['total'];
                }
                $records[$key]->total = $total;
            }
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene un listado de las ordenes solicitadas con estado pendiente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListPending()
    {
        $data = [];
        $records = SaleOrder::where('status', '=', 'pending')->get();
        foreach ($records as $key => $record) {
            if (!empty($record->products)) {
                $total = 0;
                $products = json_decode($record->products, true);
                foreach ($products as $id => $row) {
                    $total += $row['total'];
                }
                $records[$key]->total = $total;
            }
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene un listado de las ordenes solicitadas con estado rechazadas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListRejected()
    {
        $records = SaleOrder::where('status', '=', 'rechazado')->get();
        foreach ($records as $key => $record) {
            if (!empty($record->products)) {
                $total = 0;
                $products = json_decode($record->products, true);
                foreach ($products as $id => $row) {
                    $total += $row['total'];
                }
                $records[$key]->total = $total;
            }
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Obtiene un listado de las ordenes solicitadas con estado aprobadas
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getListApproved()
    {
        $records = SaleOrder::where('status', '=', 'aprobado')->get();
        foreach ($records as $key => $record) {
            if (!empty($record->products)) {
                $total = 0;
                $products = json_decode($record->products, true);
                foreach ($products as $id => $row) {
                    $total += $row['total'];
                }
                $records[$key]->total = $total;
            }
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Listado de órdenes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function options()
    {
        return response()->json(['records' => SaleOrder::all()], 200);
    }

    /**
     * Muestra el formulario para crear una nueva configuración de ordenes
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::order.create');
    }

    /**
     * Crea una nueva configuración de ordenes
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {

        $codeSetting = CodeSetting::where('table', 'sale_orders')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('sale.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
        ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
            substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
            $currentFiscalYear->year : date('Y')),
            SaleOrder::class,
            $codeSetting->field
        );

        $this->saleOrderValidate($request);

        $products = [];
        if (count($request->list_products)) {
            foreach ($request->list_products as $product) {
                $inventory_product = SaleWarehouseInventoryProduct::find($product['sale_warehouse_inventory_product_id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['quantity']) {
                        $inventory_product->reserved = $inventory_product->reserved + $product['quantity'];
                        $inventory_product->save();
                        $products[] = $product;
                    }
                }
            }
        }

        $order = SaleOrder::create([
            'name'        => $request->name,
            'id_number'   => $request->id_number,
            'email'       => $request->email,
            'type_person' => $request->type_person,
            'phone'       => $request->phone,
            'code'        => $code,
            'products'    => json_encode($products, JSON_FORCE_OBJECT),
            'status' => 'pending'
        ]);

        return response()->json(['record' => $order, 'message' => 'Success', 'redirect' => route('sale.order.index')], 200);
    }

    /**
     * Realiza la validación de un pedido
     *
     * @author Ing. Jose Puentes <jpuentes@cenditel.gob.ve>
     *
     * @param     Request    $request Datos de la petición
     *
     * @return void
     **/
    public function saleOrderValidate(Request $request)
    {
        $attributes = [
            'type_person' => 'Tipo de persona',
            'name' => 'Nombre',
            'id_number' => 'Identificación',
            'phone' => 'Teléfono de contacto',
            'email' => 'Correo Electrónico',
        ];

        $validation = [];
        $validation['type_person'] = ['required'];
        $validation['name'] = ['required', 'max:100'];
        $validation['id_number'] = ['required', 'max:10'];
        $validation['phone'] = ['required'];
        $validation['email'] = ['required', 'email'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Muestra información sobre una orden de pedido
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar una orden de pedido
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $total = 0;
        $productos = [];
        $total_without_tax = 0;
        $record = SaleOrder::where('id', $id)->first();
        $products = json_decode($record->products, true);
        foreach ($products as $id => $row) {
            $productos[] = [
                'id' => $id,
                'sale_warehouse_inventory_product_id' => $row['inventory_product']['id'],
                'quantity' => $row['quantity'],
                'value' => $row["value"],
                'iva' => $row["product_tax_value"],
                'total' => $row['total'],
                'measurement_unit_id' => $row['measurement_unit_id'],
                'measurement_unit' => $row['measurement_unit'],
                'history_tax_id' => $row['history_tax_id'],
                'total_without_tax' => $row['total_without_tax'],
                'currency_id' => $row['currency']['name']
            ];
            $total += $row['total'];
            $total_without_tax += $row['total_without_tax'];
        }
        if (!empty($record->id)) {
            $record->list_products = $productos;
            $record->total_without_tax = $total_without_tax;
            $record->total = $total;
        }
        return view('sale::order.create', ['orderid' => $id, 'order' => $record]);
    }

    /**
     * Actualiza la información de una orden de pedido
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $order = SaleOrder::find($id);

        $this->saleOrderValidate($request);
        $products = [];
        if ($request->list_products && !empty($request->list_products)) {
            foreach ($request->list_products as $product) {
                $inventory_product = SaleWarehouseInventoryProduct::find($product['sale_warehouse_inventory_product_id']);
                if (!is_null($inventory_product)) {
                    $exist_real = $inventory_product->exist - $inventory_product->reserved;
                    if ($exist_real >= $product['quantity']) {
                        $inventory_product->reserved = $inventory_product->reserved + $product['reserved'];
                        $inventory_product->save();
                        $products[] = $product;
                    }
                }
            }
        }

        $order->name  = $request->name;
        $order->id_number = $request->id_number;
        $order->email  = $request->email;
        $order->phone  = $request->phone;
        $order->type_person  = $request->type_person;
        $order->code = $request->input('code');
        $order->products = json_encode($products, JSON_FORCE_OBJECT);
        $order->save();
        return response()->json(['message' => 'Success', 'redirect' => route('sale.order.index')], 200);
    }

    /**
     * Elimina una orden de pedido
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $order = SaleOrder::find($id);
        $order->delete();
        return response()->json(['record' => $order, 'message' => 'Success', 'redirect' => route('sale.order.index')], 200);
    }

    /**
     * Obtiene el precio del producto
     *
     * @param     integer|null    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getPriceProduct($id = null)
    {
        $product = SaleSettingProduct::find($id);
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Rechaza la solicitud de la orden
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectedOrder(Request $request, $id)
    {
        $sale_order = SaleOrder::find($id);
        $sale_order->status = 'rechazado';
        $sale_order->save();

        $products = json_decode($sale_order->products, true);
        foreach ($products as $id => $row) {
            $inventory_product = SaleWarehouseInventoryProduct::find($row['inventory_product']['id']);
            $reserved = $inventory_product->reserved;
            $reserved -= $row['quantity'];
            $inventory_product->reserved = $reserved;
            $inventory_product->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('sale.order.index')], 200);
    }

    /**
     * Aprueba la solicitud de la orden
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedOrder(Request $request, $id)
    {
        $sale_order = SaleOrder::find($id);
        $sale_order->status = 'aprobado';
        $sale_order->save();

        $products = json_decode($sale_order->products, true);
        foreach ($products as $id => $row) {
            $inventory_product = SaleWarehouseInventoryProduct::find($row['inventory_product']['id']);
            $exist = $inventory_product->exist;
            $exist -= $row['quantity'];
            $reserved = $inventory_product->reserved;
            $reserved -= $row['quantity'];
            $inventory_product->reserved = $reserved;
            $inventory_product->exist = $exist;
            $inventory_product->save();
        }

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('sale.order.index')], 200);
    }

    /**
     * Obtiene la información de la orden
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderInfo($id)
    {
        $data = [];
        $total = 0;
        $total_without_tax = 0;
        $record = SaleOrder::where('id', $id)->first();
        $products = json_decode($record->products, true);
        foreach ($products as $id => $row) {
            $productos[] = [
                'id' => $id,
                'name' => $row['inventory_product']['name'],
                'quantity' => $row['quantity'],
                'history_tax_id' => $row['history_tax_id'],
                'price_product' => $row["total_without_tax"],
                'iva' => $row["product_tax_value"],
                'total' => $row['total'],
                'moneda' => $row['currency']['name']
            ];
            $total += $row['total'];
            $total_without_tax += $row['total_without_tax'];
        }

        if (!empty($record->id)) {
            $data = [
                'id' => $record->id,
                'name' => $record->name,
                'id_number' => $record->id_number,
                'email' => $record->email,
                'type_person' => $record->type_person,
                'phone' => $record->phone,
                'status' => $record->status,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
                'list_products' => $productos,
                'total_without_tax' => $total_without_tax,
                'total' => $total,
            ];
        }
        return response()->json(['record' => $data], 200);
    }

    /**
     * Muestra una lista de los distintos estados que puede tener un pedido
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleOrderStatus()
    {
        $status = [];
        $status[] = [
            'id' => '',
            'text' => 'Seleccione...',
        ];
        foreach ($this->status_list as $id => $name) {
            $status[] = [
                'id' => $id,
                'text' => $name,
            ];
        }

        return response()->json($status, 200);
    }

    /**
     * Obtiene una lista con de los clientes con pedidos
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleOrderClients()
    {
        $Orderclients = SaleOrder::select('id_number', 'name')->distinct('id_number')->get();
        $clients = [];
        $clients[] = [
            'id' => '',
            'text' => 'Todos',
        ];
        foreach ($Orderclients as $client) {
            $clients[] = [
                'id' => $client->id_number,
                'text' => $client->name,
            ];
        }
        return response()->json($clients, 200);
    }

    /**
     * Obtiene una lista con la fecha minima de cotizaciones
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleOrdersYear()
    {
        $Orderdate = SaleOrder::all('created_at')->min('created_at')->toArray();
        $years = [];
        $years[] = [
            'id' => '',
            'text' => 'Todos',
        ];
        if (isset($Orderdate['year'])) {
            $current = date("Y");
            for ($i = $Orderdate['year']; $i <= $current; $i++) {
                $years[] = [
                    'id' => $i,
                    'text' => $i,
                ];
            }
        }
        return response()->json($years, 200);
    }

    /**
     * Obtiene una lista con la fecha de creacion min y max de cotizaciones
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleOrderRangeDates()
    {
        $Orderdate = SaleQuote::all('created_at')->min('created_at')->toArray();
        $dates = [];
        if ($Orderdate) {
            if ($Orderdate['day'] < 10) {
                $Orderdate['day'] = '0' . $Orderdate['day'];
            }
            $dates['min'] = $Orderdate['year'] . '-' . $Orderdate['month'] . '-' . $Orderdate['day'];
        }
        $Orderdate = SaleQuote::all('created_at')->max('created_at')->toArray();
        if ($Orderdate) {
            if ($Orderdate['day'] < 10) {
                $Orderdate['day'] = '0' . $Orderdate['day'];
            }
            $dates['max'] = $Orderdate['year'] . '-' . $Orderdate['month'] . '-' . $Orderdate['day'];
        }
        return response()->json($dates, 200);
    }
}
