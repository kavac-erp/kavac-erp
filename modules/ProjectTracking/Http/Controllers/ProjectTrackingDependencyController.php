<?php

/**
 * Controlador de dependencias en el módulo de seguimientos
 */

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingDependency;

/**
 * @class ProjectTrackingDependencyController
 * @brief Clase que gestiona las dependencias
 *
 * @author  William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingDependencyController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        {
            /* Define las reglas de validación para el formulario */
            $this->validateRules = [
                'name'                                  => ['required'],
                'description'                           => ['nullable', 'max:250'],
            ];

            /* Define los mensajes de validación para las reglas del formulario */
            $this->messages = [
                'name.required'                                  => 'El campo nombre es obligatorio.',
            ];
            }
    }

    /**
     * Muestra todos los registros de dependencias
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de dependencias
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingDependency::all()], 200);
    }

    /**
     * Retorna un json con todas las dependencias para ser usado en un componente <select2>
     *
     * @author Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDependencies()
    {
        $dependenciesList = ProjectTrackingDependency::all();
        $dependencies = [];
        array_push(
            $dependencies,
            [
            'id' => '',
            'text' => 'Seleccione...'
            ]
        );
        foreach ($dependenciesList->all() as $dependency) {
            array_push(
                $dependencies,
                [
                'id' => $dependency->id,
                'text' => $dependency->name
                ]
            );
        }
        return response()->json($dependencies);
    }

    /**
     * Mostrar el formulario para crear una nueva dependencia.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Valida y registra una nueva dependencia
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Solicitud con los datos a guardar
     *
     * @return \Illuminate\Http\JsonResponse        Json: objeto guardado y mensaje de confirmación de la operación
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
            'name' => ['required', 'max:100', 'unique:project_tracking_dependencies,name']
            ]
        );
        $projecttrackingDependency = ProjectTrackingDependency::create(['name' => $request->name, 'description' => $request->description]);
        return response()->json(['record' => $projecttrackingDependency, 'message' => 'Success'], 200);
    }

    /**
     * Mostrar el recurso específico.
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('projecttracking::show');
    }

    /**
     * Mostrar el formulario para el recurso específico
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('projecttracking::edit');
    }

    /**
     * Actualiza la información del cargo
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param \Illuminate\Http\Request $request Solicitud con los datos a actualizar
     * @param integer                  $id      Identificador de la dependencia a actualizar
     *
     * @return \Illuminate\Http\JsonResponse        Json con mensaje de confirmación de la operación
     */
    public function update(Request $request, $id)
    {
        $projecttrackingDependency = ProjectTrackingDependency::find($id);
        $this->validate(
            $request,
            [
            'name' => ['required', 'max:100', 'unique:project_tracking_dependencies,name,' . $projecttrackingDependency->id],
            'description' => ['nullable', 'max:200']
            ]
        );
        $projecttrackingDependency->name  = $request->name;
        $projecttrackingDependency->description = $request->description;
        $projecttrackingDependency->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la dependencia
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @param integer $id Identificador de la dependencia a eliminar
     *
     * @return \Illuminate\Http\JsonResponse    Json: objeto eliminado y mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $projecttrackingDependency = ProjectTrackingDependency::find($id);
        $projecttrackingDependency->delete();
        return response()->json(['record' => $projecttrackingDependency, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene las dependencias registradas
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse    Json con los datos de cargos
     */
    public function getProjectTrackingDependencies()
    {
        return response()->json(template_choices('Modules\ProjectTracking\Models\ProjectTrackingDependency', 'name', '', true));
    }
}
