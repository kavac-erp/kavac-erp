<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseSupplierType;

/**
 * @class PurchaseSupplierTypeController
 * @brief Gestiona los procesos de los tipos de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra una lista de tipos de proveedores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseSupplierType::all()], 200);
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
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_types,name'],
        ]);

        $supplierType = PurchaseSupplierType::create([
            'name' => $request->name
        ]);

        return response()->json(['record' => $supplierType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
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
        /* Datos del tipo de proveedores */
        $supplierType = PurchaseSupplierType::find($id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_types,name,' . $supplierType->id],
        ]);

        $supplierType->name = $request->name;
        $supplierType->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un tipo de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        /* Datos del tipo de proveedores */
        $supplierType = PurchaseSupplierType::find($id);
        $supplierType->delete();
        return response()->json(['record' => $supplierType, 'message' => 'Success'], 200);
    }
}
