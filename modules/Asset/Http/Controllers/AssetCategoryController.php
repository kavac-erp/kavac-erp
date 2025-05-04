<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Models\AssetType;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Rules\Setting\AssetCategoryUnique;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class      AssetCategoryController
 * @brief      Controlador de categorias de bienes institucionales
 *
 * Clase que gestiona las categorias de bienes institucionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetCategoryController extends Controller
{
    use ValidatesRequests;

    /**
     * Reglas de validación
     *
     * @var array $validateRules
     */
    protected $validateRules;

    /**
     * Mensajes de las reglas de validación
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
        $this->middleware('permission:asset.setting.category');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'          => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100', Rule::unique('asset_categories')],
            'code'          => ['required', 'max:10'],
            'asset_type_id' => ['required']


        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'     => 'El campo categoría general es obligatorio.',
            'name.max'          => 'El campo categoría general no debe contener más de 100 caracteres.',
            'name.regex'        => 'El campo categoría general no debe permitir números ni símbolos.',
            'name.unique'       => 'El campo categoría general ya ha sido registrado.',
            'code.required'     => 'El campo código de categoría general es obligatorio.',
            'code.max'          => 'El campo código de categoría general no debe contener más de 10 caracteres.',
            'asset_type_id.required' => 'El campo tipo de bien es obligatorio.'
        ];
    }

    /**
     * Muestra un listado de las categorias de un tipo de bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetCategory::with('assetType')->get()], 200);
    }

    /**
     * Valida y registra un nueva categoria general
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     Request         $request    Datos de la petición
     *
     * @return    JsonResponse    Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_merge(
            ['id' => [new AssetCategoryUnique($request->input('asset_type_id'), $request->input('code'))]],
            $validateRules
        );

        $this->validate($request, $validateRules, $this->messages);


        /* Objeto asociado al modelo AssetCategory */
        $category = AssetCategory::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'asset_type_id' => $request->asset_type_id,
        ]);

        return response()->json(['record' => $category, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de la categoria general
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     Request          $request     Datos de la petición
     * @param     AssetCategory    $category    Datos de la categoria
     *
     * @return    JsonResponse          Objeto con los registros a mostrar
     */
    public function update(Request $request, AssetCategory $category)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100',
                            Rule::unique('asset_categories')->ignore($category->id)]]
        );
        $validateRules  = array_merge(
            [
                'id' => [
                    new AssetCategoryUnique(
                        $request->input('asset_type_id'),
                        $request->input('code')
                    )
                ]
            ],
            $validateRules
        );

        $this->validate($request, $validateRules, $this->messages);


        $category->name = $request->input('name');
        $category->code = $request->input('code');
        $category->asset_type_id = $request->asset_type_id;
        $category->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la categoria general
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     AssetCategory    $category    Datos de la categoria
     *
     * @return    JsonResponse     Objeto con los registros a mostrar
     */
    public function destroy(AssetCategory $category)
    {
        $category->delete();
        return response()->json(['record' => $category, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de las categorias generales de bienes institucionales a implementar en elementos select
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer|null    $type_id    Identificador único del tipo de bien
     *
     * @return    array      Arreglo con los registros a mostrar
     */
    public function getCategories($type_id = null)
    {
        if (is_null($type_id)) {
            $records = $this->templateChoices(
                'Modules\Asset\Models\AssetCategory',
                'name',
                [],
                true,
                null,
                ['code']
            );
        }
        $asset_type = AssetType::find($type_id);
        return ($asset_type) ? $this->templateChoices(
            'Modules\Asset\Models\AssetCategory',
            'name',
            ['asset_type_id' => $asset_type->id],
            true,
            null,
            ['code']
        ) : [];
    }

    /**
     * Obtiene el listado de las categorias generales de bienes institucionales a implementar en elementos select
     *
     * @param string            $model     Instancia del modelo
     * @param string|array      $fields    Campos a mostrar en el listado
     * @param array             $filters   Filtros de consulta
     * @param boolean           $vuejs     Si se requiere el listado en formato de vuejs
     * @param integer|null      $except_id Identificador del registro a excluir
     * @param array             $others    Otras columnas a mostrar
     *
     * @return array
     */
    public function templateChoices($model, $fields = 'name', $filters = [], $vuejs = false, $except_id = null, $others = [])
    {
        $records = (is_object($model)) ? $model : $model::all();
        if ($filters) {
            if (!isset($filters['relationship'])) {
                $records = $model::where($filters)->get();
            } else {
                /* Filtra la información a obtener mediante relaciones */
                $relationship = $filters['relationship'];
                $records = $model::whereHas($relationship, function ($q) use ($filters) {
                    $q->where($filters['where']);
                })->get();
            }
        }

        /* Inicia la opción vacia por defecto */
        $options = ($vuejs) ? [['id' => '', 'text' => 'Seleccione...']] : ['' => 'Seleccione...'];

        foreach ($records as $rec) {
            if (is_array($fields)) {
                $text = '';
                foreach ($fields as $field) {
                    $text .= ($field !== "-" && $field !== " ")
                        ? $rec->$field
                        : (($field === " ") ? $field : " {$field} ");
                }
            } else {
                $text = $rec->$fields;
            }

            if (is_null($except_id) || $except_id !== $rec->id) {
                /*
                 * Carga el listado según el tipo de plantilla en el cual se va a implementar
                 * (normal o con VueJS)
                 */
                if ($vuejs) {
                    $option = ['id' => $rec->id, 'text' => $text];
                    foreach ($others as $other) {
                        $option[$other] = $rec->$other;
                    }
                    array_push($options, $option);
                } else {
                    $options[$rec->id] = $text;
                }
            }
        }
        return $options;
    }
}
