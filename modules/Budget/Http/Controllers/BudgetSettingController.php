<?php

namespace Modules\Budget\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Models\CodeSetting;
use App\Rules\CodeSetting as CodeSettingRule;
use Modules\Budget\Models\BudgetCompromise;
use Modules\Budget\Models\BudgetProject;
use Modules\Budget\Models\BudgetSubSpecificFormulation;
use Modules\Budget\Models\BudgetModification;

/**
 * @class BudgetSettingController
 * @brief Controlador de configuraciones en el módulo de Presupuesto
 *
 * Clase que gestiona las configuraciones en el módulo de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetSettingController extends Controller
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
        $this->middleware('permission:budget.setting.list', ['only' => 'index', 'vueList']);
        $this->middleware('permission:budget.setting.create', ['only' => ['create', 'store']]);
        $this->middleware('permission:budget.setting.edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:budget.setting.delete', ['only' => 'destroy']);
    }

    /**
     * Muestra un listado de configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return Renderable
     */
    public function index()
    {
        /* Contiene los registros de proyectos */
        $projects = BudgetProject::all();

        /* Contiene los registros de configuraciones de códigos tomando en cuenta Presupuesto y Compras */
        $codeSettings = CodeSetting::where('module', 'budget')->orWhere('module', 'purchase')->get();

        /* Contiene información sobre la configuración de código para la formulación */
        $fCode = $codeSettings->where('table', 'budget_formulations')->first();

        /* Contiene información sobre la configuración de código para los compromisos */
        $cCode = $codeSettings->where('table', 'budget_compromises')->first();

        /* Contiene información sobre la configuración de código para los créditos adicionales */
        $crCode = $codeSettings->where('table', 'budget_modifications')
            ->where('type', 'budget.aditional-credits')->first();

        /* Contiene información sobre la configuración de código para las reducciones presupuestarias */
        $rCode = $codeSettings->where('table', 'budget_modifications')->where('type', 'budget.reductions')->first();

        /* Contiene información sobre la configuración de código para las transferencias entre presupuestos */
        $tCode = $codeSettings->where('table', 'budget_modifications')->where('type', 'budget.transfers')->first();

        /* Contiene información sobre la configuración de código para los causados presupuestarios */
        $caCode = $codeSettings->where('table', 'budget_caused')->first();

        /* Contiene información sobre la configuración de código para los pagados presupuestarios */
        $pCode = $codeSettings->where('table', 'budget_payed')->first();

        /* Contiene información sobre la configuración de código para la disponibilidad presupuestaria manual */
        $bamCode = $codeSettings->where('table', 'purchase_budgetary_availabilities')->first();

        return view('budget::settings', compact(
            'projects',
            'fCode',
            'cCode',
            'tCode',
            'rCode',
            'crCode',
            'caCode',
            'pCode',
            'bamCode'
        ));
    }

    /**
     * Muestra un formulario para la creación de las configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Guarda información de las configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  Request $request Datos de la petición
     *
     * @return Renderable
     */
    public function store(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $request->validate([
            'formulations_code' => [new CodeSettingRule()],
            'compromises_code' => [new CodeSettingRule()],
            'caused_code' => [new CodeSettingRule()],
            'payed_code' => [new CodeSettingRule()],
            'transfers_code' => [new CodeSettingRule()],
            'reductions_code' => [new CodeSettingRule()],
            'credits_code' => [new CodeSettingRule()],
            'budgetary_availabilities_code' => [new CodeSettingRule()],
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

                if ($table === "formulations") {
                    /* Define el modelo asociado a la formulación de presupuesto */
                    $model = BudgetSubSpecificFormulation::class;
                } elseif (in_array($key, ['transfers_code', 'reductions_code', 'credits_code'])) {
                    /* Define el modelo asociado a las modificaciones presupuestarias */
                    $model = BudgetModification::class;
                    /* Define la tabla asociada a las modificaciones presupuestarias */
                    $table = 'modifications';
                    if ($key === 'transfers_code') {
                        /* Define el tipo de registro como transferencia entre presupuestos */
                        $type = 'budget.transfers';
                    } elseif ($key === 'reductions_code') {
                        /* Define el tipo de registro como reducciones presupuestarias */
                        $type = 'budget.reductions';
                    } elseif ($key === 'credits_code') {
                        /* Define el tipo de registro como créditos adicionales */
                        $type = 'budget.aditional-credits';
                    }
                } elseif ($table === "compromises") {
                    $model = BudgetCompromise::class;
                } elseif ($key === 'budgetary_availabilities_code') {
                    /* Define el modelo asociado a las disponibilidades presupuestarias */
                    $model = \Modules\Purchase\Models\PurchaseBudgetaryAvailability::class;
                }

                // Si el caso es budgetary_availabilities_code.
                if ($key === 'budgetary_availabilities_code') {
                    // Buscar en la base de datos la existencia de un código igual.
                    $codeSetting = CodeSetting::where([
                        'module' => 'purchase',
                        'table'  => 'purchase_budgetary_availabilities',
                        'field'  => 'code',
                        'type'   => null
                    ])->first();

                    /* Si no se encuentra ninguna configuración de código que
                    coincida se crea un registro utilizando el método create()
                    del modelo CodeSetting.
                    */
                    if (!isset($codeSetting)) {
                        $codeSetting = CodeSetting::create([
                            'module'        => 'purchase',
                            'table'         => 'purchase_budgetary_availabilities',
                            'field'         => 'code',
                            'type'          => null,
                            'format_prefix' => $prefix,
                            'format_digits' => $digits,
                            'format_year'   => $sufix,
                            'model'         => $model
                        ]);
                    }
                } else {
                    // Otros casos que no sean de budgetary_availabilities_code.
                    $codeSetting = CodeSetting::where([
                        'module' => 'budget',
                        'table'  => 'budget_' . $table,
                        'field'  => $field,
                        'type'   => (isset($type)) ? $type : null
                    ])->first();

                    if (!isset($codeSetting)) {
                        $codeSetting = CodeSetting::create([
                            'module'        => 'budget',
                            'table'         => 'budget_' . $table,
                            'field'         => $field,
                            'type'          => (isset($type)) ? $type : null,
                            'format_prefix' => $prefix,
                            'format_digits' => $digits,
                            'format_year'   => $sufix,
                            'model'         => $model
                        ]);
                    }
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
     * Muestra información con las configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function show()
    {
        //
    }

    /**
     * Muestra el formulario para editar información de configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function edit()
    {
        //
    }

    /**
     * Actualiza información de configuración de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
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
     * Elimina configuraciones de presupuesto
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return void
     */
    public function destroy()
    {
        //
    }
}
