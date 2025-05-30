<?php

namespace Modules\Warehouse\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Rules\CodeSetting as CodeSettingRule;
use App\Models\CodeSetting;
use App\Models\Parameter;
use App\Repositories\ParameterRepository;

/**
 * @class WarehouseRequestController
 * @brief Controlador de solicitudes de almacén
 *
 * Clase que gestiona las solicitudes de los productos de almacén
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseSettingController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:warehouse.setting', ['only' => 'index']);
    }

    /**
     * Muestra el listado de las configuraciones de almacén
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $paramMultiWarehouse = Parameter::where([
            'active' => true, 'required_by' => 'warehouse',
            'p_key' => 'multi_warehouse', 'p_value' => 'true'
        ])->first();
        $header = [
            'route' => 'warehouse.setting-parameter.store', 'method' => 'POST', 'role' => 'form', 'class' => 'form',
        ];

        $codeSettings = CodeSetting::where('module', 'warehouse')->get();
        $pdCode = $codeSettings->where('table', 'warehouse_inventory_products')->first();
        $mvCode = $codeSettings->where('table', 'warehouse_movements')->first();
        $rqCode = $codeSettings->where('table', 'warehouse_requests')->first();
        $rpCode = $codeSettings->where('table', 'warehouse_reports')->first();
        $ivCode = $codeSettings->where('table', 'warehouse_inventories')->first();

        return view(
            'warehouse::settings',
            compact('paramMultiWarehouse', 'header', 'pdCode', 'mvCode', 'rqCode', 'rpCode', 'ivCode')
        );
    }

    /**
     * Valida y registra la configuración de los códigos de modulo de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        /* Reglas de validación para la configuración de códigos */
        $this->validate($request, [
            'products_code'    => [new CodeSettingRule()],
            'movements_code'   => [new CodeSettingRule()],
            'requests_code'    => [new CodeSettingRule()],
            'reports_code'     => [new CodeSettingRule()],
            'inventories_code' => [new CodeSettingRule()]
        ]);

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                if ($table === "products") {
                    /* Define la tabla asociado a los productos inventariados */
                    $table = "inventory_products";

                    /* Define el modelo asociado a los productos inventariados */
                    $model = \Modules\Warehouse\Models\WarehouseInventoryProduct::class;
                } elseif ($table === "movements") {
                    /* Define el modelo para asociado a los movimientos de almacén */
                    $model = \Modules\Warehouse\Models\WarehouseMovement::class;
                } elseif ($table === "requests") {
                    /* Define el modelo para asociado a las solicitudes de almacén */
                    $model = \Modules\Warehouse\Models\WarehouseRequest::class;
                } elseif ($table === "reports") {
                    /* Define el modelo para asociado a los reportes de almacén */
                    $model = \Modules\Warehouse\Models\WarehouseReport::class;
                } elseif ($table === "inventories") {
                    /* Define el modelo para asociado al inventario de almacenes */
                    //$model = \Modules\Warehouse\Models\WarehouseInventory::class;
                }

                if ($table != "inventories") {
                    $codeSetting = CodeSetting::where([
                        'module' => 'warehouse',
                        'table'  => 'warehouse_' . $table,
                        'field'  => $field
                    ])->first();

                    if (!isset($codeSetting)) {
                        $codeSetting = CodeSetting::create([
                            'module'        => 'warehouse',
                            'table'         => 'warehouse_' . $table,
                            'field'         => $field,
                            'format_prefix' => $prefix,
                            'format_digits' => $digits,
                            'format_year'   => $sufix,
                            'model'         => $model
                        ]);
                    }

                    /* Define el estado verdadero para indicar que se ha registrado información */
                    $saved = true;
                }
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->route('warehouse.setting.index');
    }

    /**
     * Valida y registra en la configuración del sistema la opcion de multi almacenes
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function storeParameter(Request $request, ParameterRepository $parameterRepository)
    {
        $msgType = ['type' => 'store'];
        $parameterRepository->updateOrCreate(
            ['p_key' => 'multi_warehouse', 'required_by' => 'warehouse'],
            ['p_value' => (!is_null($request->multi_warehouse)) ? 'true' : 'false']
        );

        $request->session()->flash('message', ['type' => 'store']);
        return redirect()->route('warehouse.setting.index');
    }

    /**
     * Muesta todos los registros de los parámetros de configuración del requeridos por el módulo de almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueSetting()
    {
        $paramMultiWarehouse = Parameter::where([
            'active' => true, 'required_by' => 'warehouse',
            'p_key' => 'multi_warehouse', 'p_value' => 'true'
        ])->first();
        $paramMultiInstitution = Parameter::where([
            'active' => true, 'required_by' => 'core',
            'p_key' => 'multi_institution', 'p_value' => 'true'
        ])->first();

        return response()->json(['record' => [
            'multi_institution' => is_null($paramMultiInstitution) ? false : $paramMultiInstitution->p_value,
            'multi_warehouse' => is_null($paramMultiWarehouse) ? false : $paramMultiWarehouse->p_value]
        ], 200);
    }
}
