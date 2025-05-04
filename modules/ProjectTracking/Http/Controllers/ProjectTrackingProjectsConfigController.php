<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

/**
 * @class ProjectTrackingProjectsConfigController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProjectsConfigController extends Controller
{
    /**
     * Lista de configuraciónes de proyectos
     *
     * @return    \Illuminate\View\View
     */
    public function index()
    {
        return view('projecttracking::index');
    }

    /**
     * Muestra el formulario de configuración de proyectos
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena los datos de una nueva configuración de proyecto
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
     * Muestra información de una configuración de proyecto
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
     * Muestra el formulario de edición de una configuración de proyecto
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
     * Actualiza los datos de una configuración de proyecto
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
     * Elimina los datos de una configuración de proyecto
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
