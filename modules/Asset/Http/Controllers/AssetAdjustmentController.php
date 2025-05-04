<?php

/** [descripción del namespace] */

namespace Modules\Asset\Http\Controllers;

use App\Models\Institution;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\Asset;
use Modules\Asset\Models\AssetAdjustmentAsset;
use Modules\Asset\Models\AssetBook;

/**
 * @class AssetAdjustmentController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAdjustmentController extends Controller
{
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return view('asset::adjustments.list');
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        //
    }
}
