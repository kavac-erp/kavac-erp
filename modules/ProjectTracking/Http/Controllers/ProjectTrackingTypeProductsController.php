<?php

namespace Modules\ProjectTracking\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Validation\Rule;
use Modules\ProjectTracking\Models\ProjectTrackingTypeProducts;

/**
 * @class ProjectTrackingTypeProductsController
 * @brief Gestiona los procesos del controlador
 *
 * Controlador dedicado a la funcion typos de productos
 *
 * @author    Francisco Escala <fjescala@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTypeProductsController extends Controller
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
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        //$this->middleware('permission:asset.setting.condition');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'     => ['required',  'max:100', Rule::unique('asset_conditions')],

        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'     => 'El Nombre es obligatorio.',
            'name.max'          => 'El Nombre no debe contener más de 100 caracteres.',
            'name.unique'       => 'El Nombre ya ha sido registrado'
        ];
    }

    /**
     * Retorna un json con todos los tipos de productos
     *
     * @author    Francisco Escala <fjescala@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return response()->json(['records' => ProjectTrackingTypeProducts::all()], 200);
    }

    /**
     * Retorna un json con todos los tipos de producto para ser usado en un componente <select2>
     *
     * @author    Oscar González <xxmaestroyixx@gmail.com>
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getProductTypes()
    {
        $producttypesList = ProjectTrackingTypeProducts::all();
        $producttypes = [];
        array_push($producttypes, [
            'id' => '',
            'text' => 'Seleccione...'
        ]);
        foreach ($producttypesList->all() as $productT) {
            array_push($producttypes, [
                'id' => $productT->id,
                'text' => $productT->name
            ]);
        }
        return response()->json($producttypes);
    }

    /**
     * Muestra un formulario para crear un nuevo tipo de producto
     *
     * @author    Francisco Escala <fjescala@gmail.com>
     *
     * @return    \Illuminate\View\View
     */
    public function create()
    {
        return view('projecttracking::create');
    }

    /**
     * Almacena la información de un nuevo tipo de producto
     *
     * @author    Francisco Escala <fjescala@gmail.com>
     *
     * @param     Request    $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        /* Objeto asociado al modelo ProjectTrackingTypeProducts */
        $this->validate($request, $this->validateRules, $this->messages);
        $product = ProjectTrackingTypeProducts::create([
            'name' => $request->input('name'),
            'description' => $request->input('description')
        ]);

        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Muestra información de un tipo de producto
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
     * Actualiza la información de un tipo de producto
     *
     * @author    Francisco Escala <fjescala@gmail.com>
     *
     * @param     Request    $request         Datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function update(ProjectTrackingTypeProducts $product, Request $request)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => [
                'required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100'
            ]]
        );

        $product = ProjectTrackingTypeProducts::find($request->input('id'));
        $product->name = $request->input('name');
        $product->description = $request->input('description');
        $product->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un registro de tipo de producto
     *
     * @author    Francisco Escala <fjescala@gmail.com>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {

        $product = ProjectTrackingTypeProducts::find($id);
        $product->delete();
        return response()->json(['record' => $product, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de las condiciones físicas de los bienes institucionales a implementar en elementos select
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    array    Arreglo con los registros a mostrar
     */
    public function getTypeProducts()
    {
        return template_choices('Modules\ProjectTracking\Models\ProjectTrackingTypeProducts', 'name', '', true);
    }
}
