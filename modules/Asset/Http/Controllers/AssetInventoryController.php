<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\CodeSetting;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\Asset;
use Modules\Asset\Models\AssetInventory;
use Modules\Asset\Models\AssetInventoryAsset;

/**
 * @class      AssetInventoryController
 * @brief      Controlador del historico de inventario de bienes institucionales
 *
 * Clase que gestiona el historico del inventario de bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetInventoryController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.inventory.history.index', ['only' => 'index']);
    }

    /**
     * Muestra un listado de los inventarios registrados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('asset::inventories.list');
    }

    /**
     * Valida y registra el estado actual del inventario
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $codeSetting = CodeSetting::where('table', 'asset_inventories')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar',
            ]);
            return response()->json(['result' => false, 'redirect' => route('asset.setting.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            AssetInventory::class,
            $codeSetting->field
        );

        /* Objeto asociado al modelo AssetInventory */
        $inventory = AssetInventory::create([
            'code' => $code,
        ]);
        $assets = Asset::with(
            'assetCondition',
            'assetStatus',
            'assetUseFunction',
            'assetAsignationAsset',
            'assetDisincorporationAsset',
            'assetRequestAsset'
        )->withTrashed()->get();

        $registered = count($assets);
        $assigned = $disincorporated = $reserved = 0;

        foreach ($assets as $asset) {
            if (($asset->asset_status_id == 1) && ($asset->assetAsignationAsset != null)) {
                $assigned++;
            } elseif (($asset->asset_status_id == 6) && ($asset->assetRequestAsset != null)) {
                $reserved++;
            } elseif (($asset->asset_status_id == 11) && ($asset->assetDisincorporationAsset != null)) {
                $disincorporated++;
            }

            $inventory_asset = AssetInventoryAsset::create([
                'asset_condition' => ($asset->assetCondition) ? $asset->assetCondition->name : null,
                'asset_status' => ($asset->assetStatus) ? $asset->assetStatus->name : null,
                'asset_use_function' => ($asset->assetUseFunction) ? $asset->assetUseFunction->name : null,
                'asset_id' => $asset->id,
                'asset_inventory_id' => $inventory->id,
            ]);
        }
        $inventory->registered = $registered;
        $inventory->assigned = $assigned;
        $inventory->disincorporated = $disincorporated;
        $inventory->reserved = $reserved;
        $inventory->save();

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.inventory-history.index')], 200);
    }

    /**
     * Elimina un registro de inventario
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                                 $id    Identificador único del registro a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        $inventory = AssetInventory::find($id);
        $inventory->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Otiene un listado de las inventarios registradas
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList()
    {
        $inventories = AssetInventory::with('assetInventoryAssets')->get();

        return response()->json(['records' => $inventories], 200);
    }
}
