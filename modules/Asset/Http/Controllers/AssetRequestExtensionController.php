<?php

/**Revisar */

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Auth;
use Modules\Asset\Models\AssetRequestExtension;
use Modules\Asset\Models\AssetRequest;
use Modules\Asset\Rules\DateExtension;

/**
 * @class      AssetRequestExtensionController
 * @brief      Controlador de prorrogas de entrega en bienes institucionales solicitados
 *
 * Clase que gestiona las prorrogas de entrega solicitadas
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRequestExtensionController extends Controller
{
    use ValidatesRequests;

    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:asset.request.extension', ['only' => 'store']);
        $this->middleware('permission:asset.request.extension.approved', ['only' => 'approved']);
        $this->middleware('permission:asset.request.extension.rejected', ['only' => 'rejected']);
    }
    /**
     * Muestra un listado de las solicitudes de de prorroga de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetRequestExtension::all()], 200);
    }

    /**
     * Valida y registra una nueva prorroga
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'asset_request_id' => ['required']
        ]);
        $asset_request = AssetRequest::find($request->asset_request_id);


        $prorroga = new AssetRequestExtension();
        $prorroga->delivery_date = $request->delivery_date;
        $prorroga->asset_request_id = $request->asset_request_id;
        $prorroga->state = 'Pendiente';
        $prorroga->user_id = Auth::id();
        $prorroga->save();


        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('asset.request.index')], 200);
    }

    /**
     * Show the specified resource.
     * @return Renderable
     */
    public function show()
    {
        return view('asset::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @return Renderable
     */
    public function edit()
    {
        return view('asset::edit');
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

    public function vuePendingList()
    {
        $assetRequestExtension = AssetRequestExtension::with('user', 'assetRequest')->where('state', 'Pendiente')->get();
        $asset_request_exten_code = [];
        foreach ($assetRequestExtension as $assetRequestExten) {
            $assetRequestExten->code = $assetRequestExten->assetRequest->code;
            array_push($asset_request_exten_code, $assetRequestExten);
        }

        return response()->json(
            [
                'records'  => $asset_request_exten_code,
            ],
            200
        );
    }

    public function approved(Request $request, $id)
    {
        $request_prorroga = AssetRequestExtension::find($id);
        $request_prorroga->state = 'Aprobado';

        $asset_request = $request_prorroga->assetRequest;
        $asset_request->delivery_date = $request_prorroga->delivery_date;
        $asset_request->save();

        $request_prorroga->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.request.index')], 200);
    }

    public function rejected(Request $request, $id)
    {
        $asset_request = AssetRequestExtension::find($id);
        $asset_request->state = 'Rechazado';
        $asset_request->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('asset.request.index')], 200);
    }
}
