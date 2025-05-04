<?php

/** Controladores de reportes del modulo de compras */

namespace Modules\Purchase\Http\Controllers\Reports;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use App\Models\Institution;
use Modules\Purchase\Models\PurchaseBaseBudget;

/**
 * @class PurchaseBaseBudgetController
 * @brief Controlador para la generación del reporte del presupuesto base
 *
 * Clase que gestiona de la generación del reporte del presupuesto base
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBaseBudgetController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Genera el reporte de presupuesto base para su visualización
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function generatePdf($id)
    {
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        $record = PurchaseBaseBudget::with(
            'currency',
            'purchaseRequirement.preparedBy.payrollStaff',
            'purchaseRequirement.reviewedBy.payrollStaff',
            'purchaseRequirement.verifiedBy.payrollStaff',
            'purchaseRequirement.firstSignature.payrollStaff',
            'purchaseRequirement.secondSignature.payrollStaff',
            'purchaseRequirement.contratingDepartment',
            'purchaseRequirement.userDepartment',
            'relatable.purchaseRequirementItem.purchaseRequirement',
            'relatable.purchaseRequirementItem.measurementUnit',
            'relatable.purchaseRequirementItem.historyTax',
            'preparedBy.payrollStaff',
            'reviewedBy.payrollStaff',
            'verifiedBy.payrollStaff',
            'firstSignature.payrollStaff',
            'secondSignature.payrollStaff'
        )->find($id);

        /* Definicion de las caracteristicas generales de la página pdf */
        $pdf->setConfig(
            [
                'institution' => Institution::first(),
                //'reportDate' => date("d-m-Y", strtotime($record->date)),
                'urlVerify'   => url('/purchase/base-budget/pdf/' . $id)
            ]
        );

        $pdf->setHeader(
            'Reporte de Presupuesto Base ' . ((isset($requirement) ? $requirement['code'] : '') ?? '')
        );
        $pdf->setFooter();
        $pdf->setBody('purchase::pdf.base-budget', true, [
            'pdf'    => $pdf,
            'record' => $record
        ]);
    }
}
