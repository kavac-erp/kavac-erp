<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleTypeGoodAttribute;

/**
 * @class SaleTypeGoodAttributeController
 * @brief Gestiona los datos de los atributos de tipos de bienes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleTypeGoodAttributeController extends Controller
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
     * Muestra un listado de los atributos de tipo de bienes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Listado de atributos por tipo de bien
     *
     * @param integer $id Identificador del tipo de bien
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function product($id)
    {
        return response()->json(['records' => SaleTypeGoodAttribute::where('sale_type_good_id', '=', $id)->get()], 200);
    }

    /**
     * Almacena los datos de un atributo de tipo de bien
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_type_good_id' => ['required'],
        ]);

        $id = $request->input('sale_type_good_id');

        $attribute = SaleTypeGoodAttribute::create([
            'name' => $request->input('name'),
            'sale_type_good_id' => $request->input('sale_type_good_id'),
        ]);

        return response()->json(['records' => SaleTypeGoodAttribute::where('sale_type_good_id', '=', $id)->get()], 200);
    }

    /**
     * Actualiza la información de un atributo de tipo de bien
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaleTypeGoodAttribute $attribute)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'sale_type_good_id' => ['required'],
        ]);

        $attribute->name = $request->input('name');
        $attribute->sale_type_good_id = $request->input('sale_type_good_id');
        $attribute->save();

        $id = $attribute->sale_type_good_id;

        return response()->json(['records' => SaleTypeGoodAttribute::where('sale_type_good_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina los datos de un atributo de tipo de bien
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaleTypeGoodAttribute $attribute)
    {
        $attribute->delete();
        return response()->json(['record' => $attribute, 'message' => 'Success'], 200);
    }
}
