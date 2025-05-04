<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\DB;
use Modules\Payroll\Models\PayrollExceptionType;
use Modules\Payroll\Models\PayrollParameterTimeSheetParameter;
use Modules\Payroll\Models\PayrollPaymentTypeTimeSheetParameter;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollTimeSheetParameter;
use Modules\Payroll\Models\PayrollTimeSheetPending;

/**
 * @class PayrollTimeSheetParameterController
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetParameterController extends Controller
{
    use ValidatesRequests;

    protected $validateRules;
    protected $messages;

    /**
     * Define la configuración de la clase
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        $this->middleware('permission:payroll.time_sheet_parameter.index', ['only' => 'index']);
        $this->middleware('permission:payroll.time_sheet_parameter.create', ['only' => ['store']]);
        $this->middleware('permission:payroll.time_sheet_parameter.edit', ['only' => ['update']]);
        $this->middleware('permission:payroll.time_sheet_parameter.delete', ['only' => 'destroy']);

        /** Define las reglas de validación para el formulario */
        $this->validateRules = [
            'code' => ['required', 'unique:payroll_time_sheet_parameters,code'],
            'name' => ['required'],
            'time_parameters' => ['required'],
            'payment_types' => ['required']
        ];

        /** Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required'        => 'El campo código es obligatorio.',
            'code.unique'          => 'El campo código ya ha sido registrado.',
            'name.required' => 'El campo nombre es obligatorio.',
            'time_parameters.required' => 'El campo parámetros es obligatorio.',
            'payment_types.required' => 'El campo tipos de pago es obligatorio.'
        ];
    }

    /**
     * [descripción del método]
     *
     * @method    index
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function index()
    {
        return response()->json(['records' => PayrollTimeSheetParameter::query()
            ->with([
                'payrollParameterTimeSheetParameters.parameter',
                'payrollPaymentTypeTimeSheetParameters.payrollPaymentType'
            ])
            ->get()], 200);
    }

    /**
     * [descripción del método]
     *
     * @method    create
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function create()
    {
        return view('payroll::create');
    }

    /**
     * [descripción del método]
     *
     * @method    store
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request    Objeto con información de la petición
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function store(Request $request)
    {
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request) {
            $payrollTimeSheetParameter = PayrollTimeSheetParameter::create([
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description
            ]);

            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }

            return response()->json(['record' => $payrollTimeSheetParameter, 'message' => 'Success'], 200);
        });
    }

    /**
     * [descripción del método]
     *
     * @method    show
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function show($id)
    {
        return view('payroll::show');
    }

    /**
     * [descripción del método]
     *
     * @method    edit
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function edit($id)
    {
        return view('payroll::edit');
    }

    /**
     * [descripción del método]
     *
     * @method    update
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     object    Request    $request         Objeto con datos de la petición
     * @param     integer   $id        Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function update(Request $request, $id)
    {
        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);
        $this->validateRules['code'] = [
            'required',
            'unique:payroll_time_sheet_parameters,code,' . $payrollTimeSheetParameter->id
        ];
        $this->validate($request, $this->validateRules, $this->messages);

        DB::transaction(function () use ($request, $payrollTimeSheetParameter) {
            $payrollTimeSheetParameter->code = $request->code;
            $payrollTimeSheetParameter->name = $request->name;
            $payrollTimeSheetParameter->description = $request->description;
            $payrollTimeSheetParameter->save();

            PayrollParameterTimeSheetParameter::query()
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->delete();

            PayrollPaymentTypeTimeSheetParameter::query()
                ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
                ->delete();

            foreach ($request->time_parameters as $parameter) {
                PayrollParameterTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'parameter_id' => $parameter['id']
                ]);
            }

            foreach ($request->payment_types as $type) {
                PayrollPaymentTypeTimeSheetParameter::create([
                    'payroll_time_sheet_parameter_id' => $payrollTimeSheetParameter->id,
                    'payroll_payment_type_id' => $type['id']
                ]);
            }

            return response()->json(['message' => 'Success'], 200);
        });
    }

    /**
     * [descripción del método]
     *
     * @method    destroy
     *
     * @author    [nombre del autor] [correo del autor]
     *
     * @param     integer    $id    Identificador del registro
     *
     * @return    Renderable    [descripción de los datos devueltos]
     */
    public function destroy($id)
    {
        $timeSheet = PayrollTimeSheet::query()
            ->where('payroll_time_sheet_parameter_id', $id)
            ->first();

        $timeSheetPending = PayrollTimeSheetPending::query()
            ->where('payroll_time_sheet_parameter_id', $id)
            ->first();

        if ($timeSheet || $timeSheetPending) {
            return response()->json(['error' => true, 'message' => __('No se puede eliminar los parámetros de hoja' .
                ' de tiempo debido a que tiene una hoja de tiempo asociada')], 200);
        }

        $payrollTimeSheetParameter = PayrollTimeSheetParameter::find($id);

        $parameters = PayrollParameterTimeSheetParameter::query()
            ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
            ->get();

        foreach ($parameters as $parameter) {
            $parameter->delete();
        }

        $paymentTypes = PayrollPaymentTypeTimeSheetParameter::query()
            ->where('payroll_time_sheet_parameter_id', $payrollTimeSheetParameter->id)
            ->get();

        foreach ($paymentTypes as $paymentType) {
            $paymentType->delete();
        }

        $payrollTimeSheetParameter->delete();

        return response()->json(['record' => $payrollTimeSheetParameter, 'message' => 'Success'], 200);
    }

    public function getPayrollTimeSheetParameters()
    {
        $parameters = PayrollTimeSheetParameter::query()
            ->with('payrollParameterTimeSheetParameters.parameter')
            ->get();

        $records = [];

        foreach ($parameters as $key => $parameter) {
            $records[$key] = [
                'id' => $parameter->id,
                'text' => $parameter->code,
                'parameters' => []
            ];

            foreach ($parameter->payrollParameterTimeSheetParameters as $param) {
                $pValue = json_decode($param->parameter->p_value);
                $exceptionType = PayrollExceptionType::find($pValue->exception_type);

                $records[$key]['parameters'][$exceptionType->name][] = [
                    'id' => $param->parameter->id,
                    'group' => $exceptionType->name,
                    'max' => $exceptionType->value_max ?? null,
                    'affectGroup' => $exceptionType->affect?->name,
                    'text' => $pValue->acronym . ' - ' . $pValue->name,
                ];
            }
        }

        $data = array_merge(
            [
                [
                    'id' => '',
                    'text' => 'Seleccione...',
                ]
            ],
            $records
        );

        return response()->json(
            $data,
            200
        );
    }
}
