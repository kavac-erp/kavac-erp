<?php

namespace Modules\Sale\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleQuote;
use Modules\Sale\Models\SaleQuoteProduct;
use Modules\Sale\Models\SaleWarehouseInventoryProduct;
use Modules\Sale\Models\SaleWarehouseMovement;
use Modules\Sale\Models\SaleTypeGood;
use App\Models\HistoryTax;
use Modules\Sale\Models\SaleClient;

/**
 * @class SaleQuoteControlle
 * @brief Controlador de cotizaciones en el modulo de comercializacion
 *
 * Controlador de cotizaciones en el modulo de comercializacion
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleQuoteController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar en select
     *
     * @var array $data
     */
    protected $data = [];

    // -1 Rejected, 0 Created and 1 approved
    /**
     * Estatus inicial
     * @var integer $initial_status
     */
    protected $initial_status = 0;

    /**
     * Estatus inicial aprobado
     *
     * @var integer $approved_status
     */
    protected $approved_status = 1;

    /**
     * Estatus inicial rechazado
     *
     * @var integer $rejected_status
     */
    protected $rejected_status = 2;

    /**
     * Lista de estatus
     *
     * @var array $status_list
     */
    protected $status_list = [2 => 'Cancelado', 0 => 'Creado', 1 => 'Aprobado'];

    /**
     * Define la configuración de la clase
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {

        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:sale.quote.list', ['only' => 'index']);
        $this->middleware('permission:sale.quote.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.quote.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.quote.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra el listado de cotizaciones
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::quotes.list');
    }

    /**
     * Muestra el formulario para registrar una nueva Cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::quotes.create');
    }

    /**
     * Valida y registra un nuevo producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  \Illuminate\Http\Request $request    Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
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
            SaleQuote::class,
            $codeSetting->field
        );

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? date('y') : date('Y'),
            $codeSetting->model,
            $codeSetting->field
        );

        $this->saleQuoteValidate($request);
        foreach ($this->getSaleSaleQuoteFields() as $id) {
            if ($id != 'status') {
                $inputs[$id] = $request->input($id);
            } else {
                $inputs[$id] = $this->initial_status;
            }
        }
        $inputs['total_without_tax'] = 0;
        $inputs['total'] = 0;
        $inputs['code'] = $code;
        $SaleQuote = SaleQuote::create($inputs);

        $products = count($request->sale_quote_products) > 0 ? $request->sale_quote_products : [];
        $totals = $this->createProducts($products, $SaleQuote->id);

        $SaleQuote->total = $totals['total'];
        $SaleQuote->total_without_tax = $totals['total_without_tax'];
        $SaleQuote->save();
        $request->session()->flash('message', ['type' => 'Save']);
        return response()->json(['result' => true, 'redirect' => route('sale.quotes.index')], 200);
    }

    /**
     * Muestra el formulario para editar una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\View\View
     */
    public function edit(SaleQuote $SaleQuote)
    {
        $width = [
            'saleChargeMoney',
            'saleWarehouseMethod',
            'saleQuoteProduct.saleWarehouseInventoryProduct.SaleSettingProduct',
            'saleQuoteProduct.saleTypeGood',
            'saleQuoteProduct.SaleListSubservices',
            'saleQuoteProduct.measurementUnit',
            'saleQuoteProduct.Currency',
            'saleQuoteProduct.historyTax',
        ];
        $quote = SaleQuote::with($width)->find($SaleQuote->id);
        return view('sale::quotes.create', ['quoteid' => $SaleQuote->id, 'quote' => $quote]);
    }

    /**
     * Actualiza una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaleQuote $SaleQuote)
    {

        $this->saleQuoteValidate($request);
        foreach ($this->getSaleSaleQuoteFields() as $id) {
            if ($id != 'status') {
                $SaleQuote->{$id} = $request->input($id);
            } else {
                $SaleQuote->{$id} = $this->initial_status;
            }
        }
        $products = count($request->sale_quote_products) > 0 ? $request->sale_quote_products : [];
        $totals = $this->createProducts($products, $SaleQuote->id);

        $SaleQuote->total = $totals['total'];
        $SaleQuote->total_without_tax = $totals['total_without_tax'];
        $SaleQuote->save();
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('sale.quotes.index')], 200);
    }

    /**
     * Elimina una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaleQuote $SaleQuote)
    {
        $this->createProducts([], $SaleQuote->id);
        $SaleQuote->delete();
        return response()->json(['result' => true, 'redirect' => route('sale.quotes.index')], 200);
    }

    /**
     * Realiza la validación de una cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     Request    $request         Datos de la peticióncurrency_id\
     *
     * @return    void
     */
    public function saleQuoteValidate(Request $request)
    {
        $attributes = [
            'type_person' => 'Tipo de persona',
            'name' => 'Nombre',
            'id_number' => 'Identificación',
            'phone' => 'Teléfono de contacto',
            'email' => 'Correo Electrónico',
            'sale_charge_money_id' => 'Método de cobro',
            'deadline_date' => 'Fecha límite de respuesta',
            'sale_quote_products' => 'Productos',
            'sale_warehouse_method_id' => 'Almacenes',
            'sale_quote_products.*.product_type' => 'Tipo de Producto',
            'sale_quote_products.*.sale_type_good_id' => 'Servicio',
            'sale_quote_products.*.sale_warehouse_inventory_product' => 'Producto',
            'sale_quote_products.*.value' => 'Precio unitario',
            'sale_quote_products.*.currency_id' => 'Moneda',
            'sale_quote_products.*.measurement_unit_id' => 'Unidad de medida',
            'sale_quote_products.*.quantity' => 'Cantidad de productos',
        ];
        $validation = [];
        $validation['type_person'] = ['required'];
        $validation['name'] = ['required', 'max:100'];
        $validation['id_number'] = ['required', 'digits_between:1,10'];
        $validation['phone'] = ['required', 'regex:/^\+\d{2}-\d{3}-\d{7}$/u'];
        $validation['email'] = ['required', 'email'];
        $validation['sale_charge_money_id'] = ['required'];
        $validation['sale_quote_products'] = ['required'];
        $validation['sale_warehouse_method_id'] = ['required'];
        $todayDate = date('Y-m-d');
        $validation['deadline_date'] = ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:' . $todayDate];
        $validation['sale_quote_products.*.product_type'] = ['required'];
        $validation['sale_quote_products.*.sale_type_good_id'] = ['required_if:sale_quote_products.*.type_product,"Servicios"'];
        $validation['sale_quote_products.*.sale_warehouse_inventory_product'] = ['required_if:sale_quote_products.*.type_product,"Productos"'];
        $validation['sale_quote_products.*.value'] = ['required', 'numeric', 'gt:0'];
        $validation['sale_quote_products.*.currency_id'] = ['required'];
        $validation['sale_quote_products.*.measurement_unit_id'] = ['required'];
        $validation['sale_quote_products.*.quantity'] = ['required', 'integer', 'gt:0'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Agrega los productos a una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     array    $products         Arreglo con los atributos a agregar
     * @param     integer   $id        Identificador de la Cotización
     *
     * @return    array
     */
    public function createProducts($products = [], $id = 0)
    {
        $total = 0;
        $total_without_tax = 0;
        if ($id) {
            SaleQuoteProduct::where('sale_quote_id', $id)->delete();
            foreach ($products as $product) {
                $new_product = [];
                foreach ($this->getSaleSaleQuoteProductsFields() as $id_field) {
                    if (isset($product[$id_field])) {
                        $new_product[$id_field] = $product[$id_field];
                    }
                }
                $tax_value = ($tax = HistoryTax::find($product['history_tax_id'])) ? $tax->percentage / 100 : 0;
                $new_product['sale_quote_id'] = $id;
                $new_product['total_without_tax'] = $new_product['value'] * $new_product['quantity'];
                $total_without_tax += $new_product['total_without_tax'];
                $new_product['total'] = $new_product['total_without_tax'] + $new_product['total_without_tax'] * $tax_value;
                $total += $new_product['total'];
                $product = SaleQuoteProduct::create($new_product);
            }
        }
        return ['total_without_tax' => $total_without_tax, 'total' => $total];
    }

    /**
     * Obtiene los campos de una cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array
     */
    public function getSaleSaleQuoteFields()
    {
        return ['name', 'id_number', 'email', 'type_person', 'sale_warehouse_method_id', 'sale_charge_money_id', 'deadline_date', 'status', 'phone', 'code'];
    }

    /**
     * Obtiene los campos de un producto en una cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array
     */
    public function getSaleSaleQuoteProductsFields()
    {
        return [
            'value',
            'product_type',
            'sale_quote_id',
            'currency_id',
            'measurement_unit_id',
            'quantity',
            'total',
            'total_without_tax',
            'sale_warehouse_inventory_product_id',
            'sale_type_good_id',
            'history_tax_id',
            'sale_list_subservices_id',
        ];
    }

    /**
     * Rechaza una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  Request   $request   Datos de la petición
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function rejectedQuote(Request $request, SaleQuote $SaleQuote)
    {
        if ($SaleQuote->status != $this->initial_status) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'La cotización no se encuentra en un estado que se pueda cambiar su valor'
            ]);
            return response()->json(['result' => false, 'redirect' => route('sale.quotes.index')], 200);
        }
        $SaleQuote->status = $this->rejected_status;
        $SaleQuote->save();
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('sale.quotes.index')], 200);
    }

    /**
     * Aprueba una cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  Request   $request   Datos de la petición
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedQuote(Request $request, SaleQuote $SaleQuote)
    {
        if ($SaleQuote->status != $this->initial_status) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'La cotización no se encuentra en un estado que se pueda cambiar su valor'
            ]);
            return response()->json(['result' => false, 'redirect' => route('sale.quotes.index')], 200);
        }
        $SaleQuote->status = $this->approved_status;
        $SaleQuote->save();
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('sale.quotes.index')], 200);
    }

    /**
     * Vizualiza información de una cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  SaleQuote $SaleQuote Objeto con la cotización
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueInfo(SaleQuote $SaleQuote)
    {
        $width = [
            'saleChargeMoney',
            'saleWarehouseMethod',
            'saleQuoteProduct.saleWarehouseInventoryProduct.SaleSettingProduct',
            'saleQuoteProduct.saleTypeGood',
            'saleQuoteProduct.SaleListSubservices',
            'saleQuoteProduct.measurementUnit',
            'saleQuoteProduct.Currency',
            'saleQuoteProduct.historyTax',
        ];
        $quote = SaleQuote::with($width)->find($SaleQuote->id);
        return response()->json(['record' => $quote], 200);
    }

    /**
     * Obtiene un listado de cotizaciones
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueList()
    {
        return response()->json(['records' => SaleQuote::with(['saleQuoteProduct', 'saleChargeMoney', 'saleWarehouseMethod'])->get()], 200);
    }

    /**
     * Obtiene un listado de las cotización
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param  integer|string $status estado de la cotizacion (ver atributo status)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueStateList($status = '0')
    {
        $SaleQuotes = SaleQuote::where('status', $status)->get();
        return response()->json(['records' => $SaleQuotes], 200);
    }

    /**
     * Muestra una lista del porcentaje de impuestos registrados en el sistema
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los impuestos
     */
    public function getTaxes()
    {
        return template_choices('App\Models\HistoryTax', ['percentage'], '', true);
    }

    /**
     * Muestra una lista de los distintos estados que puede tener una cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleQuoteStatus()
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
     * Muestra una lista de los productos en el almacen
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los productos
     */
    public function getInventoryProducts()
    {
        $inventoryProduct = SaleWarehouseInventoryProduct::with(['SaleSettingProduct'])->get();
        $options = [['id' => '', 'text' => 'Seleccione...']];
        foreach ($inventoryProduct as $product) {
            if (!empty($product->SaleSettingProduct)) {
                array_push($options, ['id' => $product->id, 'text' => $product->SaleSettingProduct->name]);
                /** @todo puede pasar que haya un producto en inventario con el mismo nombre y cantidades, unidades de medida, etc. */
                /* pero no esta estipulado en el caso de uso para diferenciarlos
                descomentar y comentar el anterior para agregar el codigo del producto */
            }
        }

        return $options;
    }

    /**
     * Muestra una lista de los subservicios
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los subservicios
     */
    public function getSaleListSubservices()
    {
        return template_choices('Modules\Sale\Models\SaleListSubservices', ['name'], '', true);
    }

    /**
     * Muestra una lista los metodos de pago
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los subservicios
     */
    public function getSaleChargeMonies()
    {
        return template_choices('Modules\Sale\Models\SaleChargeMoney', ['name_charge_money'], '', true);
    }

    /**
     * Muestra una lista los metodos de pago
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los subservicios
     */
    public function getSaleWarehouseMethod()
    {
        return template_choices('Modules\Sale\Models\SaleWarehouse', ['name'], '', true);
    }

    /**
     * Obtiene el resgistro del producto en el almacen
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     integer    $type_id    Identificador único del producto en el almacen
     *
     * @return     \Illuminate\Http\JsonResponse
     */
    public function getPriceProduct($id = null)
    {
        $product = SaleWarehouseInventoryProduct::find($id);

        if ($product) {
            $product->quantity_max = isset($product->exist) ? $product->exist : 0;
            $product->quantity_max -= isset($product->reserved) ? $product->reserved : 0;
            $SaleWarehouseMovement = SaleWarehouseMovement::join(
                'sale_warehouse_inventory_product_movements',
                'sale_warehouse_movements.id',
                '=',
                'sale_warehouse_inventory_product_movements.sale_warehouse_movement_id'
            )->where('sale_warehouse_inventory_product_movements.sale_warehouse_inventory_product_id', $product->id)->first();

            $product->history_tax_id = $SaleWarehouseMovement && isset($SaleWarehouseMovement->history_tax_id) ? $SaleWarehouseMovement->history_tax_id : 0;
        }
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el registro del bien a comercializar
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param     integer    $type_id    Identificador único del tipo de bien
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getPriceService($id = null)
    {
        $product = SaleTypeGood::find($id);
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Muestra una lista de los productos para ser comercializados
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los registros a mostrar
     */
    public function getQuoteGoodsToBeTradeds()
    {
        return template_choices('Modules\Sale\Models\SaleTypeGood', ['name'], '', true);
    }

    /**
     * Muestra una lista de las unidades de medida
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return array con los registros a mostrar
     */
    public function getMeasurementUnits()
    {
        return template_choices('App\Models\MeasurementUnit', ['acronym', '-', 'name'], '', true);
    }

    /**
     * Obtiene una lista con los clientes registrados
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de los productos
     */
    public function getSaleClients()
    {
        $saleClients = SaleClient::with(['saleClientsPhone', 'saleClientsEmail'])->get();
        $clients = [];
        foreach ($saleClients as $client) {
            $clients[] = $client;
        }
        return response()->json($clients, 200);
    }

    /**
     * Obtiene una lista con de los clientes con cotizaciones
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleQuoteClients()
    {
        $Quoteclients = SaleQuote::select('id_number', 'name', 'email', 'type_person')->distinct('id_number')->get();
        $clients = [];
        $clients[] = [
            'id' => '',
            'text' => 'Todos',
        ];
        foreach ($Quoteclients as $client) {
            $clients[] = [
                'id' => $client->id_number,
                'text' => $client->name . ' (' . $client->email . ')',
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
    public function getSaleQuoteYear()
    {
        $Quotedate = SaleQuote::all('created_at')->min('created_at')->toarray();
        $years = [];
        $years[] = [
            'id' => '',
            'text' => 'Todos',
        ];
        if (isset($Quotedate['year'])) {
            $current = date("Y");
            for ($i = $Quotedate['year']; $i <= $current; $i++) {
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
    public function getSaleQuoteRangeDates()
    {
        $Quotedate = SaleQuote::all('created_at')->min('created_at')->toarray();
        $dates = [];
        if ($Quotedate) {
            if ($Quotedate['day'] < 10) {
                $Quotedate['day'] = '0' . $Quotedate['day'];
            }
            $dates['min'] = $Quotedate['year'] . '-' . $Quotedate['month'] . '-' . $Quotedate['day'];
        }
        $Quotedate = SaleQuote::all('created_at')->max('created_at')->toarray();
        if ($Quotedate) {
            if ($Quotedate['day'] < 10) {
                $Quotedate['day'] = '0' . $Quotedate['day'];
            }
            $dates['max'] = $Quotedate['year'] . '-' . $Quotedate['month'] . '-' . $Quotedate['day'];
        }
        return response()->json($dates, 200);
    }
}
