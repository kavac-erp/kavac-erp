<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleWarehouseInventoryProduct;
use Modules\Sale\Models\SaleWarehouseReport;
use Modules\Sale\Pdf\SaleReport;
use App\Models\Institution;
use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Carbon\Carbon;

/**
 * @class SaleReportController
 * @brief Gestiona los datos de los reportes de ventas
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra el listado de reportes de ventas
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::index');
    }

    /**
     * Muestra el listado del inventario de productos
     *
     * @return \Illuminate\View\View
     */
    public function inventoryProducts()
    {
        return view('sale::reports.sale-report-products');
    }

    /**
     * Obtiene el listado del inventario de productos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function vueList(Request $request)
    {
        if ($request->current == "inventory-products") {
            if ($request->sale_setting_product_id <= 0 && $request->institution_id <= 0 && $request->sale_warehouse_id <= 0) {
                $fields = SaleWarehouseInventoryProduct::where('id', 0);
            } else {
                $fields = SaleWarehouseInventoryProduct::with(
                    [
                        'currency',
                        'saleSettingProduct',
                        'saleWarehouseInventoryRule',
                        'saleWarehouseProductValues',
                        'saleWarehouseInstitutionWarehouse' => function ($query) {
                            $query->with('sale_warehouse');
                        },
                    ]
                )->orderBy('code');
            }
            if ($request->sale_setting_product_id > 0) {
                $fields = $fields->where(
                    'sale_setting_product_id',
                    $request->sale_setting_product_id
                );
            }
            if ($request->institution_id > 0) {
                $institutionsSaleWarehouses = DB::table('sale_warehouse_institution_warehouses')
                    ->where('institution_id', $request->institution_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'sale_warehouse_institution_warehouse_id',
                    $institutionsSaleWarehouses
                );
            }
            if ($request->sale_warehouse_id > 0) {
                $institutionsSaleWarehouses = DB::table('sale_warehouse_institution_warehouses')
                    ->where('sale_warehouse_id', $request->sale_warehouse_id)
                    ->select('id')->get()->pluck('id')->toArray();
                $fields = $fields->whereIn(
                    'sale_warehouse_institution_warehouse_id',
                    $institutionsSaleWarehouses
                );
            }
            if ($request->type_search == "date") {
                if (!is_null($request->start_date)) {
                    if (!is_null($request->end_date)) {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                $request->end_date
                            ]
                        );
                    } else {
                        $fields = $fields->whereBetween(
                            "created_at",
                            [
                                $request->start_date,
                                now()
                            ]
                        );
                    }
                }
            }
            if ($request->type_search == "mes") {
                if (!is_null($request->mes_id)) {
                    if (!is_null($request->year)) {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        )->whereYear('created_at', $request->year);
                    } else {
                        $fields = $fields->whereMonth(
                            'created_at',
                            $request->mes_id
                        );
                    }
                }
            }
        }

        return response()->json(['records' => $fields->get()], 200);
    }

    /**
     * Muestra el formulario para generar el reporte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $is_admin = auth()->user()->isAdmin();

        /* base para generar el pdf*/
        $pdf = new SaleReport();

        /* Definición de las caracteristicas generales de la página pdf */
        $institution = null;

        $filename = 'sale-inventory-products-report-' . Carbon::now() . '.pdf';

        $institution = Institution::where('default', true)
            ->where('active', true)->first();

        $codeSetting = CodeSetting::where('table', 'sale_warehouse_reports')->first();
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
            SaleWarehouseReport::class,
            $codeSetting->field
        );

        $report = SaleWarehouseReport::create([
            'code'           => $code,
            'type_report'    => $request->current,
            'institution_id' => $institution->id,
            'filename'       => $filename
        ]);

        $pdf->setConfig([
            'institution' => Institution::first(),
            'filename'    => $filename
        ]);
        $pdf->setHeader('Reporte de inventario de productos');
        $pdf->setFooter();
        $pdf->setBody('sale::pdf.inventory-products', true, [
            'pdf'         => $pdf,
            'inventory_products' => $request->sale_warehouse_products
        ]);

        $url = '/sale/reports/show/' . $report->code;
        return response()->json(['result' => true, 'redirect' => $url], 200);
    }

    /**
     * Almacena los datos de un reporte
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de un reporte
     *
     * @param string $code Código del reporte
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($code)
    {
        $report = SaleWarehouseReport::where('code', $code)->first();
        $file = storage_path() . '/reports/' . $report->filename ?? 'sale-inventory-products-report-' . Carbon::now() . '.pdf';

        return response()->download($file, $report->filename, [], 'inline');
    }

    /**
     * Muestra el formulario para editar un reporte
     *
     * @param integer $id Identificador del registro
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de un reporte
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador del registro
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina un reporte
     *
     * @param integer $id Identificador del registro
     *
     * @return void
     */
    public function destroy($id)
    {
        //
    }
}
