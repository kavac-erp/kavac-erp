<?php

namespace Modules\Asset\Http\Controllers;

use App\Models\Institution;
use Illuminate\Http\Request;
use Modules\Asset\Models\Asset;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetBook;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetAdjustmentAsset;

/**
 * @class AssetAdjustmentController
 * @brief Controlador de ajustes de bienes
 *
 * Clase que gestiona los ajustes de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAdjustmentController extends Controller
{
    /**
     * Método que regresa una vista con la lista de ajustes de bienes
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Devuelve la vista con la lista de ajustes de bienes
     */
    public function index()
    {
        return view('asset::adjustments.list');
    }

    /**
     * Método que regresa una vista para crear un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Devuelve la vista para crear un ajuste de bien
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Método que crea un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
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
     * Método que muestra un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    Devuelve la vista con la información del ajuste de bien
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * Método que muestra la vista para editar un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    Devuelve la vista para editar un ajuste de bien
     */
    public function edit($id)
    {
        $asset = Asset::find($id);
        $parameters['asset_type_id'] = false;
        $parameters['asset_category_id'] = false;
        $parameters['asset_subcategory_id'] = false;
        $parameters['asset_specific_category_id'] = true;

        if (isset(auth()->user()->profile) && isset(auth()->user()->profile->institution_id)) {
            $institution = Institution::where(['id' => auth()->user()->profile->institution_id])->first();
        } else {
            $institution = Institution::where(['active' => true, 'default' => true])->first();
        }

        return view('asset::adjustments.create', compact('asset', 'parameters', 'institution'));
    }

    /**
     * Método que actualiza un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     Request   $request   Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    JsonResponse    Devuelve la lista de ajustes de bienes
     */
    public function update(Request $request, $id)
    {
        $asset = Asset::find($id);

        $assetBook = AssetBook::create([
            'asset_id' => $asset->id,
            'amount' => $request->asset_total_value,
        ]);

        AssetAdjustmentAsset::create([
            'asset_id' => $asset->id,
            'asset_book_id' => $assetBook->id,
            'description' => $request->adjustment_description,
            'adjustment_value' => $request->asset_adjustment_value,
            'residual_value' => $request->asset_details[0]['residual_value'],
            'depresciation_years' => $request->asset_details[0]['depresciation_years']
        ]);

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['message' => 'Success', 'redirect' => route('asset.adjustment.index')], 200);
    }

    /**
     * Método que elimina un ajuste de bien
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function destroy($id)
    {
        //
    }
}
