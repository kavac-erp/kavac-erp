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

class CitizenServiceReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function __construct()
    {
         /** Establece permisos de acceso para cada método del controlador */
         $this->middleware('permission:citizenservice.report.create', ['only' => 'create']);
         $this->middleware('permission:citizenservice.report.list', ['only' => 'index']);
    }
    public function index()
    {

        return view('citizenservice::reports.create');
    }
    public function request()
    {
        return view('citizenservice::reports.citizenservice-report-request');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {



        /** Para el PDF**/
        $user = Auth()->user();
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
                'citizenServiceIndicator',
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
     * Show the specified resource.
     * @return Renderable
     */
    public function show($filename)
    {
        $file = storage_path() . '/reports/' . $filename ?? 'citizenservice-report-' . Carbon::now() . '.pdf';

        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit()
    {
        return view('citizenservice::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Renderable
     */
    public function update(Request $request)
    {
    }

    /**
     * Remove the specified resource from storage.
     * @return Renderable
     */
    public function destroy()
    {
    }

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
