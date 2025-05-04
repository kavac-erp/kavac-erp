<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @class BudgetController
 * @brief Controlador principal del módulo de Presupuesto
 *
 * Clase que gestiona información del módulo de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role:budget|admin');
    }

    /**
     * Muestra el listado de presupuestos
     *
     * @return Renderable
     */
    public function index()
    {
        return view('budget::index');
    }

    /**
     * Muestra el formulario para crear un presupuesto
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena la información del presupuesto
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
     * Muestra detalles de un presupuesto
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar un presupuesto
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza la información de un presupuesto
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
     * Elimina un presupuesto
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
