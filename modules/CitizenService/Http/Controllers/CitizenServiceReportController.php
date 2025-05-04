<?php

namespace Modules\CitizenService\Http\Controllers;

use Modules\CitizenService\Pdf\CitizenServiceReport  as Report;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CodeSetting;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequest;
use Modules\CitizenService\Models\CitizenServiceReport;
use Elibyy\TCPDF\TCPDF as PDF;
use App\Models\Institution;
use App\Models\FiscalYear;
use Carbon\Carbon;
use Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class CitizenServiceReportController
 * @brief Controlador para los reportes de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para los reportes de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
         // Establece permisos de acceso para cada método del controlador
         $this->middleware('permission:citizenservice.report.create', ['only' => 'create']);
         $this->middleware('permission:citizenservice.report.list', ['only' => 'index']);
    }

    /**
     * Muestra el formulario para la consulta de reportes de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('citizenservice::reports.create');
    }

    /**
     * Solicita un reporte de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function request()
    {
        return view('citizenservice::reports.citizenservice-report-request');
    }

    /**
     * Muestra el formulario para la creación de un nuevo reporte de la OAC
     *
     * @return \illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        /* Para el PDF**/
        $user = auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        $pdf = new Report();

        $citizenservice = CitizenServiceRequest::with('phones')->Search(
            $request
        )->with([
                'citizenServiceRequestType',
                'citizenServiceIndicator.indicator',
                'parish.municipality.estate.country',
                'citizenServiceDepartment'
            ])->get();

        $filename = 'citizenservice-report-' . Carbon::now() . '.pdf';


        $body = 'citizenservice::pdf.citizenservice_general';

        $institution = Institution::find(1);

        $fiscal_year = FiscalYear::where('active', true)->first();

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify'   => url(''),
                'orientation' => 'L',
                'filename'    => $filename
            ]
        );

        $pdf->setHeader("Reporte de solicitudes");
        $pdf->setFooter(true, strip_tags($institution->legal_address));
        $pdf->setBody(
            $body,
            true,
            [
                'pdf'    => $pdf,
                'field' => $citizenservice,
                'institution' => $institution,
                'fiscal_year' => $fiscal_year['year'],
            ]
        );

        $url = '/citizenservice/report/show/' . $filename;

        return response()->json(['result' => true,'redirect' =>  $url], 200);
    }


    /**
     * Muestra el PDF del reporte de la OAC
     *
     * @return BinaryFileResponse
     */
    public function show($filename)
    {
        $file = storage_path() . '/reports/' . $filename ?? 'citizenservice-report-' . Carbon::now() . '.pdf';

        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Muestra el formulario para la edición de un reporte de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Actualiza el reporte de la OAC
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina el reporte de la OAC
     *
     * @return void
     */
    public function destroy()
    {
        //
    }

    /**
     * Busca registros de solicitudes
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $citizenservice = CitizenServiceRequest::Search(
            $request
        )->with([
            'citizenServiceRequestType'
        ])->get();

        return response()->json(['records' => $citizenservice], 200);
    }
}
