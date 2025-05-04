<?php

namespace Modules\Purchase\Http\Controllers;

use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Purchase\Models\PurchaseOrder;
use Modules\Purchase\Models\PurchaseStates;
use Illuminate\Contracts\Support\Renderable;
use App\Rules\CodeSetting as CodeSettingRule;
use Modules\Purchase\Models\PurchaseQuotation;
use Modules\Purchase\Models\PurchaseDirectHire;
use Modules\Purchase\Models\PurchaseRequirement;

/**
 * @class PurchaseSettingController
 * @brief Gestiona los procesos de configuración del módulo de compras
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSettingController extends Controller
{
    /**
     * Define la configuración de la clase
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:purchase.setting.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:purchase.setting.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:purchase.setting.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:purchase.setting.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra las configuraciones en el módulo de compras
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        /* Contiene los registros de configuraciones de códigos */
        $codeSettings = CodeSetting::where('module', 'purchase')->get();
        /* Contiene información sobre la configuración de código para la requisición */
        $rqCode = $codeSettings->where('table', 'purchase_requirements')->first();
        /* Contiene información sobre la configuración de código para la cotización */
        $quCode = $codeSettings->where('table', 'purchase_quotations')->first();
        /* Contiene información sobre la configuración de código para pre-compromisos */
        $esCode = $codeSettings->where('table', 'purchase_states')->first();
        /* Contiene información sobre la configuración de código para la acta */
        $miCode = $codeSettings->where('table', 'purchase_minutes')->first();
        /* Contiene información sobre la configuración de código para la orden de compra */
        $buCode = $codeSettings->where('table', 'purchase_buy_orders')->first();
        /* Contiene información sobre la configuración de código para la orden de servicio */
        $soCode = $codeSettings->where('table', 'purchase_service_orders')->first();
        /* Contiene información sobre la configuración de código para el reintegro */
        $reCode = $codeSettings->where('table', 'purchase_refunds')->first();

        return view(
            'purchase::settings',
            compact(
                'rqCode',
                'quCode',
                'esCode',
                'miCode',
                'buCode',
                'soCode',
                'reCode'
            )
        );
    }

    /**
     * Muestra el formulario para crear una nueva configuración
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('purchase::create');
    }

    /**
     * Almacena una nueva configuración
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $request->validate([
            'requirements_code'   => [new CodeSettingRule()],
            'quotations_code'     => [new CodeSettingRule()],
            'compromises_code'    => [new CodeSettingRule()],
            'minutes_code'        => [new CodeSettingRule()],
            'buy-orders_code'     => [new CodeSettingRule()],
            'service-orders_code' => [new CodeSettingRule()],
            'refunds_code'        => [new CodeSettingRule()],
        ]);

        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el modelo al cual hace referencia el código */
            $model = '';
            if ($key !== '_token' && !is_null($value)) {
                list($table, $field) = explode("_", $key);
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);
                switch ($table) {
                    case 'requirements':
                        $model = PurchaseRequirement::class;
                        break;
                    case 'quotations':
                        $model = PurchaseQuotation::class;
                        break;
                    case 'states':
                        $model = PurchaseStates::class;
                        break;
                    /*case 'minutes':
                        $model = PurchaseMinute::class;
                        break;*/
                    case 'buy-orders':
                        $model = PurchaseDirectHire::class;
                        $type = 'buy';
                        break;
                    case 'service-orders':
                        $model = PurchaseOrder::class;
                        $type = 'service';
                        break;
                    /*case 'refunds':
                        $model = PurchaseRefund::class;
                        break;*/
                    default:
                        $model = null;
                        break;
                }
                $codeSetting = CodeSetting::where([
                    'module' => 'purchase',
                    'table'  => 'purchase_' . str_replace("-", "_", $table),
                    'field'  => $field,
                    'type'   => (isset($type)) ? $type : null
                ])->first();

                if (!isset($codeSetting)) {
                    $codeSetting = CodeSetting::create([
                        'module'        => 'purchase',
                        'table'         => 'purchase_' . str_replace("-", "_", $table),
                        'field'         => $field,
                        'type'          => (isset($type)) ? $type : null,
                        'format_prefix' => $prefix,
                        'format_digits' => $digits,
                        'format_year'   => $sufix,
                        'model'         => $model
                    ]);
                }

                /* Define el estatus verdadero para indicar que se ha registrado información */
                $saved = true;
            }
        }

        if ($saved) {
            $request->session()->flash('message', ['type' => 'store']);
        }

        return redirect()->back();
    }

    /**
     * Muestra información de una configuración
     *
     * @return \Illuminate\View\View
     */
    public function show()
    {
        return view('purchase::show');
    }

    /**
     * Muestra el formulario de edición de una configuración
     *
     * @return \Illuminate\View\View
     */
    public function edit()
    {
        return view('purchase::edit');
    }

    /**
     * Actualiza la información de una configuración
     *
     * @param  Request $request Datos de la petición
     *
     * @return void
     */
    public function update(Request $request)
    {
        //
    }

    /**
     * Elimina una configuración
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
