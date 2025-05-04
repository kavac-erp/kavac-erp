<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Rules\CodeSetting as CodeSettingRule;
use Nwidart\Modules\Facades\Module;
use App\Models\Parameter;

/**
 * @class CloseFiscalYearSettingController
 * @brief Controlador de configuraciones para el cierre de ejercicio
 *
 * Clase que gestiona las configuraciones para el cierre de ejercicio
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
 *
 * @license
 *      [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CloseFiscalYearSettingController extends Controller
{
    /**
     * Método constructor de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware(
            'permission:closefiscalyear.setting',
            ['only' => ['index', 'store', 'storeAccount', 'getAccountingAccount']]
        );
    }

    /**
     * Muestra la configuración de códigos para el cierre de ejercicio fiscal
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $codeSettings = CodeSetting::where('module', 'base')->get();
        // Contiene información sobre la configuración de código para la formulación
        $code = $codeSettings->where('table', 'entries')->first();
        return view('close-fiscal-year.settings', compact('code'));
    }

    /**
     * Muestra el formulario para la creación de un nuevo registro
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena la configuración del código para el cierre de ejercicio fiscal
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve> | <exodiadaniel@gmail.com>
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Reglas de validación para la configuración de códigos
        $request->validate(['code' => [new CodeSettingRule()]]);

        // Arreglo con información de los campos de códigos configurados
        $codes = $request->input();

        // Define el estatus verdadero para indicar que no se ha registrado información
        $saved = false;

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            foreach ($codes as $key => $value) {
                // Define el modelo al cual hace referencia el código
                $model = '';

                if ($key !== '_token' && !is_null($value)) {
                    list($table, $field) = explode("_", $key);
                    list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                    if ($table === "entries") {
                        // Define el modelo para asociado a la formulación de presupuesto
                        $model = \Modules\Accounting\Models\AccountingEntry::class;
                    }

                    $codeSetting = CodeSetting::where([
                        'module' => 'base',
                        'table'  => $table,
                        'field'  => $field,
                        'type'   => (isset($type)) ? $type : null
                    ])->first();

                    if (!isset($codeSetting)) {
                        $codeSetting = CodeSetting::create([
                            'module'        => 'base',
                            'table'         => $table,
                            'field'         => $field,
                            'type'          => (isset($type)) ? $type : null,
                            'format_prefix' => $prefix,
                            'format_digits' => $digits,
                            'format_year'   => $sufix,
                            'model'         => $model
                        ]);
                    }
                    // Define el estatus verdadero para indicar que se ha registrado información
                    $saved = true;
                }
            }

            if ($saved) {
                $request->session()->flash('message', ['type' => 'store']);
            }
        } else {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => __('¡Atención!'),
                'text' => __('Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad'),
                'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);
        }

        return redirect()->back();
    }

    /**
     * Almacena la configuración de la cuenta contable para el cierre de ejercicio fiscal
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return \Illuminate\Http\Response
     */
    public function storeAccount(Request $request)
    {
        // Reglas de validación para la configuración de códigos
        $request->validate([
            'accounting_account_id' => 'required'
        ], [
            'accounting_account_id.required' => __('El campo cuenta contable es obligatorio')
        ]);

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            Parameter::updateOrCreate(
                [
                    'p_key' => 'close_fiscal_year_account',
                ],
                [
                    'p_value' => $request->accounting_account_id,
                    'required_by' => 'base',
                    'active' => 't'
                ]
            );

            $request->session()->flash('message', ['type' => 'store']);
        } else {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => __('¡Atención!'),
                'text' => __('Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad'),
                'icon' => 'screen-error',
                'class' => 'growl-danger'
            ]);
        }

        return response()->json([
            'result' => true,
            'redirect' => route('close-fiscal-year.settings.index')
        ], 200);
    }

    /**
     * Obtiene la cuenta contable para el cierre de ejercicio fiscal
     *
     * @return \Illuminate\Http\Response|void
     */
    public function getAccountingAccount()
    {
        $accounting_account = null;
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $parameter = Parameter::where('p_key', 'close_fiscal_year_account')->first();
            if ($parameter) {
                $accounting_account = \Modules\Accounting\Models\AccountingAccount::where(
                    'id',
                    $parameter->p_value
                )->first();
            }
            return $accounting_account;
        } else {
            session()->flash(
                'message',
                [
                    'type' => 'other',
                    'title' => __('¡Atención!'),
                    'text' => __('Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad'),
                    'icon' => 'screen-error',
                    'class' => 'growl-danger'
                ]
            );
        }
    }
}
