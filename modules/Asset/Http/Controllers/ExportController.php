<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Asset\Actions\Registers\ExportAssetAction;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class ExportController
 *
 * @brief Controlador de exportación de bienes
 *
 * Gestiona los procesos para la exportación de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ExportController extends Controller
{
    /**
     * Muestra el formulario para la exportación de datos de bienes
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    BinaryFileResponse
     */
    public function index(Request $request, ExportAssetAction $export)
    {
        return $export->invoke($request->input(), 'asset_registers');
    }

    /**
     * Muestra información del bien a exportar
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  integer  $id    Identificador del registro
     * @return    Renderable
     */
    public function show($id)
    {
        return view('asset::show');
    }
}
