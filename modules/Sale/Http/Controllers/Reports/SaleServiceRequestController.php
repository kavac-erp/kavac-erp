<?php

namespace Modules\Sale\Http\Controllers\Reports;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Sale\Models\Profile;
use Modules\Sale\Models\Institution;
use App\Repositories\ReportRepository;
use Auth;
use Modules\Sale\Models\SaleService;

/**
 * @class SaleServiceRequestController
 * @brief Gestiona los procesos del controlador
 *
 * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleServiceRequestController extends Controller
{
    /**
     * Muestra el listado de solicitudes de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::reports.sale-report-service-request');
    }

    /**
     * Muestra el formulario para crear una nueva solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena la nueva solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información sobre una solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar una solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [description de los datos devueltos]
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de una solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina una solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function destroy($id)
    {
        //
    }

    /**
     * Filtra los registros de una solicitud de servicio
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param     Request    $request    Informacion de la consulta
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function filterRecords(Request $request)
    {
        $filter = $request->all();

        $records = SaleService::with(['SaleServiceRequirement','saleClient', 'payrollStaff']);
        if ($filter['filterDate'] == 'specific') {
            if ($filter['dateIni'] != null && $filter['dateEnd'] != null) {
                $records->whereDate('created_at', '>=', $filter['dateIni'])->whereDate('created_at', '<=', $filter['dateEnd']);
            }
        } elseif ($filter['filterDate'] == 'general') {
            if ($filter['year_init'] != null && $filter['year_end'] != null && $filter['month_init'] != null && $filter['month_end'] != null) {
                $records->whereYear('created_at', '>=', $filter['year_init'])->whereYear('created_at', '<=', $filter['year_end'])
                        ->whereMonth('created_at', '>=', $filter['month_init'])->whereMonth('created_at', '<=', $filter['month_end']);
            }
        }

        if ($filter['status'] != null && $filter['status'] != 'Todos') {
            $records->where('status', $filter['status']);
        }
        return response()->json([
            'records' => $records->get(),
            'message' => 'success'], 200);
    }

    /**
     * Genera Pdf
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param    array|string    $value    Listado de identificadores
     */
    public function pdf($value = [])
    {
        $listIds = json_decode($value);
        // Validar acceso para el registro
        if (!auth()->user()->isAdmin()) {
            $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
            if (isset($report) && $report && $report->queryAccess($user_profile['institution']['id'])) {
                return view('errors.403');
            }
        }

        $service_requests = SaleService::with(
            'saleClient.saleClientsPhone',
            'saleClient.saleClientsEmail',
            'saleServiceRequirement',
            'payrollStaff'
        )->whereIn('id', $listIds)->get()->toArray();

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definición de las características generales de la página pdf */
        if (auth()->user()->isAdmin()) {
            $institution = Institution::first();
        } else {
            $institution = Institution::find($user_profile->institution->id);
        }
        $pdf->setConfig(['institution' => $institution, 'urlVerify' => url('/sale/reports/service-requests/pdf/' . $value)]);
        $pdf->setHeader('Reporte de comercialización', 'Reporte de solicitudes de servicios');
        $pdf->setFooter(true, $institution->rif . ' ' . $institution->legal_address);
        $pdf->setBody('sale::pdf.service-requests', true, [
            'pdf'      => $pdf,
            'records'  => $service_requests,
        ]);
    }
}
