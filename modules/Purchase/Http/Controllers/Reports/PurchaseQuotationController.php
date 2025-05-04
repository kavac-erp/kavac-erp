<?php

namespace Modules\Purchase\Http\Controllers\Reports;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use App\Models\Institution;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\Pivot;

/**
 * @class PurchaseQuotationController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseQuotationController extends Controller
{
    /**
     * Genera el reporte de cotización base para su visualización
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    void
     */
    public function pdf($id)
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)
            ->where('default', true)
            ->first();
        }

        /* base para generar el pdf */
        $pdf = new ReportRepository();
        $records = PurchaseQuotation::with(
            'purchaseSupplier',
            'currency',
            'documents',
            'relatable.purchaseRequirementItem.purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.historyTax'
        )->find($id);

        // Enviando las cuentas presupuestarias de gastos al reporte PDF
        $records['base_budget'] = Pivot::where('recordable_type', PurchaseQuotation::class)
            ->where('recordable_id', $id)
            ->with('relatable')
            ->get();

        /* Definicion de las caracteristicas generales de la página pdf */
        $pdf->setConfig([
            'institution' => Institution::first(),
            'urlVerify'   => url('/purchase/quotation/pdf/' . $id)
        ]);

        $pdf->setHeader(
            'Reporte de cotización ' . $records->code,
            'Información de la cotización'
        );
        $pdf->setFooter();
        $pdf->setBody('purchase::pdf.quotation', true, [
            'pdf'    => $pdf,
            'records' => $records
        ]);
    }
}
