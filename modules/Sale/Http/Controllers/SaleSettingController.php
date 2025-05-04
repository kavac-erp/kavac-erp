<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Rules\CodeSetting as CodeSettingRule;
use App\Models\CodeSetting;

/**
 * @class SaleSettingController
 * @brief Gestiona los datos de la configuración del módulo de comercialización
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleSettingController extends Controller
{
    use ValidatesRequests;

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
        $this->middleware('permission:sale.setting', ['only' => 'index']);
    }

    /**
     * Muestra la configuración del módulo de comercialización
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $codeSettings = CodeSetting::where('module', 'sale')->get();
        $pdCode = $codeSettings->where('table', 'sale_warehouse_inventory_products')->first();
        $mvCode = $codeSettings->where('table', 'sale_warehouse_movements')->first();
        $billCode = $codeSettings->where('table', 'sale_bills')->first();
        $serviceCode = $codeSettings->where('table', 'sale_services')->first();
        $saleOrderCode = $codeSettings->where('table', 'sale_orders')->first();
        $saleQuoteCode = $codeSettings->where('table', 'sale_quotes')->first();
        $saleWarehouseReportCode = $codeSettings->where('table', 'sale_warehouse_reports')->first();

        return view(
            'sale::settings',
            compact('codeSettings', 'pdCode', 'mvCode', 'billCode', 'serviceCode', 'saleOrderCode', 'saleQuoteCode', 'saleWarehouseReportCode')
        );
        //return view('sale::index');
    }

    /**
     * Muestra el formulario para la creación de un nuevo registro de configuración
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena la configuración del módulo de comercialización
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $this->validate($request, [
            'products_code' => [new CodeSettingRule()],
            'movements_code' => [new CodeSettingRule()],
            'bills_code' => [new CodeSettingRule()],
            'services_code' => [new CodeSettingRule()],
            'orders_code' => [new CodeSettingRule()],
            'quotes_code' => [new CodeSettingRule()],
            'warehouse_reports_code' => [new CodeSettingRule()]
        ]);

        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                if ($table === "products") {
                    /* Define la tabla asociado a los productos inventariados */
                    $table = "warehouse_inventory_products";

                    /* Define el modelo asociado a los productos inventariados */
                    $model = \Modules\Sale\Models\SaleWarehouseInventoryProduct::class;
                } elseif ($table === "movements") {
                    /* Define la tabla asociado a los moviemientos de almacén */
                    $table = "warehouse_movements";
                    /* Define el modelo para asociado a los movimientos de almacén */
                    $model = \Modules\Sale\Models\SaleWarehouseMovement::class;
                } elseif ($table === "bills") {
                    /* Define la tabla asociado a las facturas */
                    $table = "bills";
                    /* Define el modelo de las facturas */
                    $model = \Modules\Sale\Models\SaleBill::class;
                } elseif ($table === "services") {
                    /* Define la tabla asociado a las facturas */
                    $table = "services";
                    /* Define el modelo de las facturas */
                    $model = \Modules\Sale\Models\SaleService::class;
                } elseif ($table === "orders") {
                    /* Define la tabla asociada a los pedidos */
                    $table = "orders";
                    /* Define el modelo de los pedidos */
                    $model = \Modules\Sale\Models\SaleOrder::class;
                } elseif ($table === "quotes") {
                    /* Define la tabla asociada a las cotizaciones */
                    $table = "quotes";
                    /* Define el modelo de las cotizaciones */
                    $model = \Modules\Sale\Models\SaleQuote::class;
                } elseif ($table === "reports") {
                    /* Define la tabla asociado a los reportes */
                    $table = "warehouse_reports";
                    /* Define el modelo de los reportes */
                    $model = \Modules\Sale\Models\SaleWarehouseReport::class;
                }

                CodeSetting::updateOrCreate([
                    'module' => 'sale',
                    'table' => 'sale_' . $table,
                    'field' => $field,
                ], [
                    'format_prefix' => $prefix,
                    'format_digits' => $digits,
                    'format_year' => $sufix,
                    'model' => $model,
                ]);

                /* Define el estado verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->route('sale.settings.index');
    }

    /**
     * Muestra información de una configuración
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para la edición de una configuración
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información de una configuración
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
     * Elimina una configuración
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
