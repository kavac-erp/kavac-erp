<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleClientsEmail;

/**
 * @class SaleClientsEmailController
 * @brief Controlador que gestiona las notificaciones por correo a los clientes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleClientsEmailController extends Controller
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
        // $this->middleware('permission:sale.setting.email');
    }

    /**
     * Muestra el listado de notificaciones
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => []], 200);
    }

    /**
     * Datos del cliente al cual enviar la notificación
     *
     * @param integer $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function client($id)
    {
        return response()->json(['records' => SaleClientsEmail::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Registra los datos del correo del cliente
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'email' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);

        $id = $request->input('sale_client_id');

        $email = SaleClientsEmail::create([
            'email' => $request->input('email'),
            'sale_client_id' => $request->input('sale_client_id'),
        ]);

        return response()->json(['records' => SaleClientsEmail::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Actualiza los datos del correo del cliente
     *
     * @param  Request $request Datos de la petición
     * @param  SaleClientsEmail $email Registro a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, SaleClientsEmail $email)
    {
        $this->validate($request, [
            'email' => ['required', 'max:100'],
            'sale_client_id' => ['required'],
        ]);

        $email->email = $request->input('email');
        $email->sale_client_id = $request->input('sale_client_id');
        $email->save();

        $id = $email->sale_client_id;

        return response()->json(['records' => SaleClientsEmail::where('sale_client_id', '=', $id)->get()], 200);
    }

    /**
     * Elimina los datos del correo del cliente
     *
     * @param  SaleClientsEmail $email Registro a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(SaleClientsEmail $email)
    {
        $email->delete();
        return response()->json(['record' => $email, 'message' => 'Success'], 200);
    }
}
