<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
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
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class CloseFiscalYearSettingController extends Controller
{
    public function __construct()
    {
        /**
         * Establece permisos de acceso para cada método del controlador
         */
        $this->middleware(
            'permission:close_fiscal_year.setting',
            ['only' => ['index', 'store', 'storeAccount', 'getAccountingAccount']]
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $codeSettings = CodeSetting::where('module', 'base')->get();
        /** @var object Contiene información sobre la configuración de código para la formulación */
        $code = $codeSettings->where('table', 'entries')->first();
        return view('close-fiscal-year.settings', compact('code'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** Reglas de validación para la configuración de códigos */
        $request->validate(['code' => [new CodeSettingRule()]]);

        /** @var array Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();

        /** @var boolean Define el estatus verdadero para indicar que no se ha registrado información */
        $saved = false;

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            foreach ($codes as $key => $value) {
                /** @var string Define el modelo al cual hace referencia el código */
                $model = '';

                if ($key !== '_token' && !is_null($value)) {
                    list($table, $field) = explode("_", $key);
                    list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);

                    if ($table === "entries") {
                        /** @var string Define el modelo para asociado a la formulación de presupuesto */
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
                    /** @var boolean Define el estatus verdadero para indicar que se ha registrado información */
                    $saved = true;
                }
            }

            if ($saved) {
                $request->session()->flash('message', ['type' => 'store']);
            }
        } else {
            $request->session()->flash('message', ['type' => 'other', 'title' => '¡Atención!',
                                                   'text' => 'Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad',
                                                   'icon' => 'screen-error',
                                                   'class' => 'growl-danger']);
        }

        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAccount(Request $request)
    {
        /** Reglas de validación para la configuración de códigos */
        $request->validate(['accounting_account_id' => 'required'], ['accounting_account_id.required' => 'El campo cuenta contable es obligatorio']);

        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            $parameter = Parameter::updateOrCreate(
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
            $request->session()->flash('message', ['type' => 'other', 'title' => '¡Atención!',
                                                   'text' => 'Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad',
                                                   'icon' => 'screen-error',
                                                   'class' => 'growl-danger']);
        }

        return response()->json(['result' => true, 'redirect' => route('close-fiscal-year.settings.index')], 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
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
                    'title' => '¡Atención!',
                    'text' => 'Debe tener el módulo de contabilidad instalado para acceder a esta funcionalidad',
                    'icon' => 'screen-error',
                    'class' => 'growl-danger'
                ]
            );
        }
    }
}
