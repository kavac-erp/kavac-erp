<?php

/** [descripción del namespace] */

namespace Modules\Asset\Http\Controllers;

use Auth;
use App\Models\Profile;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Repositories\ReportRepository;
use Modules\Asset\Models\AssetInventory;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Http\Resources\AssetResource;
use Modules\Asset\Repositories\AssetParametersRepository;
use Modules\Asset\Http\Controllers\AssetSpecificCategoryController;

/**
 * @class AssetInventoryReportController
 * @brief Controlador para la emision de un pdf
 *
 * Clase que gestiona de la emision de un pdf
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
 * @copyright <a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *                LICENCIA DE SOFTWARE CENDITEL</a>
 */
class AssetInventoryReportController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
    }

        /**
     * vista en la que se genera la emisión de la factura en pdf
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve | exodiadaniel@gmail.com>
     * @param Int $id id de la factura
    */
    public function pdf($type, $code)
    {
        ini_set('max_execution_time', 3600);
        // Validar acceso para el registro
        $is_admin = auth()->user()->isAdmin();
        $assets = AssetInventory::where('code', $code)->with(['assetInventoryAssets' => function ($query) {
            $query->with(['asset' => function ($query) {
                $query->with(['institution', 'assetCondition', 'assetStatus',
                'assetAsignationAsset' => function ($query) {
                    $query->with(['assetAsignation' => function ($query) {
                        $query->with('payrollStaff');
                    }]);
                },
                'assetDisincorporationAsset' => function ($query) {
                    $query->with(['assetDisincorporation' => function ($query) {
                        $query->with('assetDisincorporationMotive');
                    }]);
                }]);
            }]);
        }])->get();

        // Crear resource para parsear información del PDF
        $collection = [];

        foreach ($assets as $asset) {
            foreach ($asset->assetInventoryAssets as $assetInventory) {
                $resource = new AssetResource($assetInventory->asset);
                $json = response()->json($resource);
                $content = json_decode($json->content());

                // Agregar información del nombre del asignado
                if (isset($assetInventory['asset']['assetAsignationAsset']['assetAsignation']['payroll_staff_id'])) {
                    $content->asignee_name = $assetInventory['asset']['assetAsignationAsset']['assetAsignation']['payrollStaff']['first_name'] .
                    ' ' .  $assetInventory['asset']['assetAsignationAsset']['assetAsignation']['payrollStaff']['last_name'];
                }
                // Agregar información del motivo de desincorporación
                if (isset($assetInventory['asset']['assetDisincorporationAsset']['assetDisincorporation'])) {
                    $content->disincorporation_motive = $assetInventory['asset']['assetDisincorporationAsset']['assetDisincorporation']['assetDisincorporationMotive']['name'];
                    $content->asset_status->name = $content->asset_status->name . ": " . $assetInventory['asset']['assetDisincorporationAsset']['assetDisincorporation']['assetDisincorporationMotive']['name'];
                }

                array_push($collection, $content);
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

        /*
         *  Definicion de las caracteristicas generales de la página pdf
         */
        if (auth()->user()->isAdmin()) {
            $institution = Institution::first();
        } else {
            $profile = Profile::where('user_id', auth()->user()->id)->first();
            $institution = Institution::find($profile->institution_id);
        }
        $pdf->setConfig(['institution' => Institution::first(), 'orientation' => 'L']);
        $pdf->setHeader('Reporte de Historial de Inventario de Bienes', $code);
        $pdf->setFooter(true, $institution->name);
        $pdf->setBody('asset::pdf.asset_inventario', true, [
            'pdf'         => $pdf,
            'assets' => $collection
        ]);
    }
}
