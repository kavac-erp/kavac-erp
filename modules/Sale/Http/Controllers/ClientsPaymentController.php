<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SalePaymentMethod;

/**
 * @class ClientsPaymentController
 * @brief Gestiona los procesos del controlador de pagos de clientes
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ClientsPaymentController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     */

    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador/*
        $this->middleware('permission:sale.payment.method.list', ['only' => 'index']);
        $this->middleware('permission:sale.payment.method.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:sale.payment.method.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:sale.payment.method.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de tipos de personal
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function index()
    {
    }

    /**
     * Muestra el formulario para crear un pago de un cliente
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Valida y registra un nuevo pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Datos de la Solicitud
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'description' => ['required', 'max:200']
        ]);
        $salePaymentMethod = SalePaymentMethod::create([
            'name' => $request->name,'description' => $request->description
        ]);
        return response()->json(['record' => $salePaymentMethod, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un pago
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar un pago
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información del metodo de pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Datos de la solicitud
     * @param  integer $id                          Identificador del pago a actualizar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $salePaymentMethod = SalePaymentMethod::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'description' => ['nullable', 'max:200']
        ]);
        $salePaymentMethod->name  = $request->name;
        $salePaymentMethod->description = $request->description;
        $salePaymentMethod->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina el pago
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador del pago a eliminar
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $salePaymentMethod = SalePaymentMethod::find($id);
        $salePaymentMethod->delete();
        return response()->json(['record' => $salePaymentMethod, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los métodos de pago registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalePaymentMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SalePaymentMethod', 'name', '', true));
    }
}
