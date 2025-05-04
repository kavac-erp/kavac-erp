<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Models\AssetSubcategory;
use Modules\Asset\Models\AssetSpecificCategory;
use Modules\Asset\Models\AssetRequiredItem;
use Modules\Asset\Rules\Setting\AssetSpecificCategoryUnique;
use Illuminate\Validation\Rule;

/**
 * @class      AssetSpecificCategoryController
 * @brief      Controlador de Categorias Especificas de Bienes
 *
 * Clase que gestiona las Categorias Especificas de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @author     Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSpecificCategoryController extends Controller
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
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.setting.specific');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'asset_type_id'         => ['required'],
            'asset_category_id'     => ['required'],
            'asset_subcategory_id'  => ['required'],
            'code'                  => ['required', 'max:10'],
            'name'                  => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100',
                                        Rule::unique('asset_specific_categories')],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'name.required'                 => 'El campo categoría específica es obligatorio.',
            'name.max'                      => 'El campo categoría específica no debe contener más de 100 caracteres.',
            'name.regex'                    => 'El campo categoría específica no debe permitir números ni símbolos.',
            'code.required'                 => 'El campo código de la categoría específica es obligatorio.',
            'code.max'                      => 'El campo código de la categoría específica no debe contener más de 10 caracteres.',
            'asset_subcategory_id.required' => 'El campo subcategoría es obligatorio.',
            'asset_type_id.required'        => 'El campo tipo de bien es obligatorio.',
            'asset_category_id.required'    => 'El campo categoría general es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de las Subcategorias de una categoria de Bien
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetSpecificCategory::with(
            ['assetSubcategory' =>

            function ($query) {
                $query->with(['assetCategory' => function ($query) {
                    $query->with('assetType');
                }]);
            }]
        )->get()], 200);
    }

    /**
     * Muestra el formulario para crear un nueva Categoria Especifica
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function create()
    {
    }

    /**
     * Valida y Registra un nueva Categoria Especifica
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
        $validateRules  = $this->validateRules;
        $validateRules  = array_merge(
            [
                'id' => [
                    new AssetSpecificCategoryUnique(
                        $request->input('asset_subcategory_id'),
                        $request->input('code')
                    )
                ]
            ],
            $validateRules
        );

        $this->validate($request, $validateRules, $this->messages);


        /* Objeto asociado al modelo AssetSpecificCategory */
        $specific_category = AssetSpecificCategory::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'asset_subcategory_id' => $request->input('asset_subcategory_id'),
        ]);

        return response()->json(['record' => $specific_category, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de la Categoria Especifica de un Bien
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request                       $request              Datos de la petición
     * @param     \Modules\Asset\Models\AssetSpecificCategory    $specific_category    Datos de la categoria especifica
     *
     * @return    \Illuminate\Http\JsonResponse                  Objeto con los registros a mostrar
     */
    public function update(Request $request, AssetSpecificCategory $specific_category)
    {
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            [
                'name' => [
                    'required',
                    'regex:/^[a-zA-ZÁ-ÿ\s]*$/u',
                    'max:100',
                    Rule::unique('asset_specific_categories')->ignore($specific_category->id)
                ]
            ]
        );
        $validateRules  = array_merge(
            [
                'id' => [
                    new AssetSpecificCategoryUnique(
                        $request->input('asset_subcategory_id'),
                        $request->input('code')
                    )
                ]
            ],
            $validateRules
        );

        $this->validate($request, $validateRules, $this->messages);

        $specific_category = AssetSpecificCategory::find($request->id);

        $specific_category->name = $request->input('name');
        $specific_category->code = $request->input('code');
        $specific_category->asset_subcategory_id = $request->input('asset_subcategory_id');

        $specific_category->save();

        return response()->json(['message' => 'Registro actualizado correctamente'], 200);
    }

    /**
     * Elimina la Categoria Especifica de un Bien
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          Identificador único de la categoría específica a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        $specific_category = AssetSpecificCategory::find($id);
        $specific_category->delete();
        return response()->json(['record' => $specific_category, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de las categorias específicas de bienes institucionales a implementar en elementos select
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $subcategory_id    Identificador único de la sub-categoría
     *
     * @return    array      Arreglo con los registros a mostrar
     */
    public function getSpecificCategories($subcategory_id = null)
    {
        if (is_null($subcategory_id)) {
            return $this->templateChoices('Modules\Asset\Models\AssetSpecificCategory', 'name', '', true, null, ['code']);
        }
        $asset_subcategory = AssetSubcategory::find($subcategory_id);
        return ($asset_subcategory)
            ? $this->templateChoices(
                'Modules\Asset\Models\AssetSpecificCategory',
                'name',
                ['asset_subcategory_id' => $asset_subcategory->id],
                true,
                null,
                ['code']
            )
            : [];
    }

    /**
     * Obtiene el listado de los requerimientos de las categorias específicas de bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador único de la categoría específica
     *
     * @return    array      Arreglo con los registros a mostrar
     */
    public function getRequired($id)
    {
        $required = AssetRequiredItem::where('asset_specific_category_id', $id)->first();
        if (is_null($required)) {
            $required = [
                'serial' => false,
                'marca' => false,
                'model' => false,
                'address' => false
            ];
        }
        return response()->json(['record' => $required], 200);
    }

    /**
     * Obtiene un listado de registros
     *
     * @param string $model Clase del modelo a consultar
     * @param string|array $fields Campos a incorporar en la consulta
     * @param string|array $filters Filtros de la consulta
     * @param boolean $vuejs Indica si el listado es para VueJS
     * @param array|null $except_id Listado de ids a excluir de la consulta
     * @param array $others Otros campos del listado
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
                /* Carga el listado según el tipo de plantilla en el cual se va a implementar (normal o con VueJS) */
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
