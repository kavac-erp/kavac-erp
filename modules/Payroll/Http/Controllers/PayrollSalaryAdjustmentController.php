<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Models\PayrollSalaryAdjustment;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;
use Illuminate\Support\Facades\DB;

/**
 * @class      PayrollSalaryAdjustmentController
 * @brief      Controlador de ajustes en tablas salariales
 *
 * Clase que gestiona los ajustes en tablas salariales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentController extends Controller
{
    use ValidatesRequests;

    /**
     * Arreglo con las reglas de validación sobre los datos de un formulario
     * @var Array $validateRules
     */
    protected $validateRules;

    /**
     * Arreglo con los mensajes para las reglas de validación
     * @var Array $messages
     */
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author     Henry Paredes <hparedes@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.salary-adjustments.list', ['only' => 'index']);
        $this->middleware('permission:payroll.salary-adjustments.create', ['only' => 'store']);
        $this->middleware('permission:payroll.salary-adjustments.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.salary-adjustments.delete', ['only' => 'destroy']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'created_at'                  => ['required'],
            'increase_of_date'            => ['required'],
            'payroll_salary_tabulator_id' => ['required'],
            'increase_of_type'            => ['required']
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'created_at.required'                  => 'El campo fecha de generación es obligatorio.',
            'increase_of_date.required'            => 'El campo fecha del aumento es obligatorio.',
            'payroll_salary_tabulator_id.required' => 'El campo tabulador salarial es obligatorio.',
            'increase_of_type.required'            => 'El campo tipo de aumento es obligatorio.'
        ];
    }

    /**
     * Muestra todos los registros de ajustes en tablas salariales
     *
     * @method    index
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Muestra los datos organizados en una tabla
     */
    public function index()
    {
        return view('payroll::salary_adjustments.index');
    }

    /**
     * Muestra el formulario para registrar un nuevo ajuste en las tablas salariales
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    Renderable
     */
    public function create()
    {
        return view('payroll::salary_adjustments.create');
    }

    /**
     * Muestra el formulario de actualización de ajuste en las tablas salariales
     *
     * @method    edit
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    Vista con el formulario y el objeto a actualizar
     */
    public function edit($id)
    {
        $payrollSalaryAdjustment = PayrollSalaryAdjustment::with('payrollSalaryTabulator')->find($id);
        return view('payroll::salary_adjustments.create', compact('payrollSalaryAdjustment'));
    }

    /**
     * Valida y registra una nueva nómina de sueldos
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request) {
            /**
             * Objeto asociado al modelo PayrollSalaryTabulator
             * @var Object $salaryTabulator
             */
            $salaryTabulator = PayrollSalaryAdjustment::create([
                'increase_of_date'                   => $request->input('increase_of_date'),
                'increase_of_type'                   => $request->input('increase_of_type'),
                'value'                              => $request->input('value'),
                'payroll_salary_tabulator_id'        => $request->input('payroll_salary_tabulator_id')
            ]);

            if ($salaryTabulator) {
                /** Se agregan las escalas del tabulador salarial */
                foreach ($request->payroll_salary_tabulator['payroll_salary_tabulator_scales'] as $payrollScale) {
                    foreach ($request->scale_values as $scale_value) {
                        /**
                         * Objeto asociado al modelo PayrollSalaryTabulatorScale
                         * @var Object $salaryTabulatorScale
                         */
                        $salaryTabulatorScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $request->payroll_salary_tabulator_id)->where('id', $scale_value['id'])->first();

                        $valueScale = $scale_value['value'];
                        $salaryTabulatorScale->value = (float)$valueScale;
                        $salaryTabulatorScale->save();
                    }
                }
            }
        });

        $request->session()->flash('message', ['type' => 'store']);
        return response()->json(['redirect' => route('payroll.salary-adjustments.index')], 200);
    }

    /**
     * Valida y actualiza un ajuste en tablas salariales
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $id) {
            /**
             * Objeto asociado al modelo PayrollSalaryTabulator
             * @var Object $salaryTabulator
             */
            $salaryTabulator = PayrollSalaryAdjustment::with('payrollSalaryTabulator')->find($id);
            $salaryTabulator->increase_of_date            = $request->increase_of_date;
            $salaryTabulator->increase_of_type            = $request->increase_of_type;
            $salaryTabulator->value                       = $request->value;
            $salaryTabulator->payroll_salary_tabulator_id = $request->payroll_salary_tabulator_id;
            $salaryTabulator->save();

            if ($salaryTabulator) {
                /** Se agregan las escalas del tabulador salarial */
                foreach ($request->payroll_salary_tabulator['payroll_salary_tabulator_scales'] as $payrollScale) {
                    foreach ($request->scale_values as $scale_value) {
                        /**
                         * Objeto asociado al modelo PayrollSalaryTabulatorScale
                         * @var Object $salaryTabulatorScale
                         */
                        $salaryTabulatorScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $request->payroll_salary_tabulator_id)->where('id', $scale_value['id'])->first();

                        $valueScale = $scale_value['value'];
                        $salaryTabulatorScale->value = (float)$valueScale;
                        $salaryTabulatorScale->save();
                    }
                }
            }
        });

        $request->session()->flash('message', ['type' => 'update']);
        return response()->json(['redirect' => route('payroll.salary-adjustments.index')], 200);
    }

    /**
     * Elimina un registro
     *
     * @method    destroy
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    Json con mensaje de confirmación de la operación
     */
    public function destroy($id)
    {
        $salaryAdjustment = PayrollSalaryAdjustment::find($id);
        $salaryAdjustment->delete();

        return response()->json(['record' => $salaryAdjustment, 'message' => 'Success'], 200);
    }

    /**
     * Muestra los ajustes en tabuladores salariales registrados
     *
     * @method    VueList
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Json con los ajustes en tabuladores salariales
     */
    public function vueList()
    {
        return response()->json(['records' => PayrollSalaryAdjustment::with('payrollSalaryTabulator')->get()], 200);
    }

    /**
     * Muestra los ajustes en tabuladores salariales registrados
     *
     * @method    VueList
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    Renderable    Json con los ajustes en tabuladores salariales
     */
    public function vueInfo($id)
    {
        return response()->json(['record' => PayrollSalaryAdjustment::with('payrollSalaryTabulator')->find($id)], 200);
    }
}
