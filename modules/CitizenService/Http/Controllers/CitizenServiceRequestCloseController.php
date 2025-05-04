<?php

namespace Modules\CitizenService\Http\Controllers;

use DB;
use App\Models\Image;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use App\Repositories\UploadDocRepository;
use App\Repositories\UploadImageRepository;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\CitizenService\Models\CitizenServiceRequest;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class CitizenServiceRequestCloseController
 * @brief Controlador para el cierre de solicitudes de la oficina de atención al ciudadano
 *
 * Clase que gestiona el controlador para el cierre de solicitudes de la OAC
 *
 * @author Ing. Yenifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRequestCloseController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:citizenservice.requests.close.list', ['only' => ['index']]);
        $this->middleware('permission:citizenservice.requests.close', ['only' => ['store', 'update']]);
    }

    /**
     * Muestra el listado de cierre de las solicitudes de la OAC
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('citizenservice::requests.list');
    }

    /**
     * Muestra el formulario para crear una nuevo cierre de solicitud
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena los datos de una nuevo cierre de solicitud
     *
     * @param  Request $request Datos de la petición
     * @param  UploadImageRepository $upImage Repositorio para subir imagenes
     * @param  UploadDocRepository $upDoc Repositorio para subir documentos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, UploadImageRepository $upImage, UploadDocRepository $upDoc)
    {

        $citizenServiceRequest = CitizenServiceRequest::find($request->request_id);
        $this->validate($request, [

            'date_verification'    => ['required'],
            'file' => ['required', 'max:10000', 'mimes:jpeg,jpg,png,pdf,docx,doc,odt,mp4,avi'],
        ]);

        $documentFormat = ['doc', 'docx', 'pdf', 'odt'];
        $imageFormat    = ['jpeg', 'jpg', 'png'];
        $videoFormat    = ['mp4', 'avi'];
        $extensionFile  = $request->file('file')->getClientOriginalExtension();

        if (in_array($extensionFile, $documentFormat)) {
            if (
                $upDoc->uploadDoc(
                    $request->file('file'),
                    'documents',
                    CitizenServiceRequest::class,
                    $request->request_id,
                    null,
                    false,
                    false,
                    true
                )
            ) {
                     $citizenServiceRequest->date_verification = $request->date_verification;
                     $file_id = $upDoc->getDocStored()->id;
                     $file_url = $upDoc->getDocStored()->url;
                     $file_name = $upDoc->getDocName();

                     $citizenServiceRequest->save();

                return response()->json([
                    'result' => true,
                    'file_id' => $file_id,
                    'file_url' => $file_url,
                    'file_name' => $file_name
                ], 200);
            }
        } elseif (in_array($extensionFile, $imageFormat)) {
            if (
                $upImage->uploadImage(
                    $request->file('file'),
                    'pictures',
                    CitizenServiceRequest::class,
                    $request->request_id,
                    true
                )
            ) {
                     $file_id = $upImage->getImageStored()->id;
                     $file_url = $upImage->getImageStored()->url;
                     $file_name = $upImage->getImageName();

                return response()->json([
                        'result' => true,
                        'file_id' => $file_id,
                        'file_url' => $file_url,
                        'file_name' => $file_name
                ], 200);
            }
        }
        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Muestra el cierre de solicitud seleccionado
     *
     * @return BinaryFileResponse
     */
    public function show($filename)
    {
        if (Storage::disk('pictures')->exists($filename)) {
            $file = storage_path() . '/pictures/' . $filename;
        } elseif (Storage::disk('documents')->exists($filename)) {
            $file = storage_path() . '/documents/' . $filename;
        }

        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Muestra el formulario para editar un cierre de solicitud
     *
     * @param  Image $image Imagen del cierre de solicitud
     *
     * @return void
     */
    public function edit(Image $image)
    {
        //
    }

    /**
     * Actualiza los datos del cierre de solicitud
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id ID del cierre de solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $citizenServiceRequest = CitizenServiceRequest::find($id);
        $citizenServiceRequest->date_verification = $request->date_verification;
        $citizenServiceRequest->state = 'Culminado';

        $citizenServiceRequest->save();

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['result' => true, 'redirect' => route('citizenservice.request.index')], 200);
    }

    /**
     * Elimina un cierre de solicitud
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id ID del cierre de solicitud
     *
     * @return \Illuminate\Http\JsonResponse|void
     */
    public function destroy(Request $request, $id)
    {
        $image = Image::find($id);
        $doc = Document::find($id);
        if (isset($image)) {
            $file = $image->file;
            if (!is_null($file)) {
                if ($request->force_delete) {
                    $image->forceDelete();
                    if (Storage::disk((isset($request->store)) ? $request->store : 'pictures')->exists($file)) {
                        Storage::disk((isset($request->store)) ? $request->store : 'pictures')->delete($file);
                    }
                } else {
                    $image->delete();
                }

                return response()->json(['result' => true, 'message' => 'Success'], 200);
            }
        } elseif (isset($doc)) {
            $file = $doc->file;
            if (!is_null($file)) {
                if ($request->force_delete) {
                    $doc->forceDelete();
                    if (Storage::disk((isset($request->store)) ? $request->store : 'documents')->exists($file)) {
                        Storage::disk((isset($request->store)) ? $request->store : 'documents')->delete($file);
                    }
                } else {
                    $doc->delete();
                }

                return response()->json(['result' => true, 'message' => 'Success'], 200);
            }
        }
        if ((is_null($image)) && (!is_null($doc))) {
            return response()->json([
                'result' => false, 'message' => __('El archivo no existe o ya fue eliminado')
            ], 200);
        } elseif ((is_null($doc)) && (!is_null($image))) {
            return response()->json([
               'result' => false, 'message' => __('El archivo no existe o ya fue eliminado')
            ], 200);
        }
    }

    /**
     * Obtiene los documentos de solicitudes
     *
     * @param string|integer $id ID de la solicitud
     * @param mixed $all Define si se obtienen todos los registros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCitizenServiceRequestDocuments($id, $all = null)
    {
        if (is_null($all)) {
            $citizenServiceRequest = CitizenServiceRequest::where(['id' => $id, 'state' => 'Culminado'])
            ->with('documents', 'images')->first();
        } else {
            $citizenServiceRequest = CitizenServiceRequest::where(['id' => $id])
            ->with('documents', 'images')->first();
        }
        $docs = $citizenServiceRequest->documents ?? null;
        $images = $citizenServiceRequest->images ?? null;
        $records = [];
        if (isset($docs)) {
            if (isset($images)) {
                $records = $docs->merge($images);
            } else {
                $records = $docs;
            }
        } elseif (isset($images)) {
            if (isset($docs)) {
                $records = $images->merge($docs);
            } else {
                $records = $images;
            }
        }
        return response()->json(['records' => $records], 200);
    }
}
