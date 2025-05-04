<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Warehouse\Models\WarehouseProductAttribute;

/**
 * @class WarehouseProductAttributeController
 * @brief Controlador de los atributos de los productos de almacén
 *
 * Clase que gestiona los atributos de productos almacenables
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseProductAttributeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.setting.attribute');
    }

    /**
     * Lista de registros
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Atributos de un producto
     *
     * @param integer $id identificador del producto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function product($id)
    {
        return response()->json(['records' => WarehouseProductAttribute::where('product_id', '=', $id)->get()], 200);
    }

    /**
     * Almacena un nuevo atributo del producto
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'product_id' => ['required'],
        ]);

        $id = $request->input('product_id');

        $attribute = WarehouseProductAttribute::create([
            'name' => $request->input('name'),
            'product_id' => $request->input('product_id'),
        ]);

        return response()->json(['records' => WarehouseProductAttribute::where('product_id', '=', $id)->get()], 200);
    }

    /**
     * Actualiza un atributo del producto
     *
     * @param  Request $request datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WarehouseProductAttribute $attribute)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'product_id' => ['required'],
        ]);

        $attribute->name = $request->input('name');
        $attribute->product_id = $request->input('product_id');
        $attribute->save();

        $id = $attribute->product_id;

        return response()->json(['records' => WarehouseProductAttribute::where('product_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina un atributo del producto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(WarehouseProductAttribute $attribute)
    {
        $attribute->delete();
        return response()->json(['record' => $attribute, 'message' => 'Success'], 200);
    }
}
