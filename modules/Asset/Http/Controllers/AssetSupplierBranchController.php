<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetSupplierBranch;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class      AssetSupplierBranchController
 * @brief      Controlador de Ramas de Proveedores
 *
 * Clase que gestiona las Ramas de los Proveedores
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierBranchController extends Controller
{
    use ValidatesRequests;

    /**
     * Obtiene un listado de las ramas de los proveedores
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => AssetSupplierBranch::all()], 200);
    }

    /**
     * Muestra el formulario para el registro de la rama de proveedor
     *
     * @return Renderable
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Almacena la infrmación de la rama del proveedor
     *
     * @param  Request $request Información de la petición
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_branches,name'],
        ]);

        $supplierBranch = AssetSupplierBranch::create([
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierBranch, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de la rama del proveedor
     *
     * @param  integer $id ID de la rama del proveedor
     *
     * @return Renderable
     */
    public function show($id)
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para la actualización de datos de la rama del proveedor
     *
     * @param  integer $id ID de la rama del proveedor
     *
     * @return Renderable
     */
    public function edit($id)
    {
        return view('asset::edit');
    }

    /**
     * Actualiza la información de la rama del proveedor
     *
     * @param  Request $request Información de la petición
     *
     * @return JsonResponse
     */
    public function update(Request $request)
    {
        /* Datos de la rama de proveedores */
        $supplierBranch = AssetSupplierBranch::find($request->id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_branches,name,' . $supplierBranch->id],
        ]);

        $supplierBranch->name = $request->name;
        $supplierBranch->description = $request->description;
        $supplierBranch->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina la rama del proveedor
     *
     * @param  Request $request Información de la petición
     * @param  integer $id ID de la rama del proveedor
     *
     * @return JsonResponse
     */
    public function destroy(Request $request, $id)
    {
        /* Datos de la rama de proveedores */
        $supplierBranch = AssetSupplierBranch::find($id);
        if ($supplierBranch) {
            $supplierBranch->delete();
        }
        return response()->json(['record' => $supplierBranch, 'message' => 'Success'], 200);
    }
}
