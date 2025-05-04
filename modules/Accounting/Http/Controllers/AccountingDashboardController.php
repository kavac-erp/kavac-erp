<?php

namespace Modules\Accounting\Http\Controllers;

use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Accounting\Models\Profile;
use Modules\Accounting\Models\Currency;
use Illuminate\Contracts\Support\Renderable;
use Modules\Accounting\Models\AccountingEntry;
use Modules\Accounting\Models\AccountingReportHistory;

/**
 * @class AccountingDashboardController
 * @brief Controlador para el manejo del dashboard
 *
 * Clase que gestiona la informacion del dashboard de contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingDashboardController extends Controller
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
        /* Establece permisos de acceso para cada método del controlador */
        $this->middleware(
            'permission:accounting.dashboard',
            ['only' => ['index', 'get_operations', 'get_report_histories']]
        );
    }

    /**
     * Despliega la vista principal
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        return view('accounting::index_test');
    }

    /**
     * Obtiene las ultimas 10 operaciones de creacion de asientos contables realizadas
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function getOperations()
    {
        /* información de la modena por defecto establecida en la aplicación */
        $currency    = Currency::where('default', true)->first();
        /* información de los ultimos 10 asientos contables generados */
        $records = [];

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if ($user_profile && $user_profile->institution !== null && $user_profile['institution']['id']) {
            $records = AccountingEntry::with('accountingAccounts.account')
                        ->where('institution_id', $user_profile['institution']['id'])
                        ->orderBy('from_date', 'ASC')->get();
        } else {
            if (auth()->user()->isAdmin()) {
                $records = AccountingEntry::with('accountingAccounts.account')
                        ->orderBy('from_date', 'ASC')->get();
            }
        }
        return response()->json(['lastRecords' => $records, 'currency' => $currency], 200);
    }

    /**
     * Obtiene los registros de los ultimos reportes generados
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return JsonResponse
     */
    public function getReportHistories()
    {
        /* almacenara la informacion de los reportes */
        $report_histories = [];

        $reports = [];

        $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();

        if ($user_profile && $user_profile['institution']) {
            $institution  = get_institution($user_profile['institution']['id']);
        } else {
            $institution  = get_institution();
        }

        if ($user_profile && $user_profile->institution !== null && $user_profile['institution']['id']) {
            $reports = AccountingReportHistory::with('institution')
                        ->where('institution_id', $user_profile['institution']['id'])
                                            ->orderBy('updated_at', 'DESC')->get();
        } else {
            if (auth()->user()->isAdmin()) {
                $reports = AccountingReportHistory::with('institution')->orderBy('updated_at', 'DESC')->get();
            }
        }
        foreach ($reports as $report) {
            /*
            * Se calcula el intervalo de tiempo entre la fecha en la que se genero el reporte
            * y la fecha actual en semanas y dias
            */
            $datetime1 = new DateTime($report['updated_at']->format('Y-m-d'));
            $datetime2 = new DateTime(date("Y-m-d"));
            $interval = $datetime1->diff($datetime2);
            array_push($report_histories, [
                                 'id'         => $report['id'],
                                 'institution_name' => $institution['name'],
                                 'created_at' => $report['updated_at']->format('d/m/Y'),
                                 'name'       => $report['report'],
                                 'url'        => $report['url'],
                                 'interval'   => (floor(($interval->format('%a') / 7)) . ' semanas con ' .
                                                 ($interval->format('%a') % 7) . ' días'),
                                ]);
        }

        return response()->json(['report_histories' => $report_histories], 200);
    }
}
