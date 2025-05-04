<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Sale\Models\SaleOrderManagement;

/**
 * @class SaleOrderManagementController
 * @brief Controlador que gestiona los pedidos
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleOrderManagementController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return void
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
     * Muestra todos los registros de gestión de pedidos
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => SaleOrderManagement::all()], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo registro de gestión de pedidos
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena un nuevo registro de gestión de pedidos
     *
     * @param Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'cedule' => ['required', 'max:20'],
            'type' => ['required', 'max:200'],
            'code' => ['required', 'max:200'],
            'category' => ['required', 'max:200'],
            'quantity' => ['required', 'max:50']
        ]);
        $SaleOrderManagement = SaleOrderManagement::create([
            'name' => $request->name, 'cedule' => $request->cedule, 'type' => $request->type, 'code' => $request->type, 'category' => $request->category, 'quantity' => $request->quantity
        ]);
        return response()->json(['record' => $SaleOrderManagement, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un pedido
     *
     * @param integer $id Identificador del pedido
     *
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Muestra el formulario para editar un registro de gestión de pedidos
     *
     * @param integer $id Identificador del pedido
     *
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Actualiza un registro de gestión de pedidos
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador del pedido
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $SaleOrderManagement = SaleOrderManagement::find($id);
        $this->validate($request, [
            'name' => ['required', 'max:100'],
            'cedule' => ['required', 'max:20'],
            'type' => ['required', 'max:200'],
            'code' => ['required', 'max:200'],
            'category' => ['required', 'max:200'],
            'quantity' => ['required', 'max:50']
        ]);
        $SaleOrderManagement->name  = $request->name;
        $SaleOrderManagement->cedule = $request->cedule;
        $SaleOrderManagement->type = $request->type;
        $SaleOrderManagement->code = $request->code;
        $SaleOrderManagement->category = $request->category;
        $SaleOrderManagement->quantity = $request->quantity;
        $SaleOrderManagement->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un pedido
     *
     * @param integer $id Identificador del pedido
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $SaleOrderManagement = SaleOrderManagement::find($id);
        $SaleOrderManagement->delete();
        return response()->json(['record' => $SaleOrderManagement, 'message' => 'Success'], 200);
    }
    /**
     * Obtiene los datos de pedidos
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSaleOrderManagementMethod()
    {
        return response()->json(template_choices('Modules\Sale\Models\SaleOrderManagement', 'name', '', true));
    }
}
