<?php

namespace Modules\Purchase\Http\Controllers\Reports;

use Auth;
use App\Models\Profile;
use App\Models\Institution;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use Modules\Purchase\Models\PurchaseRequirement;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class PurchaseRequirementController
 * @brief Controlador para la generación del reporte de requerimiento
 *
 * Clase que gestiona de la generación del reporte de requerimiento
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseRequirementController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
    }

    /**
     * vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param integer $id id del asiento contable
     *
     * @return BinaryFileResponse|\Illuminate\View\View|void
     */
    public function pdf($id)
    {
        // Validar acceso para el registro

        $is_admin = auth()->user()->isAdmin();

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $requirement = PurchaseRequirement::with(
            'contratingDepartment',
            'userDepartment',
            'fiscalYear',
            'purchaseSupplierObject',
            'purchaseRequirementItems.measurementUnit',
            'preparedBy.payrollStaff',
            'reviewedBy.payrollStaff',
            'verifiedBy.payrollStaff',
            'firstSignature.payrollStaff',
            'secondSignature.payrollStaff'
        )->find($id);

        if (!auth()->user()->hasPermission('purchase.requirements.list')) {
            return view('errors.403');
        }

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $institution = Institution::find($user_profile['institution']['id']);
        } else {
            $institution = '';
        }

        $pdf->setConfig(
            [
                'institution' => Institution::first(),
                'urlVerify' => url('/purchase/purchase_requirement/pdf/' . $id),
            ]
        );

        $pdf->setHeader('Reporte de requerimiento ' . $requirement->code);
        $pdf->setFooter();
        $pdf->setBody('purchase::pdf.requirements', true, [
            'pdf' => $pdf,
            'requirement' => $requirement,
        ]);
    }

    /**
     * Verifica la condición de salto de página
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        //return $this->PageBreakTrigger;
    }
}
