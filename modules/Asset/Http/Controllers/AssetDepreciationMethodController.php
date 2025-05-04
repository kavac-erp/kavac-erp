<?php

/**
 * [descripción del namespace]
 * */

namespace Modules\Asset\Http\Controllers;

use DateTime;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Asset\Enums\DepreciationType;
use Illuminate\Contracts\Support\Renderable;
use Modules\Asset\Models\AssetDepreciationMethod;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * @class AssetDepreciationMethodController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
 *
 * @license [LICENCIA DE SOFTWARE CENDITEL]
 * @link    (http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDepreciationMethodController extends Controller
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
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     */
    public function __construct()
    {
        /**
         * Establece permisos de acceso para cada método del controlador
        */
        //$this->middleware('permission:asset.setting.depreciation-method');

        /**
         * Define las reglas de validación para el formulario
         * */
        $this->validateRules = [
            'depreciation_type_id' => ['required'],
            'institution_id' => ['required'],
        ];

        /**
         * Define los mensajes de validación para las reglas del formulario
         * */
        $this->messages = [
            'depreciation_type_id.required' => 'El campo tipo de depreciación es obligatorio.',
            'institution_id.required' => 'El campo organización es obligatorio.',
        ];
    }
    /**
     * [descripción del método]
     *
     * @method index
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function index(): JsonResponse
    {
        return response()->json(['records' => AssetDepreciationMethod::with('institution')->get()], 200);
    }

    /**
     * [descripción del método]
     *
     * @param object Request $request Objeto con información de la petición
     *
     * @method store
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request): JsonResponse
    {
        $request->institution_id ? $institution = Institution::whereId($request->institution_id)
            ->first() : $this->validateRules['institution_id'] = ['required'];
        if ($request->activation_date) {
            $this->validateRules['depreciation_type_id'] = Rule::unique('asset_depreciation_methods')
                ->ignore($request->id)->where('institution_id', $request->institution_id);
            $this->messages['depreciation_type_id.unique'] = 'EL campo debe ser único';
            $this->validateRules['activation_date'] = ['after_or_equal:' . $institution->start_operations_date];
            $this->messages['activation_date.after_or_equal'] = 'El campo fecha de activación debe ser'
            . ' una fecha posterior o igual a ' . (new DateTime($institution->start_operations_date))->format('d-m-Y');
            $this->validateRules['activation_date'] = ['before_or_equal:' . today()];
            $this->messages['activation_date.before_or_equal'] = 'El campo fecha de activación debe ser'
            . ' una fecha anterior o igual a ' . today()->format('d-m-Y');
        }
        $this->validate($request, $this->validateRules, $this->messages);

        if ($request->active === true) {
            AssetDepreciationMethod::query()
                ->where(
                    [
                    'institution_id' => $request->institution_id,
                    'active' => true
                    ]
                )
                ->update(['active' => false]);
        }

        /**
         * Objeto asociado al modelo AssetUseFunction
         *
         * @var Object $function
         */
        $assetDepreciationMethod = AssetDepreciationMethod::create(
            [
            'depreciation_type_id' => $request->depreciation_type_id,
            'institution_id' => $request->institution_id,
            'activation_date' => $request->activation_date ?? null,
            'active' => $request->active
            ]
        );

        return response()->json(['record' => $assetDepreciationMethod, 'message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @param object  Request $request Objeto con datos de la petición
     * @param integer         $id      Identificador del registro
     *
     * @method update
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->institution_id ? $institution = Institution::whereId($request->institution_id)
            ->first() : $this->validate($request, $this->validateRules);
        if ($request->activation_date) {
            $this->validateRules['depreciation_type_id'] = Rule::unique('asset_depreciation_methods')
                ->ignore($request->id)->where('institution_id', $request->institution_id);
            $this->messages['depreciation_type_id.unique'] = 'EL campo debe ser único';
            $this->validateRules['activation_date'] = ['after_or_equal:' . $institution->start_operations_date];
            $this->validateRules['activation_date'] = ['before_or_equal:' . today()];
        }
        $this->validate($request, $this->validateRules, $this->messages);

        if ($request->active === true) {
            AssetDepreciationMethod::query()
                ->where('id', '!=', $id)
                ->where(
                    [
                    'institution_id' => $request->institution_id,
                    'active' => true
                    ]
                )
                ->update(['active' => false]);
        }

        $assetDepreciationMethod = AssetDepreciationMethod::find($id);
        $assetDepreciationMethod->depreciation_type_id = $request->depreciation_type_id;
        $assetDepreciationMethod->institution_id = $request->institution_id;
        $assetDepreciationMethod->activation_date = $request->activation_date ?? null;
        $assetDepreciationMethod->active = $request->active;
        $assetDepreciationMethod->save();

        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * [descripción del método]
     *
     * @param integer $id Identificador del registro
     *
     * @method destroy
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id): JsonResponse
    {
        $assetDepreciationMethod = AssetDepreciationMethod::find($id);
        $assetDepreciationMethod->forceDelete();
        return response()->json(['record' => $assetDepreciationMethod, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene el listado de los tipos de depreciación registrados a implementar en elementos select
     *
     * @author Yennifer Ramirez <yramirez@cenditel.gob.ve>
     * @return array    Arreglo con los registros a mostrar
     */
    public function getDepreciationTypes(): array
    {
        $enum = DepreciationType::cases();
        $response = collect($enum)->map(
            function ($item) {
                return [
                    'id' => $item->value,
                    'text' => $item->getName(),
                    'formula' => $item->getFormula(),
                    'translate_formula' => $item->getTranslateFormula(),
                    'disabled' => !$item->getPublic(),
                ];
            }
        )->prepend(['id' => '', 'text' => 'Seleccione...'])->toArray();

        return $response ?? [];
    }
}
