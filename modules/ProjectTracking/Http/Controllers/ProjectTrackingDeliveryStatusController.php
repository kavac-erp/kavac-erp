<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;

/**
 * @class ProjectTrackingDeliveryStatusController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingDeliveryStatusController extends Controller
{
    use ValidatesRequests;

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json([
            'records' => ProjectTrackingDeliveryStatus::all()
        ], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:project_tracking_delivery_statuses,name'],
            'color' => ['required', 'unique:project_tracking_delivery_statuses,color'],
            'status' => ['required', 'unique:project_tracking_delivery_statuses,status'],
        ], [], [
            'status' => 'estatus'
        ]);

        $delivery_status = ProjectTrackingDeliveryStatus::create([
            'name' => $request->name,
            'color' => $request->color,
            'description' => $request->description,
            'status' => $request->status,
        ]);

        return response()->json(['delivery_status' => $delivery_status], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('projecttracking::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('projecttracking::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => ['required', 'unique:project_tracking_delivery_statuses,name,' . $id],
            'color' => ['required', 'unique:project_tracking_delivery_statuses,color,' . $id],
            'status' => ['required', 'unique:project_tracking_delivery_statuses,status,' . $id],
        ], [], [
            'status' => 'estatus'
        ]);

        $deliveryStatus = ProjectTrackingDeliveryStatus::find($id);
        $deliveryStatus->name = $request->name;
        $deliveryStatus->color = $request->color;
        $deliveryStatus->status = $request->status;
        $deliveryStatus->description = $request->description;
        $deliveryStatus->save();

        return response()->json([$deliveryStatus], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $deliveryStatus = ProjectTrackingDeliveryStatus::find($id);
        $deliveryStatus->delete();

        return response()->json(['record' => $deliveryStatus, 'message' => 'Success'], 200);
    }
}
