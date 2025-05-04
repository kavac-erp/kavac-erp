<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingDeliveryStatus;
use Modules\ProjectTracking\Models\ProjectTrackingProduct;

/**
 * @class ProjectTrackingDeliveryStatusController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingDeliveryStatusController extends Controller
{
    use ValidatesRequests;

    /**
     * Obtiene todos los registros de estatus de entrega
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json([
            'records' => ProjectTrackingDeliveryStatus::all()
        ], 200);
    }

    /**
     * Muestra el formulario para un nuevo registro de estatus de entrega
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena un nuevo registro de estatus de entrega
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Muestra información del estatus de entrega
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
     * Muestra el formulario para editar el estatus de entrega
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
     * Actualiza un estatus de entrega
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
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
     * Elimina un estatus de entrega
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $deliveryStatus = ProjectTrackingDeliveryStatus::find($id);
        $deliveryStatus->delete();

        return response()->json(['record' => $deliveryStatus, 'message' => 'Success'], 200);
    }
}
