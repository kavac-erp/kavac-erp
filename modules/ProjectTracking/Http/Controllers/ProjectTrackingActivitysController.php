<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Modules\ProjectTracking\Models\ProjectTrackingActivity;

/**
 * @class ProjectTrackingActivitysController
 * @brief Gestiona los procesos del controlador
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivitysController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Pedro Buitragp <pbuitrago@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'orden'                               => ['required'],
            'name_activity'                       => ['required'],
            'project_tracking_project_types_id'   => ['required'],
            'project_tracking_type_products_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name_activity.required'                               => 'El campo nombre de la actividad  es obligatorio.',
            'orden.required'                              => 'El campo orden es obligatorio.',
            'project_tracking_project_types_id.required'  => 'El campo tipo de Proyecto es obligatorio.',
            'project_tracking_type_products_id.required' => 'El campo tipo de producto es obligatorio.',
        ];
    }

    /**
     * Retorna una lista de todas las actividades
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    JsonResponse    Devuelve todas las actividades registradas
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'records' => ProjectTrackingActivity::all(),
        ], 200);
    }

    /**
     * Muestra el formulario para crear una nueva actividad
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena una nueva actividad
     *
     * @author    Pedro Buitrago <pbuitrago@cednditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    JsonResponse    Actividad creada
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $activity = ProjectTrackingActivity::create([
            'orden' => $request->input('orden'),
            'name_activity' => $request->input('name_activity'),
            'description' => $request->input('description'),
            'project_tracking_type_products_id'  => $request->input('project_tracking_type_products_id'),
            'project_tracking_project_types_id'  => $request->input('project_tracking_project_types_id')
        ]);

        return response()->json(['record' => $activity, 'message' => 'Success'], 200);
    }

    /**
     * Muestra la información de una actividad
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
     * Muestra el formulario de edición de una actividad
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
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
     * Actualiza la información de una actividad
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    JsonResponse    Actividad actualizada
     */
    public function update(Request $request, $id): JsonResponse
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $product = ProjectTrackingActivity::find($request->input('id'));
        $product->orden = $request->input('orden');
        $product->name_activity = $request->input('name_activity');
        $product->description = $request->input('description');
        $product->project_tracking_type_products_id = $request->input('project_tracking_type_products_id');
        $product->project_tracking_project_types_id = $request->input('project_tracking_project_types_id');
        $product->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina una actividad
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    JsonResponse    Actividad eliminada
     */
    public function destroy($id): JsonResponse
    {
        $product = ProjectTrackingActivity::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Retorna un json con todas las actividades para ser usado en un componente <select2>
     *
     * @author    Pedro Contreras <pdrocont@gmail.com>
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getActivities(): JsonResponse
    {
        $activitiesList = ProjectTrackingActivity::all();
        $activities = [];
        array_push($activities, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($activitiesList->all() as $activity) {
            array_push($activities, [
                'id' => $activity->id,
                'text' => $activity->name_activity
            ]);
        }
        return response()->json($activities, 200);
    }

    /**
     * Obtiene las actividades por tipo de producto
     *
     * @param integer $product_type_id Identificador del tipo de producto
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityesByProductType($product_type_id): JsonResponse
    {
        $activitiesList = ProjectTrackingActivity::query()
            ->filterActivityesByProductType($product_type_id)
            ->get();
        $activities = [];
        array_push($activities, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        foreach ($activitiesList as $activity) {
            array_push($activitiesList, [
                'id' => $activity->id,
                'text' => $activity->name_activity,
            ]);
        }
        return response()->json($activities, 200);
    }

    /**
     * Obtiene las actividades por tipos de productos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     * @param string $product_type_ids Identificadores de los tipos de productos
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActivityesByProductTypes(Request $request, ?string $product_type_ids): JsonResponse
    {
        $productTypeIdArray = explode(',', $product_type_ids);
        $activitiesList = [];
        $activitiesList = ProjectTrackingActivity::query()
            ->filterActivityesByProductTypes($productTypeIdArray)
            ->get();
        $activities = [];
        array_push($activities, [
            'id' => '',
            'text' => 'Seleccione...',
        ]);
        foreach ($activitiesList as $activity) {
            $activities[] = [
                'id' => $activity->id,
                'text' => $activity->name_activity,
            ];
        }
        return response()->json($activities, 200);
    }
}
