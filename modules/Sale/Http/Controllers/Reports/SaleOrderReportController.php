<?php

namespace Modules\Sale\Http\Controllers\Reports;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\CodeSetting;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Repositories\ReportRepository;
use Modules\Sale\Models\SaleOrder;
use App\Models\Institution;

/**
 * @class SaleOrderReportController
 * @brief Controlador de los reportes de Order
 *
 * Clase que gestiona la generacion de reportes de Pedidos
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleOrderReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de estatus para el reporte
     *
     * @var array $status_list
     */
    protected $status_list = ['rechazado' => 'Cancelado', 'pending' => 'Creado', 'aprobado' => 'Aprobado'];

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
        //$this->middleware('permission:warehouse.report.', ['only' => 'create']);
    }

    /**
     * Muestra un listado para generar el reporte de Orders
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::reports.sale-report-order');
    }

    /**
     * Obtiene la lista de orders en base a los filtros del usuario en el reporte
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param Request $request datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function orderSearch(Request $request)
    {
        $filter = $request->all();
        $orders = SaleOrder::select('*');
        if (isset($filter['filterDate']) && $filter['filterDate'] == 'specific') {
            if ($filter['dateIni'] != null && $filter['dateIni'] != '') {
                $orders->whereDate('created_at', '>=', $filter['dateIni']);
            }
            if ($filter['dateEnd'] != null && $filter['dateEnd'] != '') {
                $orders->whereDate('created_at', '<=', $filter['dateEnd']);
            }
        } elseif (isset($filter['filterDate']) && $filter['filterDate'] == 'general') {
            if (count($filter['year_init']) > 0) {
                $orders->where(function ($q) use ($filter) {
                    foreach ($filter['year_init'] as $year) {
                        $q->whereYear('created_at', '=', $year, 'or');
                    }
                });
            }
            if (count($filter['month_init']) > 0) {
                $orders->where(function ($q) use ($filter) {
                    foreach ($filter['month_init'] as $month) {
                        $q->whereMonth('created_at', '=', $month, 'or');
                    }
                });
            }
        }
        if (isset($filter['status']) && $filter['status'] != null && $filter['status'] != '') {
            $orders->where('status', $filter['status']);
        }
        if (isset($filter['clients']) && count($filter['clients']) > 0) {
            $orders->whereIn('id_number', $filter['clients']);
        }
        $records = $orders->get();
        foreach ($records as $key => $record) {
            $products = [];
            $total = 0;
            $records[$key]->status_text = '';
            if (isset($record->status) && !empty($record->status)) {
                $records[$key]->status_text = isset($this->status_list[$record->status]) ? $this->status_list[$record->status] : $record->status;
            }
            if (!empty($record->products)) {
                $products_load = json_decode($record->products, true);
                foreach ($products_load as $id => $row) {
                    $products[] = [
                    'id' => $id,
                    'name' => $row['inventory_product']['name'],
                    'quantity' => $row['quantity'],
                    'price_product' => $row["total_without_tax"],
                    'iva' => $row["product_tax_value"],
                    'total' => $row['total'],
                    'moneda' => $row['currency']['name']
                    ];
                    $total += $row['total'];
                }
            }
            $records[$key]->products = $products;
            $records[$key]->total = $total;
        }
        return response()->json(['records' => $records], 200);
    }

    /**
     * Genera el archivo PDF en base a los pedidos seleccionados por el usuario
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @param array|string $values Lista de ids a consultar
     *
     * @return void
     */
    public function pdf($values = [])
    {
        $orders = SaleOrder::select('*');
        if ($values != '') {
            $ids = explode('+', $values);
            if (count($ids)) {
                $orders->whereIn('id', $ids);
            }
        }
        $records = $orders->get();
        foreach ($records as $key => $record) {
            $products = [];
            $total = 0;
            $total_without_tax = 0;
            $records[$key]->status_text = 'normal';
            if (isset($record->status) && !empty($record->status)) {
                $records[$key]->status_text = isset($this->status_list[$record->status]) ? $this->status_list[$record->status] : $record->status;
            }
            if (!empty($record->products)) {
                $products_load = json_decode($record->products, true);
                foreach ($products_load as $id => $row) {
                    $products[] = [
                    'id' => $id,
                    'name' => $row['inventory_product']['name'],
                    'quantity' => $row['quantity'],
                    'price_product' => $row["total_without_tax"],
                    'iva' => $row["product_tax_value"],
                    'total' => $row['total'],
                    'moneda' => $row['currency']['name']
                    ];
                    $total += $row['total'];
                    $total_without_tax += $row['total_without_tax'];
                }
            }
            $records[$key]->products = $products;
            $records[$key]->total = $total;
            $records[$key]->total_without_tax = $total_without_tax;
        }
        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;
        $is_admin = auth()->user()->isAdmin();
        $user_profile = auth()->user()?->profile;

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $institution = Institution::find($user_profile['institution']['id']);
        } else {
            $institution = '';
        }

        $pdf->setConfig(['institution' => $institution ?? Institution::first()]);
        $pdf->setHeader('Reporte de Pedidos');
        $pdf->setFooter();
        $pdf->setBody('sale::order.orders-pdf', true, [
          'pdf' => $pdf,
          'orders' => $records
        ]);
    }
}
