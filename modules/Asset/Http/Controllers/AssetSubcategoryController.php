<?php

namespace Modules\Asset\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Asset\Models\AssetSubcategory;
use Modules\Asset\Models\AssetCategory;
use Modules\Asset\Rules\Setting\AssetSubcategoryUnique;
use Illuminate\Validation\Rule;
use Nwidart\Modules\Facades\Module;

/**
 * @class      AssetSubcategoryController
 * @brief      Controlador de Subcategorias de Bienes
 *
 * Clase que gestiona las Subcategorias de bienes
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSubcategoryController extends Controller
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
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:asset.setting.subcategory');
        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'asset_type_id'     => ['required'],
            'asset_category_id' => ['required'],
            'code'              => ['required', 'max:10'],
            'name'              => ['required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100',
                                    Rule::unique('asset_subcategories')],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required'              => 'El campo código de la subcategoría es obligatorio.',
            'code.max'                   => 'El campo código de la subcategoría no debe contener más de 10 caracteres.',
            'name.required'              => 'El campo subcategoría es obligatorio.',
            'name.max'                   => 'El campo subcategoría no debe contener más de 100 caracteres.',
            'name.regex'                 => 'El campo subcategoría no debe permitir números ni símbolos.',
            'asset_category_id.required' => 'El campo categoría general es obligatorio.',
            'asset_type_id.required'     => 'El campo tipo de bien es obligatorio.',
        ];
    }

    /**
     * Muestra un listado de las subcategorias de una categoria general de los bienes institucionales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => AssetSubcategory::with([
            'assetCategory' => function ($query) {
                $query->with('assetType');
            }])->get()], 200);
    }

    /**
     * Valida y registra un nueva subcategoria
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
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        $validateRules  = $this->validateRules;
        $validateRules  = array_merge(
            ['id' => [new AssetSubcategoryUnique($request->input('asset_category_id'), $request->input('code'))]],
            $validateRules
        );

        if ($exist_accounting) {
            $validateRules = array_merge(
                $validateRules,
                [
                    'accounting_account_debit' => ['required'],
                    'accounting_account_asset' => ['required']
                ]
            );

            $validateMessages  = $this->messages;

            $validateMessages = array_merge(
                [
                    'accounting_account_debit.required' => 'El campo cuenta contable de gastos es obligatorio.',
                    'accounting_account_asset.required' => 'El campo cuenta contable de depreciación acumulada es obligatorio.'
                ],
                $validateMessages
            );
        }

        $this->validate($request, $validateRules, $validateMessages);


        /* Objeto asociado al modelo AssetSubcategory */
        $subcategory = AssetSubcategory::create([
            'name' => $request->input('name'),
            'code' => $request->input('code'),
            'asset_category_id' => $request->asset_category_id,
        ]);

        if ($exist_accounting) {
            $subcategory->accounting_account_debit = $request->input('accounting_account_debit');
            $subcategory->accounting_account_asset = $request->input('accounting_account_asset');
            $subcategory->save();
        }

        return response()->json(['record' => $subcategory, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de la subcategoria de un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request                  $request        Datos de la petición
     * @param     \Modules\Asset\Models\AssetSubcategory    $subcategory    Datos de la subcategoria
     *
     * @return    \Illuminate\Http\JsonResponse             Objeto con los registros a mostrar
     */
    public function update(Request $request, AssetSubcategory $subcategory)
    {
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        $validateRules  = $this->validateRules;
        $validateRules  = array_replace(
            $validateRules,
            [
                'name' => [
                    'required', 'regex:/^[a-zA-ZÁ-ÿ\s]*$/u', 'max:100',
                    Rule::unique('asset_subcategories')->ignore($subcategory->id)
                ]
            ]
        );
        $validateRules  = array_merge(
            [
                'id' => [
                    new AssetSubcategoryUnique(
                        $request->input('asset_category_id'),
                        $request->input('code')
                    )
                ]
            ],
            $validateRules
        );

        if ($exist_accounting) {
            $validateMessages  = $this->messages;

            $validateMessages = array_merge(
                [
                    'accounting_account_debit.required' => 'El campo cuenta contable de gastos es obligatorio.',
                    'accounting_account_asset.required' => 'El campo cuenta contable de depreciación acumulada es obligatorio.'
                ],
                $validateMessages
            );

            $validateRules = array_merge(
                $validateRules,
                [
                    'accounting_account_debit' => ['required'],
                    'accounting_account_asset' => ['required']
                ]
            );
        }

        $this->validate($request, $validateRules, $validateMessages);

        $subcategory->name = $request->input('name');
        $subcategory->code = $request->input('code');
        $subcategory->asset_category_id = $request->asset_category_id;

        if ($exist_accounting) {
            $subcategory->accounting_account_debit = $request->input('accounting_account_debit');
            $subcategory->accounting_account_asset = $request->input('accounting_account_asset');
        }

        $subcategory->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina la subcategoria de un bien institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Modules\Asset\Models\AssetSubcategory    $subcategory    Datos de la subcategoria
     *
     * @return    \Illuminate\Http\JsonResponse             Objeto con los registros a mostrar
     */
    public function destroy(AssetSubcategory $subcategory)
    {
        $subcategory->delete();
        return response()->json(['record' => $subcategory, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de subcategorias de un bien  institucional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $category_id    Identificador de la categoría
     *
     * @return    array
     */
    public function getSubcategories($category_id = null)
    {
        if (is_null($category_id)) {
            return $this->templateChoices('Modules\Asset\Models\AssetSubcategory', 'name', '', true, null, ['code']);
        }
        $asset_category = AssetCategory::find($category_id);
        return ($asset_category)
            ? $this->templateChoices(
                'Modules\Asset\Models\AssetSubcategory',
                'name',
                ['asset_category_id' => $asset_category->id],
                true,
                null,
                ['code']
            )
            : [];
    }

    /**
     * Obtiene el listado de subcategorias de bienes
     *
     * @param string $model Clase del modelo
     * @param string|array $fields Campos a mostrar
     * @param string|array $filters Filtros de la consulta
     * @param boolean $vuejs Indica si es para Vuejs
     * @param integer|null $except_id Identificador del registro a excluir
     * @param array $others Otras columnas
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
                /** Filtra la información a obtener mediante relaciones */
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
