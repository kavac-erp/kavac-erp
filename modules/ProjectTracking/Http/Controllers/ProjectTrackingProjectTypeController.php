<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\ProjectTracking\Models\ProjectTrackingProjectType;

/**
 * @class ProjectTrackingProjectTypeController
 * @brief Controlador que procesa los 'request' de mostrar, crear, guardar, editar, actualizar y eliminar registros.
 *
 * Controlador dedicado a procesar los 'request' de mostrar, crear, guardar, editar, actualizar y eliminar registros.
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProjectTypeController extends Controller
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
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                                  => 'El campo nombre es obligatorio.',
        ];
    }

    /**
     * Retorna un json con todos los tipos de proyecto
     *
     * @author    José Jorge Briceño <josejorgebriceno9@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingProjectType::all()], 200);
    }

    /**
     * Retorna un json con todos los tipos de proyecto para ser usado en un componente <select2>
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getProjectTypes()
    {
        $projecttypesList = ProjectTrackingProjectType::all();
        $projecttypes = [];
        array_push($projecttypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($projecttypesList->all() as $projectT) {
            array_push($projecttypes, [
                'id' => $projectT->id,
                'text' => $projectT->name
            ]);
        }
        return response()->json($projecttypes);
    }

    /**
     * Muestra el formulario para crear un nuevo tipo de proyecto
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena un nuevo tipo de proyecto
     *
     * @author    José Jorge Briceño <josejorgebriceno9@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $product = ProjectTrackingProjectType::create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información sobre un tipo de proyecto
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
     * Muestra el formulario de edición de un tipo de proyecto
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
     * Actualiza los datos de un tipo de proyecto
     *
     * @author    José Jorge Briceño <josejorgebriceno9@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        $product = ProjectTrackingProjectType::find($request->input('id'));
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->save();
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de proyecto
     *
     * @author    José Jorge Briceño <josejorgebriceno9@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $product = ProjectTrackingProjectType::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de los proyectos a implementar en elementos select
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    array    Arreglo con los registros a mostrar
     */
    public function getTypeProjects()
    {
        return template_choices('Modules\ProjectTracking\Models\ProjectTrackingProjectType', 'name', '', true);
    }
}
