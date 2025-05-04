<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseSupplierObject;
use Modules\Purchase\Models\PurchaseSupplier;

/**
 * @class PurchaseSupplierObjectController
 * @brief Gestiona los procesos de los objetos de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierObjectController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra el listado de objetos de proveedores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseSupplierObject::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo objeto de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena un nuevo objeto de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
            'type' => ['required'],
            'name' => ['required', 'unique:purchase_supplier_objects,name'],
            ],
            [ 'type.required' => 'El campo tipo es obligatorio.']
        );

        $supplierObject = PurchaseSupplierObject::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierObject, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un objeto de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar un objeto de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza un objeto de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del objeto de proveedores */
        $supplierObject = PurchaseSupplierObject::find($id);

        $this->validate(
            $request,
            [
            'type' => ['required'],
            'name' => ['required', 'unique:purchase_supplier_objects,name,' . $supplierObject->id],
            ],
            [ 'type.required' => 'El campo tipo es obligatorio.']
        );

        $supplierObject->type = $request->type;
        $supplierObject->name = $request->name;
        $supplierObject->description = $request->description ?? null;
        $supplierObject->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un objeto de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos del objeto de proveedores */
        $supplierObject = PurchaseSupplierObject::find($id);
        $supplierObject->delete();
        return response()->json(['record' => $supplierObject, 'message' => 'Success'], 200);
    }

    /**
     * Retorna el obeto del proveedor
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID del proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPurchaseSupplierObject($id)
    {
        return response()->json(PurchaseSupplier::find($id)->PurchaseSupplierObject);
    }
}
