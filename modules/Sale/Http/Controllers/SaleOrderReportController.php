<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class SaleOrderReportController
 * @brief Controlador que gestiona los reportes de ordenes de venta
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleOrderReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Muestra un listado de las ordenes registradas
     *
     * @return void
     */
    public function index()
    {
        //
    }

    /**
     * Muestra el listado de órdenes
     *
     * @return \Illuminate\View\View
     */
    public function listOrders()
    {
        return view('sale::reports.sale-report-order');
    }

    /**
     * Listado de órdenes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return void
     */
    public function vueList(Request $request)
    {
        //
    }

    /**
     * Muestra el formulario para registrar una nueva orden de venta
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Registra una nueva orden de venta
     *
     * @param Request $request Datos de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información sobre una orden de venta
     *
     * @param integer $id Identificador de la orden de venta
     *
     * @return void
     */
    public function show($id)
    {
        //
    }

    /**
     * Muestra el formulario para editar una orden de venta
     *
     * @param integer $id Identificador de la orden de venta
     *
     * @return void
     */
    public function edit($id)
    {
        //
    }

    /**
     * Actualiza la información de una orden de venta
     *
     * @param Request $request Datos de la petición
     * @param integer $id Identificador de la orden de venta
     *
     * @return void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina una orden de venta
     *
     * @param integer $id Identificador de la orden de venta
     *
     * @return void
     */
    public function destroy($id)
    {
        //
    }
}
