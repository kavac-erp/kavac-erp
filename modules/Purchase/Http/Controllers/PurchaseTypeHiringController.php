<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseTypeHiring;

/**
 * @class PurchaseTypeController
 * @brief Controlador para gestionar los tipos de contrataciones
 *
 * Clase que gestiona los tipos de contrataciones
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */

class PurchaseTypeHiringController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra un listado de los tipos de contrataciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'records' => PurchaseTypeHiring::with('purchaseTypeOperation')->orderBy('id', 'ASC')->get()], 200);
    }

    /**
     * Muestra el formulario para registrar un nuevo tipo de contratación
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo tipo de contratación
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'date'                       => 'required|date',
            'purchase_type_operation_id' => 'required|integer',
            'ut'                         => 'required',
        ], [
            'date.required'                       => 'El campo fecha es obligatorio.',
            'date.date'                           => 'El campo fecha debe tener formato YYYY-MM-DD.',
            'purchase_type_operation_id.required' => 'El campo tipo es obligatorio.',
            'purchase_type_operation_id.integer'  => 'El campo tipo debe ser numerico.',
            'ut.required'                         => 'El campo unidades tributarias es obligatorio.',
        ]);
        if ($request->active) {
            $record_ant = PurchaseTypeHiring::where(
                'purchase_type_operation_id',
                $request->purchase_type_operation_id
            )->where('active', true)->first();
            if ($record_ant) {
                $record_ant->active = false;
                $record_ant->save();
            }
        }
        PurchaseTypeHiring::create($request->all());

        return response()->json([
            'records' => PurchaseTypeHiring::with('purchaseTypeOperation')->orderBy('id', 'ASC')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de contratación
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar un tipo de contratación
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza un tipo de contratación
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      ID del tipo de contratación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'date'                       => 'required|date',
            'purchase_type_operation_id' => 'required|integer',
            'ut'                         => 'required',
        ], [
            'date.required'                       => 'El campo fecha es obligatorio.',
            'date.date'                           => 'El campo fecha debe tener formato YYYY-MM-DD.',
            'purchase_type_operation_id.required' => 'El campo tipo es obligatorio.',
            'purchase_type_operation_id.integer'  => 'El campo tipo debe ser numerico.',
            'ut.required'                         => 'El campo unidades tributarias es obligatorio.',
        ]);
        if ($request->active) {
            $record_ant = PurchaseTypeHiring::where(
                'purchase_type_operation_id',
                $request->purchase_type_operation_id
            )->where('active', true)->first();
            if ($record_ant) {
                $record_ant->purchase_type_operation_id = $request->purchase_type_operation_id;
                $record_ant->active = false;
                $record_ant->save();
            }
        }

        $record                             = PurchaseTypeHiring::find($id);
        $record->date                       = $request->date;
        $record->active                     = $request->active;
        $record->purchase_type_operation_id = $request->purchase_type_operation_id;
        $record->ut                         = $request->ut;
        $record->save();
        return response()->json([
            'records' => PurchaseTypeHiring::with('purchaseTypeOperation')->orderBy('id', 'ASC')->get(),
            'message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de contratación
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        PurchaseTypeHiring::find($id)->delete();
        return response()->json([
            'records' => PurchaseTypeHiring::with('purchaseTypeOperation')->orderBy('id', 'ASC')->get(),
            'message' => 'Success'
        ], 200);
    }
}
