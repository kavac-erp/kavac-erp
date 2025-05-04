<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleSettingProductType;

/**
 * @class SaleSettingProductTypeController
 * @brief Gestiona los datos de configuración de tipos de productos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleSettingProductTypeController extends Controller
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
        $this->middleware('permission:sale.setting.product.type', ['only' => 'index']);
    }

    /**
     * Muestra todos los registros de los tipos de productos
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleSettingProductType::all()], 200);
    }

    /**
     * Muestra el formulario para la creación de un nuevo tipo de producto
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Valida y registra un nuevo tipo de producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:sale_setting_product_types,name', 'regex:/([A-Za-z\s])\w+/u','max:100'],
        ]);
        $saleSettingProductType = SaleSettingProductType::create(['name' => $request->name]);
        return response()->json(['record' => $saleSettingProductType, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información del tipo de producto
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para la actualización de un tipo de producto
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información del tipo producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Datos de la petición
     * @param  integer $id                          Identificador del datos a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $saleSettingProduct = SaleSettingProductType::find($id);
        $this->validate($request, [
            'name' => ['required', 'unique:sale_setting_product_types,name', 'regex:/([A-Za-z\s])\w+/u','max:100'],
        ]);
        $saleSettingProduct->name  = $request->name;
        $saleSettingProduct->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el tipo de producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del producto a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $saleSettingProductType = SaleSettingProductType::with('saleSettingProduct')->find($id);
        $saleSettingProductType->delete();
        return response()->json(['record' => $saleSettingProductType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de productos registrados
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleSettingProductType()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleSettingProductType', 'name', '', true));
    }
}
