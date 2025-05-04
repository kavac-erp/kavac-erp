<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseService;

/**
 * @class PurchaseServiceController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseServiceController extends Controller
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
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name' => ['required', 'max:300'],
            'description' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required' => 'El campo nombre es obligatorio.',
            'name.max' => 'El campo nombre no debe ser mayor que 300 caracteres.',
            'description.required' => 'El campo descripción es obligatorio.',
        ];
    }

    /**
     * Muestra el listado de servicios de compra
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return response()->json(['records' => PurchaseService::orderBy('id')->get()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo servicio de compra
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo servicio de compra
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        PurchaseService::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json(['records' => PurchaseService::orderBy('id')->get(), 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un servicio de compra
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar un servicio de compra
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza la información de un servicio de compra
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $purchaseService = PurchaseService::find($id);

        $purchaseService->name = $request->name;
        $purchaseService->description = $request->description;
        $purchaseService->save();

        return response()->json(['records' => PurchaseService::orderBy('id')->get(), 'message' => 'Success'], 200);
    }

    /**
     * Elimina un servicio de compra
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $purchaseService = PurchaseService::find($id);
        $purchaseService->delete();
        return response()->json(['records' => PurchaseService::orderBy('id')->get(), 'message' => 'Success'], 200);
    }
}
