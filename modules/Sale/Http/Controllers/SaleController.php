<?php

namespace Modules\Sale\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @class SaleController
 * @brief Controlador que gestiona los datos del módulo de comercialización
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleController extends Controller
{
    /**
     * Muestra el listado de registros
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('sale::index');
    }

    /**
     * Muestra el formulario para crear un registro
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('sale::create');
    }

    /**
     * Almacena un nuevo registro
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Muestra información de un registro
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('sale::show');
    }

    /**
     * Muestra el formulario para editar datos de un registro
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('sale::edit');
    }

    /**
     * Actualiza un registro
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina un registro
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
