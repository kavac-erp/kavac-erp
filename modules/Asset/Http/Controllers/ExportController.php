<?php

/** [descripción del namespace] */

namespace Modules\Asset\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Actions\Registers\ExportAssetAction;

/**
 * @class ExportController
 *
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ExportController extends Controller
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
    public function index(Request $request, ExportAssetAction $export)
    {
        return $export->invoke($request->input(), 'asset_registers');
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param  int  $id    Identificador del registro
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('asset::show');
    }
}
