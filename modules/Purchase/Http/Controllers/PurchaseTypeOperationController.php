<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseTypeOperation;

/**
 * @class PurchaseTypeController
 * @brief Controlador para gestionar los tipos de operaciones
 *
 * Clase que gestiona los tipos de operaciones
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseTypeOperationController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra un listado de los tipos de operaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseTypeOperation::orderBy('id')->get()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de operación
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo tipo de operación
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ], [
            'name.required' => 'El campo nombre es obligatorio.',
        ]);

        PurchaseTypeOperation::create($request->all());
        return response()->json(['records' => PurchaseTypeOperation::orderBy('id')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Muestra información sobre el tipo de operación
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar el tipo de operación
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza un tipo de operación
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name'        => 'required',
        ], [
            'name.required'        => 'El campo nombre es obligatorio.',
        ]);

        $record                        = PurchaseTypeOperation::find($id);
        $record->name                  = $request->name;
        $record->description           = $request->description;
        $record->save();
        return response()->json([
            'records' => PurchaseTypeOperation::orderBy('id')->get(),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Elimina un tipo de operación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        PurchaseTypeOperation::find($id)->delete();
        return response()->json([
            'records' => PurchaseTypeOperation::orderBy('id')->get(),
            'message' => 'Success'
        ], 200);
    }

    /**
     * Obtiene el listado de los tipos de operaciones
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getRecords()
    {
        $records = template_choices('Modules\Purchase\Models\PurchaseTypeOperation', 'name', [], true);
        return response()->json(['records' => $records], 200);
    }
}
