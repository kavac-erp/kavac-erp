<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleGoodsAttribute;

/**
 * @class SaleGoodsAttributeController
 * @brief Controlador que gestiona los atributos de los bienes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleGoodsAttributeController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        // $this->middleware('permission:sale.setting.attribute');
    }

    /**
     * Listado de atributos de los bienes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Lista de atriburos de un bien
     *
     * @param integer $id Identificador del bien
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function product($id)
    {
        return response()->json(['records' => SaleGoodsAttribute::where('sale_goods_to_be_traded_id', '=', $id)->get()], 200);
    }

    /**
     * Almacena un nuevo atributo de un bien
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_goods_to_be_traded_id' => ['required'],
        ]);

        $id = $request->input('sale_goods_to_be_traded_id');

        $attribute = SaleGoodsAttribute::create([
            'name' => $request->input('name'),
            'sale_goods_to_be_traded_id' => $request->input('sale_goods_to_be_traded_id'),
        ]);

        return response()->json(['records' => SaleGoodsAttribute::where('sale_goods_to_be_traded_id', '=', $id)->get()], 200);
    }

    /**
     * Actualiza un atributo de un bien
     *
     * @param  Request $request Datos de la petición
     * @param SaleGoodsAttribute $attribute Atributo de un bien
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaleGoodsAttribute $attribute)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_goods_to_be_traded_id' => ['required'],
        ]);

        $attribute->name = $request->input('name');
        $attribute->sale_goods_to_be_traded_id = $request->input('sale_goods_to_be_traded_id');
        $attribute->save();

        $id = $attribute->sale_goods_to_be_traded_id;

        return response()->json(['records' => SaleGoodsAttribute::where('sale_goods_to_be_traded_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina un atributo de un bien
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaleGoodsAttribute $attribute)
    {
        $attribute->delete();
        return response()->json(['record' => $attribute, 'message' => 'Success'], 200);
    }
}
