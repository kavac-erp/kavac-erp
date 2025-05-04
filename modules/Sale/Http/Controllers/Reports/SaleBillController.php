<?php

namespace Modules\Sale\Http\Controllers\Reports;

use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use App\Models\Institution;
use Modules\Sale\Models\SaleBill;

/**
 * @class SaleBillController
 * @brief Controlador para la emision de una factura
 *
 * Clase que gestiona de la emision de una factura
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleBillController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
    }

        /**
     * vista en la que se genera la emisión de la factura en pdf
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     *
     * @param integer $id id de la factura
    */
    public function pdf($id)
    {
        // Validar acceso para el registro

        $is_admin = auth()->user()->isAdmin();


        $sale_bills = SaleBill::where('state', 'Aprobado')->with(['SaleFormPayment', 'saleBillInventoryProduct' => function ($query) {
                        $query->with(['saleGoodsToBeTraded', 'currency', 'saleListSubservices', 'measurementUnit', 'historyTax',
                            'saleWarehouseInventoryProduct' => function ($q) {
                                $q->with('saleSettingProduct');
                            }]);
        }])->find($id);

        if (!auth()->user()->isAdmin()) {
            $user_profile = auth()->user()?->profile;
            if (isset($requirement) && $requirement && $requirement->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        /* Definicion de las caracteristicas generales de la página pdf */
        if (auth()->user()->isAdmin()) {
            $institution = Institution::first();
        } else {
            $institution = Institution::find($user_profile->institution->id);
        }

        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('/sale/reports/payment/pdf/' . $sale_bills->id)]);
        $pdf->setConfig(['institution' => Institution::first()]);
        $pdf->setHeader('Factura');
        $pdf->setFooter(true, $institution->rif . ' ' . $institution->legal_address);
        $pdf->setBody('sale::pdf.bills', true, [
            'pdf'         => $pdf,
            'sale_bills' => $sale_bills
        ]);
    }
}
