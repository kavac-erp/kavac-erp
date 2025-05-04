<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @class SalePaymentReportController
 * @brief Gestiona los procesos del controlador
 *
 * Reporte de pagos.
 *
 * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SalePaymentReportController extends Controller
{
    /**
     * Muestra el listado de reportes de pago
     *
     * @return    void
     */
    public function index()
    {
        //
    }

    /**
     * Listado de pagos
     *
     * @return \Illuminate\View\View
     */
    public function listPayment()
    {
        return view('sale::reports.sale-report-payment');
    }

    /**
     * Muestra el formulario para registrar un nuevo pago
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena un nuevo pago
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('sale::edit');
    }

    /**
     * Actualiza la información de un pago
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    void
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Elimina un pago
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    void
     */
    public function destroy($id)
    {
        //
    }
}
