<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseType;

/**
 * @class PurchaseTypeController
 * @brief Controlador para gestionar los tipos de compras
 *
 * Clase que gestiona los tipos de compras
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                  => ['required', 'unique:purchase_types,name'],
            //'purchase_processes_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            [ 'name.required'   => 'El campo nombre es obligatorio.'],
            [ 'name.unique'     => 'El campo nombre ya existe.'],
            //[ 'purchase_processes_id.required' => 'El campo proceso de compra es obligatorio.']
        ];
    }

    /**
     * Muestra un listado de tipos de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseType::with('purchaseProcess')->orderBy('id')->get()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo tipo de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        PurchaseType::create([
            'name'                  => $request->name,
            'description'           => $request->description,
            'purchase_processes_id' => $request->purchase_processes_id,
            'documents_id'          => (!$request->documents_id) ? "" : json_encode($request->documents_id),
        ]);
        return response()->json(['records' => PurchaseType::with('purchaseProcess')->orderBy('id')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $purchase_types = [];
        $purchase_types_name = [];
        $PurchaseType = PurchaseType::find($id);

        if ($PurchaseType->documents) {
            foreach ($PurchaseType->documents as $docs) {
                $key = str_replace(" ", '_', $docs->name);
                $purchase_types[$key] = null;
                $purchase_types_name[$key] = $docs->name;
            }
        }
        return response()->json([
            'purchase_type' => $purchase_types, 'purchase_type_name' => $purchase_types_name
        ], 200);
    }

    /**
     * Muestra el formulario para editar un tipo de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza un tipo de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate(
            $request,
            [
            'name' => ['required', 'unique:purchase_types,name,' . $id],
            ],
            $this->messages
        );

        $record                        = PurchaseType::find($id);
        $record->name                  = $request->name;
        $record->description           = $request->description;
        $record->purchase_processes_id = $request->purchase_processes_id;
        $record->documents_id          = (!$request->documents_id) ? "" : json_encode($request->documents_id);
        $record->save();
        return response()->json(['records' => PurchaseType::with('purchaseProcess')->orderBy('id')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $type = PurchaseType::find($id);
        if ($type) {
            $type->delete();
        }
        return response()->json(['records' => PurchaseType::with('purchaseProcess')->orderBy('id')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Método que permite obtener un listado de las modalidades de compra ya registrados
     *
     * @author     Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     \Illuminate\Http\JsonResponse
     */
    public function getPurchaseType()
    {
        $purchase_types = [
            [
                'id' => '',
                'text' => 'Seleccione...',
            ]
        ];
        foreach (PurchaseType::all() as $purchase_type) {
            array_push($purchase_types, [
                'id' => $purchase_type->id,
                'text' => $purchase_type->name,
            ]);
        }

        return response()->json(['purchase_types' => $purchase_types], 200);
    }
}
