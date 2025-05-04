<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Rules\CodeSetting as CodeSettingRule;
use App\Models\CodeSetting;
use App\Models\Institution;
use Modules\Payroll\Models\Parameter;

/**
 * @class PayrollSettingController
 * @brief Controlador de Configuración general en el módulo de Talento Humano
 *
 * Clase que gestiona los registros de Configuración general en el módulo de Talento Humano
 *
 * @author Henry Paredes <henryp2804@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSettingController extends Controller
{
    use ValidatesRequests;

    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        $this->middleware('permission:payroll.setting.index', ['only' => ['index', 'vueList']]);
    }
    /**
     * Muestra todos los registros de configuración del módulo de Talento Humano
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $institution = Institution::where([
            'active'  => true,
            'default' => true
        ])->first();
        $enable = isModuleEnabled('DigitalSignature');
        $codeSettings = CodeSetting::where('module', 'payroll')->get();
        $sCode  = $codeSettings->where('table', 'payroll_staffs')->first();
        $pCode  = $codeSettings->where('table', 'payrolls')->first();
        $ssCode = $codeSettings->where('table', 'payroll_salary_scales')->first();
        $stCode = $codeSettings->where('table', 'payroll_salary_tabulators')->first();
        $vRCode = $codeSettings->where('table', 'payroll_vacation_requests')->first();
        $bRCode = $codeSettings->where('table', 'payroll_benefits_requests')->first();
        $parameter = Parameter::where([
            'active' => true, 'required_by' => 'payroll', 'p_key' => 'work_age'
        ])->first();
        return view(
            'payroll::settings',
            compact('codeSettings', 'sCode', 'ssCode', 'stCode', 'vRCode', 'bRCode', 'parameter', 'institution', 'pCode')
        );
    }

    /**
     * Muestra el formulario para registrar una nueva configuración
     *
     * @return void
     */
    public function create()
    {
        //
    }

    /**
     * Almacena la nueva configuración
     *
     * @param  Request $request Datos de la petición
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        /* Reglas de validación para la configuración de códigos */
        $this->validate(
            $request,
            [
                'staffs_code'            => [new CodeSettingRule()],
                'vacation_requests_code' => [new CodeSettingRule()],
                'benefits_requests_code' => [new CodeSettingRule()],
                'salary_scales_code'     => [new CodeSettingRule()],
                'salary_tabulators_code' => [new CodeSettingRule()]
            ],
            [],
            [
                'staffs_code'            => 'código del personal',
                'salary_scales_code'     => 'código de los escalafones salariales',
                'salary_tabulators_code' => 'código de los tabuladores salariales',
                'vacation_requests_code' => 'código de las solicitudes de vacaciones',
                'benefits_requests_code' => 'código de las solicitudes de adelanto de prestaciones'
            ]
        );

        /* Arreglo con información de los campos de códigos configurados */
        $codes = $request->input();
        /* Define el estatus falso para indicar que no se ha registrado información */
        $saved = false;

        foreach ($codes as $key => $value) {
            /* Define el campo model a emplear en la generación del código */
            $model = '';

            if ($key !== '_token' && !is_null($value)) {
                list($prefix, $digits, $sufix) = CodeSetting::divideCode($value);
                /* Define el campo field */
                $field = "code";

                if ($key === "staffs_code") {
                    /* Define el campo model para asociarlo al personal */
                    $model = \Modules\Payroll\Models\PayrollStaff::class;
                    /* Define el campo table para asociarlo al personal */
                    $table = "staffs";
                } elseif ($key === "salary_scales_code") {
                    /* Define el campo model para asociarlo a los escalafones salariales */
                    $model = \Modules\Payroll\Models\PayrollSalaryScale::class;
                    /* Define el campo table para asociarlo a los escalafones salariales */
                    $table = "salary_scales";
                } elseif ($key === "salary_tabulators_code") {
                    /* Define el campo model para asociarlo a los tabuladores salariales */
                    $model = \Modules\Payroll\Models\PayrollSalaryTabulator::class;
                    /* Define el campo table para asociarlo a los tabuladores salariales */
                    $table = "salary_tabulators";
                } elseif ($key === "vacation_requests_code") {
                    /* Define el campo model para asociarlo a las solicitudes de vacaciones */
                    $model = \Modules\Payroll\Models\PayrollVacationRequest::class;
                    /* Define el campo table para asociarlo a las solicitudes de vacaciones */
                    $table = "vacation_requests";
                } elseif ($key === "benefits_requests_code") {
                    /* Define el campo model para asociarlo a las solicitudes de prestaciones */
                    $model = \Modules\Payroll\Models\PayrollBenefitsRequest::class;
                    /* Define el campo table para asociarlo a las solicitudes de prestaciones */
                    $table = "benefits_requests";
                } elseif ($key === "payrolls_code") {
                    /* Define el campo model para asociarlo a las solicitudes de prestaciones */
                    $model = \Modules\Payroll\Models\Payroll::class;
                    /* Define el campo table para asociarlo a las solicitudes de prestaciones */
                    $table = "payrolls";
                }

                $codeSetting = CodeSetting::where([
                    'module' => 'payroll',
                    'table'  => $table === 'payrolls' ? $table : 'payroll_' . $table,
                    'field'  => $field
                ])->first();

                if (!isset($codeSetting)) {
                    $codeSetting = CodeSetting::create([
                        'module'        => 'payroll',
                        'table'         => $table === 'payrolls' ? $table : 'payroll_' . $table,
                        'field'         => $field,
                        'format_prefix' => $prefix,
                        'format_digits' => $digits,
                        'format_year'   => $sufix,
                        'model'         => $model,
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
}
