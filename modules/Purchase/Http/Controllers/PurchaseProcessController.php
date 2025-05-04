<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseProcess;
use App\Models\Parameter;

/**
 * @class      PurchaseProcessController
 * @brief      Controlador de la gestión de los procesos de compra
 *
 * @license   [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseProcessController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de elementos a mostrar
     *
     * @var array $data
     */
    protected $data = [];

    /**
     * Método constructor de la clase
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        $this->data[0] = [
            'id' => '',
            'text' => 'Seleccione...'
        ];
    }

    /**
     * Obtiene la lista de procesos de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseProcess::all()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_processes,name'],
            'description' => ['required']
        ]);

        $process = PurchaseProcess::create([
            'name' => $request->name,
            'description' => $request->description,
            'require_documents' => (count($request->list_documents) > 0),
            'list_documents' => (count($request->list_documents) > 0) ? json_encode($request->list_documents) : null
        ]);

        return response()->json(['record' => $process, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información sobre el proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar un proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza la información de un proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $this->validate($request, [
            'id' => ['required'],
        ]);

        $data = [
            'require_documents' => (count($request->list_documents) > 0),
            'list_documents' => (count($request->list_documents) > 0) ? json_encode($request->list_documents) : null
        ];

        if (!is_null($request->name)) {
            $data['name'] = $request->name;
        }
        if (!is_null($request->description)) {
            $data['description'] = $request->description;
        }

        $process = PurchaseProcess::updateOrCreate(['id' => $request->id], $data);

        return response()->json(['record' => $process, 'message' => 'Success'], 200);
    }

    /**
     * Elimina un proceso de compra
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(PurchaseProcess $purchaseProcess)
    {
        $purchaseProcess->delete();
        return response()->json(['record' => $purchaseProcess, 'message' => 'Success'], 200);
    }

    /**
     * Método que permite obtener un listado de procesos de compra ya registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     \Illuminate\Http\JsonResponse
     */
    public function getProcesses()
    {
        foreach (PurchaseProcess::all() as $process) {
            $this->data[] = [
                'id' => $process->id,
                'text' => $process->name
            ];
        }

        return response()->json($this->data);
    }

    /**
     * Método que permite obtener un listado de documentos a solicitar para los procesos de compra
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param      Request $request    Datos de la petición
     *
     * @return     \Illuminate\Http\JsonResponse
     */
    public function getProcessDocuments(Request $request)
    {
        $listDocuments = [];
        $process = null;
        $processDocuments = Parameter::where([
            'p_key' => 'process_documents',
            'required_by' => 'purchase',
            'active' => true,
        ])->first();
        if ($request->id) {
            $process = PurchaseProcess::find($request->id);
        }

        if (!is_null($processDocuments)) {
            foreach (json_decode($processDocuments->p_value) as $processDocument) {
                array_push($listDocuments, [
                    'id' => $processDocument->id,
                    'title' => $processDocument->title,
                    'documents' => $processDocument->documents
                ]);
            }
        }

        return response()->json([
            'records' => $listDocuments,
            'selected' => (!is_null($process) && !is_null($process->list_documents)) ? $process->list_documents : null
        ], 200);
    }
}
