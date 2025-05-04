<?php

namespace Modules\Payroll\Http\Controllers;

use Carbon\Carbon;
use App\Models\FiscalYear;
use App\Models\CodeSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;
use Modules\Payroll\Models\PayrollStaffType;
use Modules\Payroll\Rules\PayrollSalaryScales;
use Modules\Payroll\Imports\SalaryTabulatorImport;
use Modules\Payroll\Models\PayrollSalaryTabulator;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;
use Modules\Payroll\Exports\PayrollSalaryTabulatorExport;

/**
 * @class      PayrollSalaryTabulatorController
 * @brief      Controlador de los tabuladores salariales
 *
 * Clase que gestiona los tabuladores salariales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorController extends Controller
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
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador

        $this->middleware('permission:payroll.setting.salary.tabulator.create', ['only' => 'store']);
        $this->middleware('permission:payroll.setting.salary.tabulator.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.setting.salary.tabulator.delete', ['only' => 'destroy']);
        $this->middleware('permission:payroll.salary.tabulators.import', ['only' => 'import']);
        $this->middleware('permission:payroll.salary.tabulators.export', ['only' => 'export']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'                            => ['required', 'max:100', 'unique:payroll_salary_tabulators,name'],
            'institution_id'                  => ['required'],
            'payroll_salary_tabulator_type'   => ['required'],
            'payroll_salary_tabulator_scales' => ['required', new PayrollSalaryScales()]
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'currency_id.required'                     => 'El campo moneda es obligatorio.',
            'institution_id.required'                  => 'El campo organización es obligatorio.',
            'payroll_salary_tabulator_type.required'   => 'El campo tipo de tabulador es obligatorio.',
            'payroll_salary_tabulator_scales.required' => 'Las escalas del tabulador salarial son obligatorias.'
        ];
    }

    /**
     * Muestra un listado de los tabuladores salariales registrados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        $payrollSalaryTabulators = PayrollSalaryTabulator::with([
            'payrollVerticalSalaryScale' => function ($query) {
                $query->with('payrollScales')->get();
            },
            'payrollHorizontalSalaryScale' => function ($query) {
                $query->with('payrollScales')->get();
            },
            'payrollSalaryTabulatorScales'
        ])->get();
        foreach ($payrollSalaryTabulators as $payrollSalaryTabulator) {
            $payrollStaffTypes = [];
            if ($payrollSalaryTabulator->payrollStaffTypes) {
                foreach ($payrollSalaryTabulator->payrollStaffTypes as $payrollStaffType) {
                    $payrollStaffType['text'] = $payrollStaffType['name'];
                }
            }
        }
        return response()->json(['records' => $payrollSalaryTabulators], 200);
    }

    /**
     * Valida y registra un nuevo tabulador salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $validateRules = $this->validateRules;
        if ($request->percentage == false) {
            $validateRules = array_merge($validateRules, [
                'currency_id'             => ['required'],
            ]);
        }
        $this->validate($request, $validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'payroll_salary_tabulators')->first();
        if (is_null($codeSetting)) {
            $request->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'Debe configurar previamente el formato para el código a generar'
            ]);
            return response()->json(['result' => false, 'redirect' => route('payroll.settings.index')], 200);
        }

        $currentFiscalYear = FiscalYear::select('year')
            ->where(['active' => true, 'closed' => false])->orderBy('year', 'desc')->first();

        $code  = generate_registration_code(
            $codeSetting->format_prefix,
            strlen($codeSetting->format_digits),
            (strlen($codeSetting->format_year) == 2) ? (isset($currentFiscalYear) ?
                substr($currentFiscalYear->year, 2, 2) : date('y')) : (isset($currentFiscalYear) ?
                $currentFiscalYear->year : date('Y')),
            PayrollSalaryTabulator::class,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code) {
            /* Objeto asociado al modelo PayrollSalaryTabulator */
            $salaryTabulator = PayrollSalaryTabulator::create([
                'code'                               => $code,
                'name'                               => $request->input('name'),
                'active'                             => !empty($request->input('active'))
                    ? $request->input('active')
                    : false,
                'percentage'                         => !empty($request->input('percentage'))
                    ? $request->input('percentage')
                    : false,
                'description'                        => $request->input('description') ?? '',
                'institution_id'                     => $request->input('institution_id'),
                'currency_id'                        => $request->input('currency_id'),
                'payroll_salary_tabulator_type'      => $request->input('payroll_salary_tabulator_type'),
                'payroll_vertical_salary_scale_id'   => $request->input('payroll_vertical_salary_scale_id'),
                'payroll_horizontal_salary_scale_id' => $request->input('payroll_horizontal_salary_scale_id')
            ]);

            if ($salaryTabulator) {
                /** Se agregan las escalas del tabulador salarial */
                foreach ($request->payroll_salary_tabulator_scales as $payrollScale) {
                    /* Objeto asociado al modelo PayrollSalaryTabulatorScale */
                    $salaryTabulatorScale = PayrollSalaryTabulatorScale::create([
                        'value'                       => $payrollScale['value'],
                        'payroll_vertical_scale_id'   => $payrollScale['payroll_vertical_scale_id'] ?? null,
                        'payroll_horizontal_scale_id' => $payrollScale['payroll_horizontal_scale_id'] ?? null,
                        'payroll_salary_tabulator_id' => $salaryTabulator->id
                    ]);
                }
            }
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Actualiza la información del tabulador salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id         Identificador único asociado al tabulador salarial
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse|void           Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $salaryTabulator = PayrollSalaryTabulator::where('id', $id)->first();
        $validateRules = $this->validateRules;
        if ($request->percentage == false) {
            $validateRules = array_merge($validateRules, [
                'currency_id'             => ['required'],
            ]);
        }
        $validateRules  = array_replace(
            $validateRules,
            ['name' => ['required', 'max:100', 'unique:payroll_salary_tabulators,name,' . $salaryTabulator->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        DB::transaction(function () use ($request, $salaryTabulator) {
            $salaryTabulator->update([
                'name'                               => $request->input('name'),
                'active'                             => !empty($request->input('active'))
                    ? $request->input('active')
                    : false,
                'percentage'                         => !empty($request->input('percentage'))
                    ? $request->input('percentage')
                    : false,
                'description'                        => $request->input('description') ?? '',
                'institution_id'                     => $request->input('institution_id'),
                'currency_id'                        => $request->input('currency_id'),
                'payroll_salary_tabulator_type'      => $request->input('payroll_salary_tabulator_type'),
                'payroll_vertical_salary_scale_id'   => $request->input('payroll_vertical_salary_scale_id'),
                'payroll_horizontal_salary_scale_id' => $request->input('payroll_horizontal_salary_scale_id')
            ]);


            /* Se eliminan las demas escalas asociadas al tabulador */
            $salaryTabulator->payrollSalaryTabulatorScales()->forceDelete();

            /* Se agregan o actualizan las escalas del tabulador salarial */
            foreach ($request->payroll_salary_tabulator_scales as $payrollScale) {
                /* Objeto asociado al modelo PayrollSalaryTabulatorScale */
                $salaryTabulatorScale = PayrollSalaryTabulatorScale::updateOrCreate(
                    [
                        'payroll_vertical_scale_id'   => $payrollScale['payroll_vertical_scale_id'] ?? null,
                        'payroll_horizontal_scale_id' => $payrollScale['payroll_horizontal_scale_id'] ?? null,
                        'payroll_salary_tabulator_id' => $salaryTabulator->id
                    ],
                    [
                        'value'                       => $payrollScale['value'],
                        'deleted_at'                  => null
                    ]
                );
            }
        });
    }

    /**
     * Elimina un tabulador salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Modules\Payroll\Models\PayrollSalaryTabulator    $salaryTabulator    Datos del tabulador salarial
     *
     * @return    \Illuminate\Http\JsonResponse                     Objeto con los registros a mostrar
     */
    public function destroy(PayrollSalaryTabulator $salaryTabulator)
    {
        $salaryTabulator->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene el listado de los tabuladores salariales registrados a implementar en elementos select
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Listado de registros a mostrar
     */
    public function getSalaryTabulators()
    {
        return template_choices('Modules\Payroll\Models\PayrollSalaryTabulator', 'name', ['active' => 't'], true);
    }

    /**
     * Método para subir datos del tabulador salarial al formulario de ajustes en tablas salariales
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @author    Fabián Palmera <fapalmera@cenditel.gob.ve>
     *
     * @return \Illuminate\Http\JsonResponse Objeto con los registros a mostrar
     */
    public function import(Request $request)
    {
        request()->validate([
            'file' => 'required|mimes:xlx,xls,xlsx'
        ]);

        $rows = Excel::toCollection(new SalaryTabulatorImport(), request()->file('file'));
        $rows = $rows[0];
        return response()->json($rows);
    }

    /**
     * Exporta un tabulador salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único asociado al tabulador salarial
     *
     * @return    \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export($id)
    {
        $payrollSalaryTabulator = PayrollSalaryTabulator::where('id', $id)->first();
        if ($payrollSalaryTabulator) {
            $export = new PayrollSalaryTabulatorExport(PayrollSalaryTabulator::class);
            $export->setSalaryTabulatorId($payrollSalaryTabulator->id);
            return Excel::download($export, 'tabulador_salarial_' . Carbon::parse($payrollSalaryTabulator->created_at)->format('d-m-Y') . '.xlsx');
        }

        return response()->json(['message' => 'error'], 400);
    }

    /**
     * Obtiene la información de un tabulador salarial registrado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del registro de nómina
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function show($id)
    {
        /* Objeto asociado al modelo PayrollSalaryTabulator */
        $payrollSalaryTabulator = PayrollSalaryTabulator::with([
            'payrollVerticalSalaryScale' => function ($query) {
                $query->with('payrollScales')->get();
            }, 'payrollHorizontalSalaryScale' => function ($query) {
                $query->with('payrollScales')->get();
            }, 'payrollSalaryTabulatorScales',
            'payrollSalaryAdjustments' => function ($query) {
                $query->with(['payrollHistorySalaryAdjustments' => function ($query) {
                    $query->orderBy('created_at', 'desc')->get();
                }]);
            }
        ])->find($id);
        return response()->json(['record' => $payrollSalaryTabulator], 200);
    }
}
