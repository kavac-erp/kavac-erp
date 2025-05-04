<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetSupplierType;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class      AssetSupplierTypeController
 * @brief      Controlador de tipos de Proveedores de bienes
 *
 * Clase que gestiona los tipos de Proveedores de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Listado de tipos de proveedores de bienes
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => AssetSupplierType::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de proveedor de bienes
     *
     * @return Renderable
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Almacena un nuevo tipo de proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_types,name'],
        ]);

        $supplierType = AssetSupplierType::create([
            'name' => $request->name
        ]);

        return response()->json(['record' => $supplierType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de proveedor de bienes
     *
     * @return Renderable
     */
    public function show()
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para editar un tipo de proveedor de bienes
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('asset::edit');
    }

    /**
     * Actualiza los datos de un tipo de proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     * @param  int     $id     ID del tipo de proveedor de bienes
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del tipo de proveedores */
        $supplierType = AssetSupplierType::find($id);

        $this->validate($request, [
            'name' => ['required', 'unique:purchase_supplier_types,name,' . $supplierType->id],
        ]);

        $supplierType->name = $request->name;
        $supplierType->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina un tipo de proveedor de bienes
     *
     * @param  int $id ID del tipo de proveedor de bienes
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        /* Datos del tipo de proveedores */
        $supplierType = AssetSupplierType::find($id);
        $supplierType->delete();
        return response()->json(['record' => $supplierType, 'message' => 'Success'], 200);
    }
}
