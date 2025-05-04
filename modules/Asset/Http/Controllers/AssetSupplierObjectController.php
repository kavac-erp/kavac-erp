<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetSupplier;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetSupplierObject;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class      AssetSupplierObjectController
 * @brief      Controlador de objetos de Proveedores de bienes
 *
 * Clase que gestiona los objetos de Proveedores de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierObjectController extends Controller
{
    use ValidatesRequests;

    /**
     * Lista de objetos de proveedores de bienes
     *
     * @return JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => AssetSupplierObject::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo objeto de proveedor de bienes
     *
     * @return Renderable
     */
    public function create()
    {
        return view('asset::create');
    }

    /**
     * Almacena un nuevo objeto de proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     *
     * @return JsonResponse
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

        $supplierObject = AssetSupplierObject::create([
            'type' => $request->type,
            'name' => $request->name,
            'description' => $request->description ?? null
        ]);

        return response()->json(['record' => $supplierObject, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los detalles de un objeto de proveedor de bienes
     *
     * @return Renderable
     */
    public function show()
    {
        return view('asset::show');
    }

    /**
     * Muestra el formulario para editar un objeto de proveedor de bienes
     *
     * @return Renderable
     */
    public function edit()
    {
        return view('asset::edit');
    }

    /**
     * Actualiza los datos de un objeto de proveedor de bienes
     *
     * @param  Request $request Datos de la petición
     * @param  integer $id      ID del objeto de proveedor de bienes
     *
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        /* Datos del objeto de proveedores */
        $supplierObject = AssetSupplierObject::find($id);

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
     * Elimina un objeto de proveedor de bienes
     *
     * @param  integer $id ID del objeto de proveedor de bienes
     *
     * @return JsonResponse
     */
    public function destroy($id)
    {
        /* Datos del objeto de proveedores */
        $supplierObject = AssetSupplierObject::find($id);
        $supplierObject->delete();
        return response()->json(['record' => $supplierObject, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el objeto de un proveedor
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @param  integer $id ID del proveedor
     *
     * @return JsonResponse
     */
    public function getAssetSupplierObject($id)
    {
        return response()->json(AssetSupplier::find($id)->AssetSupplierObject);
    }
}
