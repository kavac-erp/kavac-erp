<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @class ProjectTrackingController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingController extends Controller
{
    /**
     * Listado de registros
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('projecttracking::index');
    }

    /**
     * Muestra el formulario para un nuevo registro
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena un nuevo registro
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
     * Muestra información del registro
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario para editar
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\View\View
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza un registro
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
     * Elimina un registro
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
