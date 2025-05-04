<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingStaffClassification;

/**
 * @class StaffClassificationController
 * @brief Controlador de clasificación del personal
 *
 * Clase que gestiona las clasificaciones del personal
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingStaffClassificationController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        // $this->middleware('permission:projecttracking.staff.classifications.list', ['only' => 'index']);
        // $this->middleware('permission:projecttracking.staff.classifications.create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:projecttracking.staff.classifications.edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:projecttracking.staff.classifications.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra todos los registros de la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la clasificación del personal
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingStaffClassification::all()], 200);
    }

    /**
     * Muestra el formulario para registrar una nueva clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Valida y registra una nuevo clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request $request    Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => ['required', 'max:100', 'unique:project_tracking_staff_classifications,name'],
            'description' => ['nullable', 'max:200']
        ]);
        $ProjectTrackingStaffClassification = ProjectTrackingStaffClassification::create([
            'name' => $request->name,'description' => $request->description
        ]);
        return response()->json(['record' => $ProjectTrackingStaffClassification, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de la clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('projecttracking::show');
    }

    /**
     * Muestra el formulario para actualizar la clasificación del personal
     *
     * @return \Illuminate\View\View
     */
    public function edit(ProjectTrackingStaffClassification $staff_classification)
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la información de la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request   Solicitud con los datos a actualizar
     * @param  integer $id                          Identificador de la clasificación del personal a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $ProjectTrackingStaffClassification = ProjectTrackingStaffClassification::find($id);
        $this->validate($request, [
            'name' => [
                'required', 'max:100', 'unique:project_tracking_staff_classifications,name,' . $ProjectTrackingStaffClassification->id
            ],
            'description' => ['nullable', 'max:200']
        ]);
        $ProjectTrackingStaffClassification->name  = $request->name;
        $ProjectTrackingStaffClassification->description = $request->description;
        $ProjectTrackingStaffClassification->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la clasificación del personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @param  integer $id                      Identificador de la clasificación del personal a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $ProjectTrackingStaffClassification = ProjectTrackingStaffClassification::find($id);
        $ProjectTrackingStaffClassification->delete();
        return response()->json(['record' => $ProjectTrackingStaffClassification, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene la clasificación del personal registradas
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de la clasificación del personal
     */
    public function getProjectTrackingStaffClassifications()
    {
        return response()->json(
            template_choices('Modules\ProjectTracking\Models\ProjectTrackingStaffClassification', 'name', '', true)
        );
    }

    /**
     * Retorna un json con todos los roles para ser usado en un componente <select2>
     *
     * @author    Pedro Contreras <pdrocont@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getStaffClassifications()
    {
        $staff_classificationsList = ProjectTrackingStaffClassification::all();
        $staff_classifications = [];
        array_push($staff_classifications, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($staff_classificationsList->all() as $staff_classification) {
            array_push($staff_classifications, [
                'id' => $staff_classification->id,
                'text' => $staff_classification->name
            ]);
        }
        return response()->json($staff_classifications);
    }
}
