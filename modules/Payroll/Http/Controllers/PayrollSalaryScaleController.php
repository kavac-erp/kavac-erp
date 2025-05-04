<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSalaryScale;
use Modules\Payroll\Models\PayrollScale;
use App\Models\Institution;
use App\Models\CodeSetting;
use App\Models\FiscalYear;

/**
 * @class      PayrollSalaryScaleController
 * @brief      Controlador de los escalafones salariales
 *
 * Clase que gestiona los escalafones salariales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryScaleController extends Controller
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
        $this->middleware('permission:payroll.setting.salary.scale.create', ['only' => 'store']);
        $this->middleware('permission:payroll.setting.salary.scale.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.setting.salary.scale.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'name'           => ['required', 'unique:payroll_salary_scales,name'],
            'institution_id' => ['required'],
            'payroll_scales' => ['required'],
            // 'payroll_scales.*.name' => ['unique:payroll_scales,name']
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'institution_id.required' => 'El campo organización es obligatorio.',
            'payroll_scales.required' => 'Debe registrar al menos una escala o nivel.',
            // 'payroll_scales.*.name.unique' => 'El nombre de la escala ya ha sido registrado.'
        ];
    }

    /**
     * Muestra un listado de los escalafones salariales registrados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(['records' => PayrollSalaryScale::with('payrollScales')->get()], 200);
    }

    /**
     * Valida y registra un nuevo escalafón salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        $codeSetting = CodeSetting::where('table', 'payroll_salary_scales')->first();
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
            PayrollSalaryScale::class,
            $codeSetting->field
        );

        DB::transaction(function () use ($request, $code) {
            /* Objeto asociado al modelo PayrollSalaryScale */
            $salaryScale = PayrollSalaryScale::create([
                'code'                   => $code,
                'name'                   => $request->input('name'),
                'active'                 => !empty($request->input('active'))
                    ? $request->input('active')
                    : false,
                'description'            => $request->input('description'),
                'institution_id'         => $request->input('institution_id'),
                'group_by'               => $request->input('group_by'),
                'type'                   => $request->input('type'),
            ]);
            foreach ($request->payroll_scales as $payrollScale) {
                /* Objeto asociado al modelo PayrollScale */
                $scale = PayrollScale::create([
                    'name'                    => $payrollScale['name'],
                    'value'                   => json_encode($payrollScale['value']),
                    'payroll_salary_scale_id' => $salaryScale->id,
                ]);
            }
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Actualiza la información del escalafón salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id         Identificador único asociado al escalafón salarial
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $payrollSalaryScale = PayrollSalaryScale::find($id);

        $this->validateRules = [
            'name'           => ['required', 'unique:payroll_salary_scales,name,' . $payrollSalaryScale->id],
            'institution_id' => ['required'],
            'payroll_scales' => ['required']
        ];

        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollSalaryScale) {
            $payrollSalaryScale->name                   = $request->input('name');
            $payrollSalaryScale->description            = $request->input('description');
            $payrollSalaryScale->institution_id         = $request->input('institution_id');
            $payrollSalaryScale->active                 = !empty($request->input('active'))
                ? $request->input('active')
                : false;
            $payrollSalaryScale->group_by               = $request->input('group_by');
            $payrollSalaryScale->type                   = $request->input('type');

            $payrollSalaryScale->save();
            $payrollSalaryScale->payrollScales()->delete();

            /* Se agregan los nuevos niveles o escalas y se actualizan los existentes */
            foreach ($request->payroll_scales as $payrollScale) {
                if ($payrollScale['id'] > 0) {
                    $scale              = PayrollScale::withTrashed()->find($payrollScale['id']);
                    $scale->name        = $payrollScale['name'];
                    $scale->value       = is_object($payrollScale['value']) ? json_encode($payrollScale['value']) : $payrollScale['value'];
                    $scale->deleted_at  = null;
                    $scale->save();
                } else {
                    $scale = PayrollScale::create([
                        'name'                    => $payrollScale['name'],
                        'value'                   => json_encode($payrollScale['value']),
                        'payroll_salary_scale_id' => $payrollSalaryScale->id,
                    ]);
                }
            }
        });
        return response()->json(['result' => true], 200);
    }

    /**
     * Elimina un escalafón salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Modules\Payroll\Models\PayrollSalaryScale    $salaryScale    Datos del escalafón salarial
     *
     * @return    \Illuminate\Http\JsonResponse                                 Objeto con los registros a mostrar
     */
    public function destroy(PayrollSalaryScale $salaryScale)
    {
        $salaryScale->delete();
        return response()->json(['message' => 'destroy'], 200);
    }

    /**
     * Obtiene el listado de los escalafones salariales a implementar en elementos select
     *
     * @method    getSalaryScales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getSalaryScales(Request $request)
    {
        /* Falta incluir el except_id en templatechoices */
        $institution  = $request->institution_id ?? Institution::where([
            'default' => true,
            'active'  => true
        ])->first();
        return template_choices(
            'Modules\Payroll\Models\PayrollSalaryScale',
            'name',
            ['institution_id' => $request->institution_id ?? $institution->id, 'active' => 't'],
            true
        );
    }

    /**
     * Obtiene la información de un escalafón salarial
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer                          $id    Identificador único sociado al escalafón salarial
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function info($id)
    {
        $record = PayrollSalaryScale::where('id', $id)->with('payrollScales')->first();

        $parameter = new PayrollParameterController();

        $options = $parameter->getPayrollParameterOptions($record->group_by);
        $options_format = collect($options)->filter(fn ($item) => !empty($item["id"]))->pluck("text", "id")->toArray();

        return response()->json(["record" => $record, "payroll_options" => $options_format], 200);
    }
}
