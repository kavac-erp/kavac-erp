<?php

namespace Modules\Purchase\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Purchase\Models\PurchaseSupplierBranch;

/**
 * @class PurchaseSupplierBranchController
 * @brief Gestiona los procesos de las ramas de proveedores
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierBranchController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra el listado de ramas de proveedores
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => PurchaseSupplierBranch::all()], 200);
    }

    /**
     * Muestra el formulario para registrar una nueva rama de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena una nueva rama de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_branches,name'],
        ]);

        $supplierBranch = PurchaseSupplierBranch::create([
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierBranch, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de una rama de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario para editar una rama de proveedor
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza la información de una rama de proveedor
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        /* Datos de la rama de proveedores */
        $supplierBranch = PurchaseSupplierBranch::find($request->id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_branches,name,' . $supplierBranch->id],
        ]);

        $supplierBranch->name = $request->name;
        $supplierBranch->description = $request->description;
        $supplierBranch->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina una rama de proveedor
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        /* Datos de la rama de proveedores */
        $supplierBranch = PurchaseSupplierBranch::find($id);
        if ($supplierBranch) {
            $supplierBranch->delete();
        }
        return response()->json(['record' => $supplierBranch, 'message' => 'Success'], 200);
    }
}
