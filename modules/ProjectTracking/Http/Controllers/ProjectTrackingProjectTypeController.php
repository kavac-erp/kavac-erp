<?php

/** [descripción del namespace] */

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
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProjectTypeController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     * @var Array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     * @var Array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                                  => ['required'],
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                                  => 'El campo nombre es obligatorio.',
        ];
    }
    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [José Jorge Briceño] [josejorgebriceno9@gmail.com]
     *
     * @return    Renderable    [Json data]
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingProjectType::all()], 200);
    }

        /**
     * Retorna un json con todos los tipos de proyecto para ser usado en un componente <select2>
     *
     * @method    getProjectTypes
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return    Renderable    [descripción de los datos devueltos]
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
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [description de los datos devueltos]
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
     * @author    [José Jorge Briceño] [josejorgebriceno9@gmail.com]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [Json data]
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);
        //
        $product = ProjectTrackingProjectType::create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return response()->json(['record' => $product, 'message' => 'Success'], 200);
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
     * @return    Renderable    [description de los datos devueltos]
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
     * @return    Renderable    [description de los datos devueltos]
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
     * @author    [José Jorge Briceño] [josejorgebriceno9@gmail.com]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [Json data]
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
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [José Jorge Briceño] [josejorgebriceno9@gmail.com]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [Json data]
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
     * @return    Array    Arreglo con los registros a mostrar
     */
    public function getTypeProjects()
    {
        return template_choices('Modules\ProjectTracking\Models\ProjectTrackingProjectType', 'name', '', true);
    }
}
