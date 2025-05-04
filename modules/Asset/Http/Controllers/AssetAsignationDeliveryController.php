<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Profile;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use App\Repositories\ReportRepository;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Asset\Models\AssetAsignation;
use Modules\Asset\Models\AssetAsignationAsset;
use Modules\Asset\Models\AssetAsignationDelivery;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AssetAsignationDeliveryController
 * @brief      Controlador de las solicitudes de entrega de equipos asignados
 *
 * Clase que gestiona las solicitudes de entrega de equipos asignados
 *
 * @author     Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve / javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAsignationDeliveryController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.asignation.approvereject', ['only' => 'update']);
    }

    /**
     * Muestra un listado de las solicitudes de entrega bienes institucionales asignados
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> / <javierrupe19@gmail.com>
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetAsignationDelivery::with(['assetAsignation' => function ($query) {
            $query->with('payrollStaff', 'section');
        } , 'user'])->get()], 200);
    }

    /**
     * Valida y registra una nueva solicitud de entrega de bienes institucionales asignados
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> / <javierrupe19@gmail.com>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'asset_asignation_id' => ['required']
        ]);

        AssetAsignationDelivery::create([
            'state' => 'Pendiente',
            'asset_asignation_id' => $request->input('asset_asignation_id'),
            'user_id' => Auth::id(),
        ]);

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.asignation.index')], 200);
    }

    /**
     * Actualiza la información de las solicitudes de entrega de bienes institucionales asignados
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve> / <javierrupe19@gmail.com>
     *
     * @param     Request    $request     Datos de la petición
     * @param     integer    $id          Identificador del registro a actualizar
     *
     * @return    JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, ($request->state == 'Aprobado') ? [
            'state' => ['required'],
            'asset_asignation_id' => ['required'],
            'approved_by_id' => ['required'],
            'received_by_id' => ['required'],
        ] :
        [
            'state' => ['required'],
            'asset_asignation_id' => ['required'],
        ], [
            'approved_by_id.required' => 'El campo aprobado por es obligatorio',
            'received_by_id.required' => 'El campo recibido por es oblogatorio',
        ]);
        $delivery = AssetAsignationDelivery::find($id);

        $asset_asignation = AssetAsignation::find($request->asset_asignation_id);
        $asset_asignation->ids_assets = json_decode($asset_asignation->ids_assets);

        $delivery->approved_by_id = $request->input('approved_by_id');
        $delivery->received_by_id = $request->input('received_by_id');
        $delivery->state = $request->input('state');
        $delivery->observation = $request->input('observation');


        if ($request->state == 'Aprobado') {
            if (count($asset_asignation->ids_assets->assigned) == 0) {
                $asset_asignation->ids_assets->delivered = array_merge(
                    $asset_asignation->ids_assets->delivered,
                    $asset_asignation->ids_assets->possible_deliveries
                );

                $assets_assigned = AssetAsignationAsset::where('asset_asignation_id', $asset_asignation->id)
                                                        ->whereIn('asset_id', $asset_asignation->ids_assets->possible_deliveries)->get();
                foreach ($assets_assigned as $assigned) {
                    $asset = $assigned->asset;
                    $asset->asset_status_id = 10;
                    $asset->save();
                }
                //Se asiganan los ids. de los bienes entregados.
                $delivery->ids_assets = json_encode($asset_asignation->ids_assets->possible_deliveries);

                $asset_asignation->ids_assets->possible_deliveries = [];
                $asset_asignation->ids_assets = json_encode($asset_asignation->ids_assets);
                $asset_asignation->state = 'Entregados';
            } else {
                $assets_assigned = AssetAsignationAsset::where('asset_asignation_id', $asset_asignation->id)
                                                        ->whereIn('asset_id', $asset_asignation->ids_assets->possible_deliveries)->get();
                foreach ($assets_assigned as $assigned) {
                    $asset = $assigned->asset;
                    $asset->asset_status_id = 10;
                    $asset->save();
                }
                $asset_asignation->ids_assets->delivered = array_merge(
                    $asset_asignation->ids_assets->delivered,
                    $asset_asignation->ids_assets->possible_deliveries
                );
                $asset_asignation->state = 'Entrega parcial';

                //Se asiganan los ids. de los bienes entregados.
                $delivery->ids_assets = json_encode($asset_asignation->ids_assets->possible_deliveries);

                $asset_asignation->ids_assets->possible_deliveries = [];
                $asset_asignation->ids_assets = json_encode($asset_asignation->ids_assets);
            }
        } elseif ($request->state == 'Rechazado') {
            if (count($asset_asignation->ids_assets->delivered) > 0) {
                $asset_asignation->state = 'Entrega parcial';
                $asset_asignation->ids_assets->assigned = array_merge(
                    $asset_asignation->ids_assets->assigned,
                    $asset_asignation->ids_assets->possible_deliveries
                );
                $asset_asignation->ids_assets->possible_deliveries = [];
                $asset_asignation->ids_assets = json_encode($asset_asignation->ids_assets);
            } else {
                $asset_asignation->state = 'Asignado';
                $asset_asignation->ids_assets = null;
            }
        }
        $delivery->save();
        $asset_asignation->save();
        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.asignation.index')], 200);
    }

    /**
     * Método que genera el archivo del acta en formato pdf
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     integer         $id         Identificador único de la asignación
     *
     * @return    void
     */
    public function managePdf($id)
    {
        $delivery = AssetAsignationDelivery::where('id', $id)->with(['assetAsignation' => function ($query) {
            $query->with(['payrollStaff', 'institution' => function ($query) {
                $query->with(['fiscalYears' => function ($query) {
                        $query->where(['active' => true])->first();
                },
                    'municipality' => function ($query) {
                            $query->with('estate');
                    }]);
            }
                , 'assetAsignationAssets' =>
                function ($query) {
                    $query->with(
                        ['asset' => function ($query) {
                            $query->with(
                                'institution',
                                'assetType',
                                'assetCategory',
                                'assetSubcategory',
                                'assetSpecificCategory',
                                'assetAcquisitionType',
                                'assetCondition',
                                'assetStatus',
                                'assetUseFunction'
                            );
                        }]
                    );
                }]);
        }])->first()->toArray();

        $data = [];
        $data['action'] = 'Entrega';
        $data['institution'] = (
            $delivery['asset_asignation']['institution_id']
        ) ? $delivery['asset_asignation']['institution']['name'] : 'N/A';
        $data['estate'] = (
            $delivery['asset_asignation']['institution_id']
        ) ? $delivery['asset_asignation']['institution']['municipality']['estate']['name'] : 'N/A';
        $data['municipality'] = (
            $delivery['asset_asignation']['institution_id']
        ) ? $delivery['asset_asignation']['institution']['municipality']['name'] : 'N/A';
        $data['address'] = (
            $delivery['asset_asignation']['institution_id']
        ) ? strip_tags($delivery['asset_asignation']['institution']['legal_address']) : 'N/A';
        $data['fiscal_year'] = (
            $delivery['asset_asignation']['institution_id']
        ) ? $delivery['asset_asignation']['institution']['fiscal_years'][0]['year'] : 'N/A';

        $date = date_create($delivery['asset_asignation']['created_at']);

        $data['created_at'] = ($date) ? date_format($date, "d/m/Y") : 'N/A';
        $data['last_name'] = (
            $delivery['asset_asignation']['payroll_staff']
        ) ? $delivery['asset_asignation']['payroll_staff']['last_name'] : 'N/A';
        $data['first_name'] = (
            $delivery['asset_asignation']['payroll_staff']
        ) ? $delivery['asset_asignation']['payroll_staff']['first_name'] : 'N/A';
        $data['id_number'] = (
            $delivery['asset_asignation']['payroll_staff_id']
        ) ? $delivery['asset_asignation']['payroll_staff']['id_number'] : 'N/A';
        $data['department'] = (
            $delivery['asset_asignation']['payroll_staff_id']
        ) ? $delivery['asset_asignation']['payroll_staff']['payroll_employment']['department']['name'] : 'N/A';
        $data['payroll_position'] = (
            $delivery['asset_asignation']['payroll_staff_id']
        ) ? $delivery['asset_asignation']['payroll_staff']['payroll_employment']['payrollPosition']['name'] : 'N/A';
        $data['location_place'] = (
            $delivery['asset_asignation']['location_place']
        ) ? $delivery['asset_asignation']['location_place'] : 'N/A';
        $data['code'] = $delivery['asset_asignation']['code'];

        $date_delivered = date_create($delivery['created_at']);
        $data['delivered_at'] = ($date_delivered) ? date_format($date_delivered, "d/m/Y") : 'N/A';
        $data['observation'] = ($delivery['observation']) ?? 'N/A';

        $delivery['ids_assets'] = json_decode($delivery['ids_assets']);
        $data['assets'] = AssetAsignationAsset::where('asset_asignation_id', $delivery['asset_asignation_id'])
                                                ->whereIn('asset_id', $delivery['ids_assets'])
                                                ->with(['asset' => function ($query) {
                                                    $query->with(
                                                        'institution',
                                                        'assetType',
                                                        'assetCategory',
                                                        'assetSubcategory',
                                                        'assetSpecificCategory',
                                                        'assetAcquisitionType',
                                                        'assetCondition',
                                                        'assetStatus',
                                                        'assetUseFunction'
                                                    );
                                                }])->get()->toArray();


        $approved_by = PayrollStaff::where('id', $delivery['approved_by_id'])->first()->toArray();
        $received_by = PayrollStaff::where('id', $delivery['received_by_id'])->first()->toArray();

        $data['approved_by'] = $approved_by['first_name'] . ' ' .
                               $approved_by['last_name'] . ' - ' .
                               $approved_by['payroll_employment']['payrollPosition']['name'];
        $data['received_by'] = $received_by['first_name'] . ' ' .
                               $received_by['last_name'] . ' - ' .
                               $received_by['payroll_employment']['payrollPosition']['name'];


        $user_profile = $user_profile = Profile::with('institution')->where('user_id', auth()->user()->id)->first();
        $is_admin = $user_profile == null || $user_profile['institution_id'] == null ? true : false;

        /* base para generar el pdf */
        $pdf = new ReportRepository();

        /* Definicion de las caracteristicas generales de la página pdf */
        $institution = null;

        /* Definicion de las caracteristicas generales de la página pdf */
        if ($is_admin) {
            $institution = Institution::find($delivery['asset_asignation']['institution_id']);
        } else {
            $institution = Institution::find($user_profile['institution_id']);
        }

        /* Definición del Nombre y ruta del acata en pdf */
        $filename = 'acta-de-entrega-de-bienes-' . $data['code'] . '-' . $id . '.pdf';

        $pdf->setConfig(['institution' => $institution, 'filename' => $filename ,
                         'urlVerify' => url('/asset/asignations/deliveries-record-pdf/' . $id)]);
        $pdf->setHeader(
            'Bienes entregados',
            'Código: ' . $data['code'] . '-' . $id,
            false,
            false,
            'L',
            'C',
            'L'
        );
        $pdf->setFooter(true, $institution->name . ' - ' . strip_tags($institution->legal_address));
        $pdf->setBody('asset::pdf.asset_acta', true, [
            'pdf'       => $pdf,
            'request'    => $data,
        ]);
    }

    /**
     * Método que permite descargar el archivo del acta en pdf
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     string  $code   Código único del registro
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function download($code)
    {
        return response()->download(storage_path('reports/' . 'acta-de-entrega-de-bienes-' . $code . '.pdf'));
    }
}
