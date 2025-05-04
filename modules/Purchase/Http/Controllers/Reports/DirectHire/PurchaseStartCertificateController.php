<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Http\Controllers\Reports\DirectHire;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use Modules\Purchase\Models\PurchaseDirectHire;
use Modules\Purchase\Models\FiscalYear;
use App\Models\Profile;
use App\Models\Institution;
use Auth;

/**
 * @class PurchaseStartCertificateController
 * @brief Controlador para la generación del reporte de acta de inicio de una contratacion directa
 *
 * Clase que gestiona de la generación del reporte de acta de inicio de una contratacion directa
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 * @copyright <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *                LICENCIA DE SOFTWARE CENDITEL</a>
 */
class PurchaseStartCertificateController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
    }

        /**
     * vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @param Int $id id del asiento contable
    */
    public function pdf($id)
    {

        // Validar acceso para el registro

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        $is_admin = $user_profile == null || $user_profile['institution_id'] == null ? true : false;

        if ($is_admin) {
            $purchaseDirectHire = PurchaseDirectHire::find($id);
        } else {
            $purchaseDirectHire = PurchaseDirectHire::where(
                'institution_id',
                $user_profile['institution_id']
            )->find($id);
        }

        if (!$is_admin) {
            if ($purchaseDirectHire && $purchaseDirectHire->queryAccess($user_profile['institution_id'])) {
                return view('errors.403');
            }
        }

        /**
         * [$pdf base para generar el pdf]
         * @var [Modules\Accounting\Pdf\Pdf]
         */
        $pdf = new ReportRepository();

        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        $institution = null;

        if ($is_admin) {
            $institution = Institution::find($purchaseDirectHire->institution_id);
        } else {
            $institution = Institution::find($user_profile['institution_id']);
        }

        $pdf->setConfig([
            'institution' => $institution,
            'urlVerify' => url('/purchase/direct_hire/start_certificate/pdf/' . $id)
        ]);
        $pdf->setHeader('ACTA DE INICIO', 'Acta de inicio N ' . $purchaseDirectHire->code);
        $pdf->setFooter();
        $pdf->setBody('purchase::pdf.direct_hire.purchase_direct_hire', true, [
            'pdf'    => $pdf,
            'record' => $purchaseDirectHire
        ]);
    }

    public function getCheckBreak()
    {
        //return $this->PageBreakTrigger;
    }
}
