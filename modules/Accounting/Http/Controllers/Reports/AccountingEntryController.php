<?php

namespace Modules\Accounting\Http\Controllers\Reports;

use Illuminate\Routing\Controller;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\Setting;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Institution;
use App\Repositories\ReportRepository;

/**
 * @class AccountingEntryController
 * @brief Controlador para la generación del reporte del asiento contable
 *
 * Clase que gestiona de la generación del reporte del asiento contable
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntryController extends Controller
{
    /**
     * Salto de página
     *
     * @var mixed $PageBreakTrigger
     */
    protected $PageBreakTrigger;

    /**
     * Define la configuración de la clase
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:accounting.entries.report', ['only' => 'pdf']);
    }

    /**
     * vista en la que se genera el reporte en pdf de balance de comprobación
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param integer $id id del asiento contable
     */
    public function pdf($id)
    {
        // Validar acceso para el registro
        $is_admin = auth()->user()->isAdmin();

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $entry = AccountingEntry::with(
                'accountingAccounts.account',
                'currency'
            )->where('institution_id', $user_profile['institution']['id'])->find($id);
        } else {
            $entry = AccountingEntry::with(
                'accountingAccounts.account',
                'currency'
            )->find($id);
        }
        if (!auth()->user()->isAdmin()) {
            if ($entry && $entry->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        /* configuración general de la apliación */
        $setting = Setting::all()->first();

        $OnlyOneEntry   = true;

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        if (!$is_admin && $user_profile && $user_profile['institution']) {
            $institution = Institution::find($user_profile['institution']['id']);
        } else {
            $institution = get_institution();
        }

        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url(' entries/pdf/' . $id)]);
        $pdf->setHeader('Reporte de Contabilidad', 'Reporte de asiento contable');
        $pdf->setFooter();
        $pdf->setBody('accounting::pdf.entry_and_daily_book', true, [
            'pdf'      => $pdf,
            'entry'    => $entry,
            'currency' => $entry->currency,
            'Entry'    => $OnlyOneEntry,
        ]);
    }

    /**
     * Devolver PageBreakTrigger
     *
     * @return mixed
     */
    public function getCheckBreak()
    {
        return $this->PageBreakTrigger;
    }
}
