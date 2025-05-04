<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Repositories\UploadDocRepository;

/**
 * @class DocumentController
 * @brief Gestiona información de Documentos
 *
 * Controlador para gestionar Documentos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DocumentController extends Controller
{
    /**
     * Listado de todos los documentos registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function index()
    {
        //
    }

    /**
     * Muestra el formulario para la creación de documentos
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Registra información de un nuevo documento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author     William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Request    $request    Objeto con información de la petición
     * @param     UploadDocRepository    $up         Objeto con las propiedades necesarias para registrar y subir
     *
     * @return    JsonResponse      JSON con información del resultado en el registro del documento
     */
    public function store(Request $request, UploadDocRepository $up)
    {
        $document_ids = [];
        if ($request->documents) {
            foreach ($request->file('documents') as $document) {
                if ($up->uploadDoc($document, 'documents')) {
                    $document_ids[] = array('id' => $up->getDocStored()->id);
                }
            }
            return response()->json(['result' => true, 'document_ids' => $document_ids], 200);
        }
        return response()->json(['result' => false, 'message' => __('No se pudo subir el(los) documento(s)')], 200);
    }

    /**
     * Muestra información de un documento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Document    $document    Objeto con información del documento a mostrar
     *
     * @return    Response
     */
    public function show(Document $document)
    {
        if (!$document->digital_file) {
            $fileContent = $document->url;
        } else {
            $fileContent = $document->digital_file;
        }

        // Determinar el tipo MIME del archivo
        $mimeType = mime_content_type($document->url);

        return response($fileContent)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'attachment; filename="' . $document->file . '.' . $document->extension . '"');
    }

    /**
     * Muestra el formulacio con información del documento a actualizar
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Document    $document    Objeto con información del documento a actualizar
     *
     * @return    void
     */
    public function edit(Document $document)
    {
        //
    }

    /**
     * Actualiza la información de un documento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param     Request     $request     Objeto con información de la petición
     * @param     Document    $document    Objeto con información del documento a actualizar
     *
     * @return void
     */
    public function update(Request $request, Document $document)
    {
        //
    }

    /**
     * Elimina un documento
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author     William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param     Document    $document    Objeto con información del documento a eliminar
     * @param     integer     $id         Identificador del documento a eliminar
     *
     * @return    JsonResponse     JSON con información del resultado en la eliminación del documento
     */
    public function destroy(Request $request, $id)
    {
        // Objeto con información del documento a eliminar
        $document = Document::find($id);

        if (is_null($document)) {
            return response()->json([
                'result' => false, 'message' => __('El documento no existe o ya fue eliminado')
            ], 200);
        }

        // Ruta del archivo a eliminar
        $file = $document->file;

        DB::transaction(function () use ($document, $file, $request) {
            if ($request->force_delete) {
                $document->forceDelete();
                if (Storage::disk((isset($request->store)) ? $request->store : 'documents')->exists($file)) {
                    Storage::disk((isset($request->store)) ? $request->store : 'documents')->delete($file);
                }
            } else {
                $document->delete();
            }
        });
        return response()->json(['result' => true, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene detalles de un documento
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @author  William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @param  Request    $request    Datos de la petición
     * @param  Document   $document   Objeto con los datos del documento
     *
     * @return JsonResponse             JSON con los detalles del documento consultado
     */
    public function getDocument(Request $request, Document $document)
    {
        return response()->json(['result' => true, 'document' => $document], 200);
    }
}
