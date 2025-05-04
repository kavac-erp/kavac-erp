<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleClientsPhone;

/**
 * @class SaleClientsPhoneController
 * @brief Controlador que gestiona la información de los telefonos de los clientes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleClientsPhoneController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        // $this->middleware('permission:sale.setting.phone');
    }

    /**
     * Muestra el listado de teléfonos de los clientes
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Obtiene los teléfonos de un cliente
     *
     * @param integer $id Identificador del cliente
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function client($id)
    {
        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Almacena los teléfonos de un cliente
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'phone' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);
        $id = $request->input('sale_client_id');

        $phone = SaleClientsPhone::create([
            'phone' => $request->input('phone'),
            'sale_client_id' => $request->input('sale_client_id'),
        ]);

        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Actualiza los teléfonos de un cliente
     *
     * @param  Request $request Datos de la petición
     * @param SaleClientsPhone $phone Teléfono a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaleClientsPhone $phone)
    {
        $this->validate($request, [
            'phone' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);

        $phone->phone = $request->input('phone');
        $phone->sale_client_id = $request->input('sale_client_id');
        $phone->save();

        $id = $phone->sale_client_id;

        return response()->json(['records' => SaleClientsPhone::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina un teléfono de un cliente
     *
     * @param SaleClientsPhone $phone Teléfono a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaleClientsPhone $phone)
    {
        $phone->delete();
        return response()->json(['record' => $phone, 'message' => 'Success'], 200);
    }
}
