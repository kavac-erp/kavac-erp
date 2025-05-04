<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Models\AssetUseFunction;
use Illuminate\Validation\Rule;

/**
 * @class      AssetUseFunctionController
 * @brief      Controlador de las funciones de uso de los bienes institucionales
 *
 * Clase que gestiona las funciones de uso de los bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetUseFunctionController extends Controller
{
    use ValidatesRequests;

    /**
     * Establece las reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Establece los mensajes de validación
     *
     * @var array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    YenniferRamirez <yramirez@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        //$this->middleware('permission:asset.setting.use-function');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'     => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100', Rule::unique('asset_use_functions')],

        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'     => 'El campo función de uso es obligatorio.',
            'name.max'          => 'El campo función de uso no debe contener más de 100 caracteres.',
            'name.regex'        => 'El campo función de uso no debe permitir números ni símbolos.',
            'name.unique'       => 'El campo función de uso ya ha sido registrado'
           ];
    }

    /**
     * Muestra un listado de las funciones de uso de los bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetUseFunction::all()], 200);
    }

    /**
     * Valida y registra una nueva funcion de uso
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);


        /* Objeto asociado al modelo AssetUseFunction */
        $function = AssetUseFunction::create([
            'name' => $request->input('name')
        ]);

        return response()->json(['record' => $function, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de la función de uso de un bien
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     * @param     integer                          $id         Identificador único de la función de usa a editar
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */

    public function update(Request $request, $id)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100',
                            Rule::unique('asset_use_functions')->ignore($id)]]
        );

        $this->validate($request, $validateRules, $this->messages);

        $function = AssetUseFunction::find($id);
        if ($function) {
            $function->name = $request->input('name');
            $function->save();
        }

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la función de uso de un bien
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id    Identificador único de la función de usa a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        $function = AssetUseFunction::find($id);
        if ($function) {
            $function->delete();
        }
        return response()->json(['record' => $function, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de las funciones de uso registradas a implementar en elementos select
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Arreglo con los registros a mostrar
     */
    public function getUseFunctions()
    {
        return template_choices('Modules\Asset\Models\AssetUseFunction', 'name', '', true);
    }
}
