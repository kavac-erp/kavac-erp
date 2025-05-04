<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Models\AssetRequestEvent;
use Modules\Asset\Models\Asset;
use App\Repositories\UploadDocRepository;

/**
 * @class      AssetRequestEventController
 * @brief      Controlador de eventos en bienes institucionales solicitados
 *
 * Clase que gestiona los eventos ocurridos a los bienes institucionales solicitados
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRequestEventController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.request.event.create', ['only' => 'store']);
        $this->middleware('permission:asset.request.event.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra un listado de las solicitudes de eventos de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>

     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetRequestEvent::all()], 200);
    }

    /**
     * Valida y registra un nuevo evento
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     \App\Repositories\UploadDocRepository $upDoc      Repositorio para la gestión de documentos
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request, UploadDocRepository $upDoc)
    {
        $this->validate($request, [
            'type'             => ['required', 'max:100'],
            'description'      => ['required'],
            'asset_request_id' => ['required'],
            'equipments'       => ['required']

        ], [], [
            'type'             => 'tipo de evento',
            'description'      => 'descripción',
        ]);
        if ($request->type == 2) {
            $this->validate($request, [
                'files.*' => ['required', 'mimes:doc,pdf,odt,docx']
            ]);
        }

        /* Objeto asociado al modelo AssetRequestEvent */
        $event = AssetRequestEvent::create([
            'type'             => $request->input('type'),
            'description'      => $request->input('description'),
            'asset_request_id' => $request->input('asset_request_id'),
            'ids_assets'       => $request->equipments
        ]);
        if ($request->type == 2) {
            /* Carga los documentos en el servidor */
            if (count($request->files) > 0) {
                foreach ($request->file('files') as $file) {
                    $upDoc->uploadDoc(
                        $file,
                        'documents',
                        AssetRequestEvent::class,
                        $event->id,
                        null,
                        false,
                        false,
                        true
                    );
                }
            }
        }

        $request->merge(['equipments' => json_decode($request->equipments)]);
        foreach ($request->equipments as $equipment) {
            $asset = Asset::find($equipment);
            /* Si se selecciona la opción averiado */
            if ($request->type == 1) {
                $asset->asset_condition_id = 4;
                $asset->asset_status_id = 5;
                $asset->save();
            } elseif ($request->type == 2) { /** Si se selecciona la opción perdido */
                $asset->asset_condition_id = 7;
                $asset->asset_status_id = 8;
                $asset->save();
            }
        }
        return response()->json(['record' => $event, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de las solicitudes de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único del evento
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $event = AssetRequestEvent::find($id);
        $this->validate($request, [
            'type' => ['required', 'max:100'],
            'description' => ['required'],
            'asset_request_id' => ['required']
        ]);

        $event->type = $request->input('type');
        $event->description = $request->input('description');
        $event->asset_request_id = $request->input('asset_request_id');
        $event->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un evento
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id         Identificador único del evento
     *
     * @return    \Illuminate\Http\JsonResponse                    Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        $event = AssetRequestEvent::find($id);

        if ($event->assets_event) {
            foreach ($event->assets_event as $equipment) {
                $equipment->asset_condition_id = 1;
                $equipment->asset_status_id = 1;
                $equipment->save();
            }
        }
        $event->delete();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Muestra un listado de los eventos pertenecientes a una solicitud de bienes institucionales da
     *
     * @author    Francisco J. P. Ruiz <javierrupe19@gmail.com>
     *
     * @param     integer                          $id         Identificador de la solicitud de bienes
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function show($id)
    {
        return response()->json(['records' => AssetRequestEvent::where('asset_request_id', $id)->get()], 200);
    }
}
