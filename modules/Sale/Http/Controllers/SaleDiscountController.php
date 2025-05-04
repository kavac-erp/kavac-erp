<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleDiscount;

/**
 * @class SaleClientsEmailController
 * @brief Controlador que gestiona los descuentos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleDiscountController extends Controller
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
        $this->middleware('permission:sale.setting.discount', ['only' => 'index']);
    }

    /**
     * Listado de descuentos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleDiscount::all()], 200);
    }

    /**
     * Muestra el formulario para crear un descuento
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un descuento
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->saleDiscountValidate($request);

        $SaleDiscount = SaleDiscount::create(['name' => $request->name,'description' => $request->description, 'percent' => $request->percent]);
        return response()->json(['record' => $SaleDiscount, 'message' => 'Success'], 200);
    }

    /**
     * Validacion de los datos
     *
     * @author Ing. Jose Puentes <jpuentes@cenditel.gob.ve>
     *
     * @param     Request    $request Datos de la petición
     *
     * @return    void
     */
    public function saleDiscountValidate(Request $request)
    {
        $attributes = [
        'name' => 'Nombre del descuento',
        'description' => 'Descripción del descuento',
        'percent' => 'Porcentaje del descuento'
        ];
        $validation = [];
        $validation['name'] = ['required', 'max:100', 'unique:sale_discounts,name', 'regex:/([A-Za-z\s])\w+/u'];
        $validation['description'] = ['required', 'max:200'];
        $validation['percent'] = ['required', 'max:3'];
        $this->validate($request, $validation, [], $attributes);
    }

    /**
     * Muestra información de un descuento
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar un descuento
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza los datoss de un descuento
     *
     * @param  Request $request Datos de la petición
     * @param integer $id Identificador del descuento a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $SaleDiscount = SaleDiscount::find($id);

        $this->saleDiscountValidate($request);

        $SaleDiscount->name  = $request->name;
        $SaleDiscount->description = $request->description;
        $SaleDiscount->percent  = $request->percent;
        $SaleDiscount->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un descuento
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $SaleDiscount = SaleDiscount::find($id);
        $SaleDiscount->delete();
        return response()->json(['record' => $SaleDiscount, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los descuento registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleDiscount()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleDiscount', 'percent', '', true));
    }
}
