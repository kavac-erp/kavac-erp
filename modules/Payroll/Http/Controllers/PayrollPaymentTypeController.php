<?php

namespace Modules\Payroll\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Parameter;
use Modules\Payroll\Models\Institution;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Models\PayrollPaymentType;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Http\Resources\PaymentTypeResource;
use Modules\Payroll\Models\PayrollSalaryTabulatorScale;

/**
 * @class      PayrollPaymentTypeController
 * @brief      Controlador de tipos de pago
 *
 * Clase que gestiona los tipos de pago
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPaymentTypeController extends Controller
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
     * Objeto que contiene la información asociada a la solicitud
     *
     * @var object|array $data
     */
    protected $data;

    /**
     * Define la configuración de la clase
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        //$this->middleware('permission:payroll.payment.types.list',   ['only' => 'index']);
        $this->middleware('permission:payroll.payment.types.create', ['only' => 'store']);
        $this->middleware('permission:payroll.payment.types.edit', ['only' => 'update']);
        $this->middleware('permission:payroll.payment.types.delete', ['only' => 'destroy']);

        /* Define las reglas de validación para el formulario */
        $this->validateRules = [
            'code' => ['required', 'unique:payroll_payment_types,code'],
            'name' => ['required'],
            'payment_periodicity' => ['required'],
            'start_date' => ['required'],
            //'payment_relationship'  => ['required'],
            'finance_bank_account_id' => ['required'],
            'payroll_concepts' => ['required'],
            'accounting_entry_category_id' => ['required'],
        ];

        /* Define los mensajes de validación para las reglas del formulario */
        $this->messages = [
            'code.required' => 'El campo código es obligatorio.',
            'code.unique' => 'El campo código ya ha sido registrado.',
            'payment_periodicity.required' => 'El campo periodicidad de pago es obligatorio.',
            'start_date.required' => 'El campo fecha de inicio del primer período es obligatorio.',
            'payment_relationship.required' => 'El campo relación de pago es obligatorio.',
            'payroll_concepts.required' => 'El campo conceptos es obligatorio.',
            'finance_bank_account_id.required' => 'El campo cuenta bancaria es obligatorio.',
            'finance_payment_method_id.required_unless' => 'El campo método de pago es obligatorio',
            'accounting_entry_category_id.required' => 'El campo categoría de cuenta contable es obligatorio.',
        ];
    }

    /**
     * Método que indica si el usuario tiene el permiso de actualizar fecha de periodos
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return   \Illuminate\Http\JsonResponse
     */
    public function getUserPermission()
    {
        $permission = auth()->user()->hasPermission('payroll.payment.types.edit.open');
        return response()->json(['permission' => $permission], 200);
    }

    /**
     * Indica si el perido identificado esta asociado a un tipo de pago en un registro de nómina
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @param     integer    $payment_period_id    Identificador único asociado al periodo
     * @param     integer    $payment_type_id    Identificador único asociado al tipo de pago
     *
     * @return    \Illuminate\Http\JsonResponse
     */
    public function getpayrollAssignedPeriod($payment_period_id, $payment_type_id)
    {
        $assigned = '';
        $payrollAssignedPeriods = Payroll::where('payroll_payment_period_id', $payment_period_id)->with([
            'payrollPaymentPeriod' => function ($q) use ($payment_type_id) {
                $q->where('payroll_payment_type_id', $payment_type_id);
            }
        ])->get();

        if (count($payrollAssignedPeriods) == 0) {
            $assigned = false;
        } else {
            $assigned = true;
        }

        return response()->json([
            'result' => $payrollAssignedPeriods,
            'assigned' => $assigned
        ], 200);
    }

    /**
     * Muestra un listado de los tipos de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function index()
    {
        return response()->json(
            [
                'records' => PaymentTypeResource::collection(PayrollPaymentType::orderBy('id')->get()),
            ],
            200
        );
    }

    /**
     * Valida y registra un nuevo tipo de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function store(Request $request)
    {
        if (!is_null($request->star_operation_date)) {
            $this->validateRules['start_date'] = array_merge(
                $this->validateRules
                [
                    'start_date'
                ],
                [
                    'date', 'after_or_equal:star_operation_date'
                ]
            );
            $this->messages['start_date.after_or_equal']
            = 'El campo Fecha de inicio del primer período debe ser mayor a '
            . $request->star_operation_date;
        }

        if (!auth()->user()->hasPermission('payroll.registers.moment.close')) {
            $this->validateRules['finance_payment_method_id'] = ['required'];
            $this->messages['finance_payment_method_id.required'] = 'El campo método de pago es obligatorio.';
        }
        $this->validate($request, $this->validateRules, $this->messages);

        /* Objeto asociado al modelo PayrollPaymentType */
        $payrollPaymentType = PayrollPaymentType::create([
            'code' => $request->code,
            'name' => $request->name,
            'payment_periodicity' => $request->payment_periodicity,
            'order' => !empty($request->order)
                ? $request->order
                : false,
            'receipt' => !empty($request->receipt)
                ? $request->receipt
                : false,
            'individual' => !empty($request->individual)
                ? $request->individual
                : false,
            'skip_moments' => !empty($request->skip_moments)
                ? $request->skip_moments
                : false,
            'start_date' => $request->start_date,
            'finance_bank_account_id' => $request->finance_bank_account_id ?? null,
            'finance_payment_method_id' => $request->finance_payment_method_id ?? null,
            'accounting_entry_category_id' => $request->accounting_entry_category_id ?? null,
        ]);
        /* Se agregan los conceptos asociados al tipo de pago a la tabla pivote */
        foreach ($request->payroll_concepts as $payrollConcept) {
            if ($payrollConcept['id'] != '') {
                $concept = PayrollConcept::find($payrollConcept['id']);
                $payrollPaymentType->payrollConcepts()->attach($concept);
            }
        }
        /* Se agregan los períodos de pago asociados al tipo de pago */
        foreach ($request->payroll_payment_periods as $paymentPeriod) {
            $startDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['start_date']);
            $endDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['end_date']);
            $payrollPaymentPeriod = PayrollPaymentPeriod::create([
                'number' => $paymentPeriod['number'],
                'start_date' => $startDate->format('Y-m-d'),
                'start_day' => $paymentPeriod['start_day'],
                'end_date' => $endDate->format('Y-m-d'),
                'end_day' => $paymentPeriod['end_day'],
                'payment_status' => $paymentPeriod['payment_status'],
                'payroll_payment_type_id' => $payrollPaymentType->id
            ]);
        }
        return response()->json(['record' => $payrollPaymentType, 'message' => 'Success'], 200);
    }

    /**
     * Actualiza la información de un tipo de pago
     *
     * @param     \Illuminate\Http\Request         $request    Datos de la petición
     *
     * @return    \Illuminate\Http\JsonResponse                Objeto con los registros a mostrar
     */
    public function update(Request $request, $id)
    {
        $errors = [];
        /* Objeto con la información del tipo de pago a editar asociado al modelo PayrollPaymentType */
        $payrollPaymentType = PayrollPaymentType::find($id);
        $createPeriods = false;

        if (
            !is_null($request->star_operation_date)
                && ($payrollPaymentType->start_date
                != $request->start_date
                || $payrollPaymentType->payment_periodicity
                != $request->payment_periodicity
            )
        ) {
            $createPeriods = true;
            $startDate = \DateTime::createFromFormat('Y-m-d', $request->star_operation_date);
            $this->validateRules['start_date'] = array_merge(
                $this->validateRules['start_date'],
                ['date', 'after_or_equal:star_operation_date']
            );
            $this->messages['start_date.after_or_equal'] = 'El campo Fecha de inicio del primer período debe ser igual o mayor a ' . $startDate->format('d/m/Y');
        }

        if (!auth()->user()->hasPermission('payroll.registers.moment.close')) {
            $this->validateRules['finance_payment_method_id'] = ['required'];
            $this->messages['finance_payment_method_id.required'] = 'El campo método de pago es obligatorio.';
        }
        $validateRules = $this->validateRules;
        $validateRules = array_replace(
            $validateRules,
            ['code' => ['required', 'unique:payroll_payment_types,code,' . $payrollPaymentType->id]]
        );
        $this->validate($request, $validateRules, $this->messages);

        $payrollPaymentType->code = $request->code;
        $payrollPaymentType->name = $request->name;
        $payrollPaymentType->payment_periodicity = $request->payment_periodicity;
        $payrollPaymentType->order = !empty($request->order)
            ? $request->order
            : false;
        $payrollPaymentType->receipt = !empty($request->receipt)
            ? $request->receipt
            : false;
        $payrollPaymentType->individual = !empty($request->individual)
            ? $request->individual
            : false;
        $payrollPaymentType->skip_moments = !empty($request->skip_moments)
            ? $request->skip_moments
            : false;

        $payrollPaymentType->start_date = $request->start_date;
        $payrollPaymentType->finance_bank_account_id = $request->finance_bank_account_id ?? null;
        $payrollPaymentType->finance_payment_method_id = $request->finance_payment_method_id ?? null;
        $payrollPaymentType->accounting_entry_category_id = $request->accounting_entry_category_id ?? null;
        $payrollPaymentType->save();

        /* Se eliminan los conceptos asociados al tipo de pago de la tabla pivote */
        foreach ($payrollPaymentType->payrollConcepts as $payrollConcept) {
            $concept = PayrollConcept::find($payrollConcept['id']);
            $payrollPaymentType->payrollConcepts()->detach($concept);
        }
        /* Se agregan los nuevos conceptos asociados al tipo de pago a la tabla pivote */
        foreach ($request->payroll_concepts as $payrollConcept) {
            if ($payrollConcept['id'] != '') {
                $concept = PayrollConcept::find($payrollConcept['id']);
                $payrollPaymentType->payrollConcepts()->attach($concept);
            }
        }

        if ($createPeriods == true) {
            /* Se eliminan los períodos de pago asociados al tipo de pago que no tengan relacion con nomina */
            try {
                $payrollPaymentType->payrollPaymentPeriods()->doesnthave('payroll')->Delete();
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                $errors = array_merge($errors, ["operation" => ["No se pudo completar la operación, existen registros de nómina sin cerrar para este tipo de pago."]]);
                return response()->json(['errors' => $errors], 422);
            }
            /* Se agregan los períodos de pago asociados al tipo de pago */
            foreach ($request->payroll_payment_periods as $paymentPeriod) {
                $startDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['start_date']);
                $endDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['end_date']);
                /* Si el usuario quiere generar nuevos periodos de pago */
                if (!auth()->user()->hasPermission('payroll.payment.types.edit.open')) {
                    /* Objeto asociado al modelo PayrollPaymentPeriod */
                    $payrollPaymentPeriod = PayrollPaymentPeriod::create([
                        'number' => $paymentPeriod['number'],
                        'start_date' => ($startDate == false) ? $paymentPeriod['start_date'] : $startDate->format('Y-m-d'),
                        'start_day' => $paymentPeriod['start_day'],
                        'end_date' => ($endDate == false) ? $paymentPeriod['end_date'] : $endDate->format('Y-m-d'),
                        'end_day' => $paymentPeriod['end_day'],
                        'payment_status' => $paymentPeriod['payment_status'],
                        'payroll_payment_type_id' => $payrollPaymentType->id
                    ]);
                } elseif (auth()->user()->hasPermission('payroll.payment.types.edit.open')) {
                    foreach ($request->payroll_payment_periods as $paymentPeriod) {
                        $startDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['start_date']);
                        $endDate = \DateTime::createFromFormat('d/m/Y', $paymentPeriod['end_date']);
                        $paymentPeriodUpdate = PayrollPaymentPeriod::find($paymentPeriod['id']);
                        $paymentPeriodUpdate->update([
                            'number' => $paymentPeriod['number'],
                            'start_date' => ($startDate == false) ? $paymentPeriod['start_date'] : $startDate->format('Y-m-d'),
                            'start_day' => $paymentPeriod['start_day'],
                            'end_date' => ($endDate == false) ? $paymentPeriod['end_date'] : $endDate->format('Y-m-d'),
                            'end_day' => $paymentPeriod['end_day'],
                            'payment_status' => $paymentPeriod['payment_status'],
                            'payroll_payment_type_id' => $payrollPaymentType->id
                        ]);
                    }
                }
            }
        }
        return response()->json(['message' => 'Success'], 200);
    }

    /**
     * Elimina un tipo de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer $id    Identificador único del tipo de pago a eliminar
     *
     * @return    \Illuminate\Http\JsonResponse           Objeto con los registros a mostrar
     */
    public function destroy($id)
    {
        /* Objeto con la información del tipo de pago a eliminar asociado al modelo PayrollPaymentType */
        $payrollPaymentPeriod = PayrollPaymentPeriod::with('payroll')->has('payroll')->where('payroll_payment_type_id', $id)->first();

        if ($payrollPaymentPeriod) {
            return response()->json(['message' => 'No se puede eliminar el tipo de pago debido a que tiene asociado un registro de nómina'], 403);
        }

        $payrollPaymentType = PayrollPaymentType::find($id);
        $payrollPaymentType->delete();
        return response()->json(['record' => $payrollPaymentType, 'message' => 'Success'], 200);
    }

    /**
     * Obtiene los tipos de pago registrados
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    array    Listado de los registros a mostrar
     */
    public function getPayrollPaymentTypes()
    {
        $payrollPaymentTypes = PayrollPaymentType::query()
            ->get(['id', 'code', 'name', 'receipt'])
            ->map(fn($model) => [
                'id' => $model->id,
                'text' => $model->code . ' - ' . $model->name,
                'payroll_ids' => $model->payrollPaymentPeriods
                    ->where('payment_status', 'pending')
                    ->map(fn(PayrollPaymentPeriod $period) => $period?->payroll?->id)
                    ->filter(fn($id) => !empty($id))
                    ->values()
                    ->unique()
                    ->toArray(),
                'receipt' => $model->receipt
            ])->toArray();

        return array_merge(
            [
                [
                    'id' => '',
                    'text' => 'Seleccione...',
                    'payroll_ids' => [],
                ]
            ],
            $payrollPaymentTypes
        );
    }

    /**
     * Obtiene los períodos asociados al tipo de pago registrado
     *
     * @method    getPayrollPaymentPeriods
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @param     integer    $payment_type_id    Identificador único asociado al tipo de pago
     *
     * @return    \Illuminate\Http\JsonResponse|void                     Listado de los registros a mostrar
     */
    public function getPayrollPaymentPeriods($payment_type_id = null)
    {
        $payrollPaymentType = PayrollPaymentType::find($payment_type_id);
        $listPayrollPaymentPeriods = [];
        $listPayrollConcepts = [];
        foreach ($payrollPaymentType->payrollConcepts as $payrollConcept) {
            array_push($listPayrollConcepts, [
                'id' => $payrollConcept->id,
                'text' => $payrollConcept->name
            ]);
        }

        $payrollPaymentPeriods = PayrollPaymentPeriod::query()
            ->where([
                'payroll_payment_type_id' => $payrollPaymentType->id,
                'payment_status' => 'pending',
            ])->orderBy('id')->get();

        $payrollPaymentPeriodCurrent = $payrollPaymentPeriods->first();

        $listPayrollPendingConcepts = array_values(
            $payrollPaymentType
            ->payrollTimeSheetParameters
            ->reduce(function ($carry, $parameter) use ($payrollPaymentType, $payrollPaymentPeriodCurrent) {
                $payrollTimeSheets = $parameter?->payrollTimeSheetsPending()
                    ->whereHas('documentStatus', function ($query) {
                        $query->where('action', 'CE');
                    })
                    ->where([
                        'from_date' => $payrollPaymentPeriodCurrent->start_date,
                        'to_date' => $payrollPaymentPeriodCurrent->end_date,
                    ])
                    ->get();

                foreach ($payrollTimeSheets ?? [] as $payrollTimeSheet) {
                    $timeSheetData = $payrollTimeSheet?->time_sheet_data ?? [];
                    foreach ($timeSheetData as $key => $values) {
                        list($pKey, $pNameStaff) = explode('-', $key);

                        if ('Conceptos' !== $pKey) {
                            continue;
                        }
                        foreach ($values as $value) {
                            if ($payrollPaymentType->id == $value['payroll_payment_type_id']) {
                                foreach ($value["payroll_concepts"] ?? [] as $concept) {
                                    if (in_array($concept['id'], array_column($carry, 'id'))) {
                                        foreach ($carry as &$item) {
                                            if ($item['id'] == $concept['id']) {
                                                $item['staffs'][] = $pNameStaff;
                                                break;
                                            }
                                        }
                                    } else {
                                        $carry[] = [
                                            'id' => $concept['id'],
                                            'text' => $concept['text'],
                                            'staffs' => [$pNameStaff]
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }

                return $carry;
            }, [])
        );

        if (!is_null($payrollPaymentPeriods)) {
            foreach ($payrollPaymentPeriods as $payrollPaymentPeriod) {
                array_push($listPayrollPaymentPeriods, [
                    'id' => $payrollPaymentPeriod->id,
                    'payroll_id' => $payrollPaymentPeriod?->payroll?->id,
                    'text' => date("d/m/Y", strtotime($payrollPaymentPeriod->start_date)),
                    'payment_status' => $payrollPaymentPeriod->payment_status
                ]);
            }
            return response()->json([
                'records' => $listPayrollPaymentPeriods,
                'concepts' => $listPayrollConcepts,
                'pending_concepts' => $listPayrollPendingConcepts
            ], 200);
        }
    }

    /**
     * Obtener los períodos de pago por estatus
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentPeriodsByStatus(Request $request)
    {
        $payrollPaymentPeriods = PayrollPaymentPeriod::query()->where([
            'payroll_payment_type_id' => $request->payment_type,
            'payment_status' => $request->status,
        ])->orderBy('start_date')->get();

        return response()->json([
            'records' => $payrollPaymentPeriods
        ], 200);
    }

    /**
     * Cálcula los pagos de nómina
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function calculatePayrollPayment(Request $request)
    {
        $this->data = $request->all();

        /* Se recorren los conceptos establecidos para la generación de la nómina */
        $concepts = [];
        foreach ($this->data['payroll_concepts'] as $concept) {
            $formula = null;
            $payrollConcept = PayrollConcept::find($concept['id']);
            /* Si el concepto está calculado mediante fórmula se identifican y sustituyen los parámetros, en caso contrario se optiene su valor de acuerdo al tabulador */
            if ($payrollConcept->calculation_way == 'formula') {
                $exploded = multiexplode(
                    [
                        'if',
                        '(',
                        ')',
                        '{',
                        '}',
                        ' ',
                        '==',
                        '<=',
                        '>=',
                        '<',
                        '>',
                        '!=',
                        '+',
                        '-',
                        '*',
                        '/'
                    ],
                    $payrollConcept->formula
                );
                while (count($exploded) > 0) {
                    $complete = false;
                    $current = max_length($exploded);
                    $key = array_search($current, $exploded);
                    /* Se descartan los elementos vacios y las constantes númericas */
                    if ($current == '' || is_numeric($current)) {
                        unset($exploded[$key]);

                        $complete = true;
                    } else {
                        /* Se recorre el listado de parámetros para sustituirlos por su valor real en la formula del concepto */
                        foreach ($this->data['payroll_parameters'] as $parameter) {
                            if ($parameter['code'] == $current) {
                                unset($exploded[$key]);
                                $complete = true;
                                $formula = str_replace($parameter['code'], $parameter['value'], $formula ?? $payrollConcept->formula);
                            }
                        }
                        if ($complete == false) {
                            /* Se descartan los parametro de vacaciones y los del expediente del trabajador para ser analizados mas adelante */
                            unset($exploded[$key]);
                            $complete = true;
                        }
                    }
                }
                array_push($concepts, ['field' => $payrollConcept, 'formula' => $formula ?? $payrollConcept->formula]);
            } elseif ($payrollConcept->calculation_way == 'tabulator') {
                array_push($concepts, ['field' => $payrollConcept, 'formula' => null]);
            }
        }

        /* Se evaluan los parámetros del expediente del trabajador y de la configuración de vacaciones */
        /* Se identifica la institución en la que se está operando */
        $institution = Institution::where('active', true)->where('default', true)->first();
        /** @todo Revisar (No funciona en segundo plano --- Alternativa solicitar institucion desde formulario) */
        /*$profileUser = auth()->user()->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        */
        /* Se obtienen todos los trabajadores asociados a la institución y se evalua si aplica cada uno de los conceptos */
        $payrollStaff = PayrollStaff::whereHas('payrollEmployment', function ($q) use ($institution) {
            $q->whereHas('department', function ($qq) use ($institution) {
                $qq->where('institution_id', $institution->id);
            });
        })->first();


        /* Se definen los arreglos de asignaciones y deducciones para clasificar los conceptos */
        $assignments = [];
        $deductions = [];
        foreach ($concepts as $concept) {
            $formula = null;
            if ($concept['field']->calculation_way == 'formula') {
                $exploded = multiexplode(
                    [
                        'if',
                        '(',
                        ')',
                        '{',
                        '}',
                        ' ',
                        '==',
                        '<=',
                        '>=',
                        '<',
                        '>',
                        '!=',
                        '+',
                        '-',
                        '*',
                        '/'
                    ],
                    $concept['formula']
                );
                while (count($exploded) > 0) {
                    $complete = false;
                    $current = max_length($exploded);
                    $key = array_search($current, $exploded);
                    /* Se descartan los elementos vacios y las constantes númericas */
                    if ($current == '' || is_numeric($current)) {
                        unset($exploded[$key]);
                        $complete = true;
                    } else {
                        if (isset($payrollParameters)) {
                            /* Se recorre el listado de parámetros asociados a la configuración de vacaciones
                            para sustituirlos por su valor real en la formula del concepto */
                            foreach ($payrollParameters->loadData('associatedVacation') as $parameter) {
                                if ($parameter['id'] == $current) {
                                    $records = (is_object($parameter['model']))
                                        ? $parameter['model']
                                        : $parameter['model']::where('institution_id', $institution->id)->first();
                                    unset($exploded[$key]);
                                    $complete = true;
                                    $formula = str_replace(
                                        $parameter['id'],
                                        $records[$parameter['required'][0]],
                                        $formula ?? $concept['formula']
                                    );
                                }
                            }
                            /* Se recorre el listado de parámetros asociados al expediente del trabajador
                            para sustituirlos por su valor real en la formula del concepto */
                            if ($complete == false) {
                                foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                                    if (!empty($parameter['children'])) {
                                        foreach ($parameter['children'] as $children) {
                                            if ($children['id'] == $current) {
                                                $record = ($parameter['model'] != PayrollStaff::class)
                                                    ? $parameter['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                    : $payrollStaff;
                                                unset($exploded[$key]);
                                                $complete = true;
                                                if ($children['type'] == 'number') {
                                                    /* Se calcula el número de registros existentes según sea el caso
                                                    y se sustituye por su valor real en la fórmula del concepto */
                                                    $record->loadCount($children['required'][0]);
                                                    $formula = str_replace(
                                                        $children['id'],
                                                        $record[Str::camel($children['required'][0]) . '_count'],
                                                        $formula ?? $concept['formula']
                                                    );
                                                } elseif ($children['type'] == 'date') {
                                                    /* Se calcula el número de años según la fecha de ingreso
                                                    y se sustituye por su valor real en la fórmula del concepto */
                                                    $formula = str_replace(
                                                        $children['id'],
                                                        $record[age($record[$children['required'][0]])],
                                                        $formula ?? $concept['formula']
                                                    );
                                                } else {
                                                    /* Se identifica el valor según el expediente del trabajador
                                                    y se sustituye por su valor real en la fórmula del concepto */
                                                    $formula = str_replace(
                                                        $children['id'],
                                                        $record[$children['required'][0]],
                                                        $formula ?? $concept['formula']
                                                    );
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } elseif ($payrollConcept->calculation_way == 'tabulator') {
                /* Se carga la propiedad tabulador asociada al concepto */
                $payrollConcept->load('payrollSalaryTabulator');
                $payrollSalaryTabulator = $payrollConcept->payrollSalaryTabulator;
                if ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'horizontal') {
                    /* Se carga el escalafón horizontal asociado al tabulador */
                    $payrollSalaryTabulator->load([
                        'payrollHorizontalSalaryScale' => function ($q) {
                            $q->load('payrollScales');
                        }
                    ]);

                    if (isset($payrollParameters)) {
                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                            if (!empty($parameter['children'])) {
                                foreach ($parameter['children'] as $children) {
                                    if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                        $record = ($parameter['model'] != PayrollStaff::class)
                                            ? $parameter['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                            : $payrollStaff;
                                        foreach ($payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale) {
                                            if ($children['type'] == 'number') {
                                                /* Se calcula el número de registros existentes según sea el caso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                $record->loadCount($children['required'][0]);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[Str::camel($children['required'][0]) . '_count'] >= $scl['from']) &&
                                                        ($record[Str::camel($children['required'][0]) . '_count'] < $scl['to'])
                                                    ) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                            ->where('payroll_vertical_scale_id', null)->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                } else {
                                                    if ($scl == $record[Str::camel($children['required'][0]) . '_count']) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                            ->where('payroll_vertical_scale_id', null)->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                }
                                            } elseif ($children['type'] == 'date') {
                                                /* Se calcula el número de años según la fecha de ingreso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[age($record[$children['required'][0]])] >= $scl['from']) &&
                                                        ($record[age($record[$children['required'][0]])] < $scl['to'])
                                                    ) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                            ->where('payroll_vertical_scale_id', null)->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                } else {
                                                    if ($scl == $record[age($record[$children['required'][0]])]) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                            ->where('payroll_vertical_scale_id', null)->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                }
                                            } else {
                                                /* Se identifica el valor según el expediente del trabajador
                                                y se sustituye por su valor en el tabulador */
                                                if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                        ->where('payroll_vertical_scale_id', null)->first();
                                                    $formula = json_decode($tabScale['value']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($payrollSalaryTabulator->payroll_salary_tabulator_type == 'vertical') {
                    /* Se carga el escalafón vertical asociado al tabulador */
                    $payrollSalaryTabulator->load([
                        'payrollVerticalSalaryScale' => function ($q) {
                            $q->load('payrollScales');
                        }
                    ]);

                    if (isset($payrollParameters)) {
                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                            if (!empty($parameter['children'])) {
                                foreach ($parameter['children'] as $children) {
                                    if ($children['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                        $record = ($parameter['model'] != PayrollStaff::class)
                                            ? $parameter['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                            : $payrollStaff;
                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scale) {
                                            if ($children['type'] == 'number') {
                                                /* Se calcula el número de registros existentes según sea el caso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                $record->loadCount($children['required'][0]);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[Str::camel($children['required'][0]) . '_count'] >= $scl['from']) &&
                                                        ($record[Str::camel($children['required'][0]) . '_count'] < $scl['to'])
                                                    ) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', null)
                                                            ->where('payroll_vertical_scale_id', $scale['id'])->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                } else {
                                                    if ($scl == $record[Str::camel($children['required'][0]) . '_count']) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', null)
                                                            ->where('payroll_vertical_scale_id', $scale['id'])->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                }
                                            } elseif ($children['type'] == 'date') {
                                                /* Se calcula el número de años según la fecha de ingreso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[age($record[$children['required'][0]])] >= $scl['from']) &&
                                                        ($record[age($record[$children['required'][0]])] < $scl['to'])
                                                    ) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', null)
                                                            ->where('payroll_vertical_scale_id', $scale['id'])->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                } else {
                                                    if ($scl == $record[age($record[$children['required'][0]])]) {
                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                            ->where('payroll_horizontal_scale_id', null)
                                                            ->where('payroll_vertical_scale_id', $scale['id'])->first();
                                                        $formula = json_decode($tabScale['value']);
                                                    }
                                                }
                                            } else {
                                                /* Se identifica el valor según el expediente del trabajador
                                                y se sustituye por su valor en el tabulador */
                                                if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                        ->where('payroll_horizontal_scale_id', null)
                                                        ->where('payroll_vertical_scale_id', $scale['id'])->first();
                                                    $formula = json_decode($tabScale['value']);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    /* Se carga el escalafón horizontal asociado al tabulador */
                    $payrollSalaryTabulator->load([
                        'payrollHorizontalSalaryScale' => function ($q) {
                            $q->load('payrollScales');
                        },
                        'payrollVerticalSalaryScale' => function ($q) {
                            $q->load('payrollScales');
                        }
                    ]);
                    if (isset($payrollParameters)) {
                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameter) {
                            if (!empty($parameter['children'])) {
                                foreach ($parameter['children'] as $children) {
                                    if ($children['id'] == $payrollSalaryTabulator->payrollHorizontalSalaryScale['group_by']) {
                                        $record = ($parameter['model'] != PayrollStaff::class)
                                            ? $parameter['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                            : $payrollStaff;
                                        foreach ($payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales as $scale) {
                                            if ($children['type'] == 'number') {
                                                /* Se calcula el número de registros existentes según sea el caso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                $record->loadCount($children['required'][0]);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[Str::camel($children['required'][0]) . '_count'] >= $scl['from']) &&
                                                        ($record[Str::camel($children['required'][0]) . '_count'] < $scl['to'])
                                                    ) {
                                                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                            if (!empty($parameterV['children'])) {
                                                                foreach ($parameterV['children'] as $childrenV) {
                                                                    if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                        $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                            ? $parameterV['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                                            : $payrollStaff;
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] >= $sclV['from']) &&
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::camel($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] >= $sclV['from']) &&
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[age($recordV[$childrenV['required'][0]])]) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    if ($scl == $record[Str::camel($children['required'][0]) . '_count']) {
                                                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                            if (!empty($parameterV['children'])) {
                                                                foreach ($parameterV['children'] as $childrenV) {
                                                                    if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                        $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                            ? $parameterV['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                                            : $payrollStaff;
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] >= $sclV['from']) &&
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::camel($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] >= $sclV['from']) &&
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[age($recordV[$childrenV['required'][0]])]) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } elseif ($children['type'] == 'date') {
                                                /* Se calcula el número de años según la fecha de ingreso
                                                y se sustituye por su valor en el tabulador */
                                                $scl = json_decode($scale['value']);
                                                if (isset($scl['from']) && isset($scl['to'])) {
                                                    if (
                                                        ($record[age($record[$children['required'][0]])] >= $scl['from']) &&
                                                        ($record[age($record[$children['required'][0]])] < $scl['to'])
                                                    ) {
                                                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                            if (!empty($parameterV['children'])) {
                                                                foreach ($parameterV['children'] as $childrenV) {
                                                                    if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                        $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                            ? $parameterV['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                                            : $payrollStaff;
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] >= $sclV['from']) &&
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::camel($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] >= $sclV['from']) &&
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[age($recordV[$childrenV['required'][0]])]) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                } else {
                                                    if ($scl == $record[age($record[$children['required'][0]])]) {
                                                        foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                            if (!empty($parameterV['children'])) {
                                                                foreach ($parameterV['children'] as $childrenV) {
                                                                    if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                        $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                            ? $parameterV['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                                            : $payrollStaff;
                                                                        foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                            if ($childrenV['type'] == 'number') {
                                                                                /* Se calcula el número de registros existentes según sea el caso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                $recordV->loadCount($childrenV['required'][0]);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] >= $sclV['from']) &&
                                                                                        ($recordV[Str::camel($childrenV['required'][0]) . '_count'] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[Str::camel($childrenV['required'][0]) . '_count']) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } elseif ($childrenV['type'] == 'date') {
                                                                                /* Se calcula el número de años según la fecha de ingreso
                                                                                y se sustituye por su valor en el tabulador */
                                                                                $sclV = json_decode($scaleV['value']);
                                                                                if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                    if (
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] >= $sclV['from']) &&
                                                                                        ($recordV[age($recordV[$childrenV['required'][0]])] < $sclV['to'])
                                                                                    ) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                } else {
                                                                                    if ($sclV == $recordV[age($recordV[$childrenV['required'][0]])]) {
                                                                                        $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                            ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                            ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                        $formula = json_decode($tabScale['value']);
                                                                                    }
                                                                                }
                                                                            } else {
                                                                                /* Se identifica el valor según el expediente del trabajador
                                                                                y se sustituye por su valor en el tabulador */
                                                                                if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            } else {
                                                /* Se identifica el valor según el expediente del trabajador
                                                y se sustituye por su valor en el tabulador */
                                                if (json_decode($scale['value']) == $record[$children['required'][0]]) {
                                                    foreach ($payrollParameters->loadData('associatedWorkerFile') as $parameterV) {
                                                        if (!empty($parameterV['children'])) {
                                                            foreach ($parameterV['children'] as $childrenV) {
                                                                if ($childrenV['id'] == $payrollSalaryTabulator->payrollVerticalSalaryScale['group_by']) {
                                                                    $recordV = ($parameterV['model'] != PayrollStaff::class)
                                                                        ? $parameterV['model']::where('payroll_staff_id', $payrollStaff->id)->first()
                                                                        : $payrollStaff;
                                                                    foreach ($payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales as $scaleV) {
                                                                        if ($childrenV['type'] == 'number') {
                                                                            /* Se calcula el número de registros existentes según sea el caso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            $recordV->loadCount($childrenV['required'][0]);
                                                                            if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                if (
                                                                                    ($recordV[Str::camel($childrenV['required'][0]) . '_count'] >= $sclV['from']) &&
                                                                                    ($recordV[Str::camel($childrenV['required'][0]) . '_count'] < $sclV['to'])
                                                                                ) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            } else {
                                                                                if ($sclV == $recordV[Str::camel($childrenV['required'][0]) . '_count']) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        } elseif ($childrenV['type'] == 'date') {
                                                                            /* Se calcula el número de años según la fecha de ingreso
                                                                            y se sustituye por su valor en el tabulador */
                                                                            $sclV = json_decode($scaleV['value']);
                                                                            if (isset($sclV['from']) && isset($sclV['to'])) {
                                                                                if (
                                                                                    ($recordV[age($recordV[$childrenV['required'][0]])] >= $sclV['from']) &&
                                                                                    ($recordV[age($recordV[$childrenV['required'][0]])] < $sclV['to'])
                                                                                ) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            } else {
                                                                                if ($sclV == $recordV[age($recordV[$childrenV['required'][0]])]) {
                                                                                    $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                        ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                        ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                    $formula = json_decode($tabScale['value']);
                                                                                }
                                                                            }
                                                                        } else {
                                                                            /* Se identifica el valor según el expediente del trabajador
                                                                            y se sustituye por su valor en el tabulador */
                                                                            if (json_decode($scaleV['value']) == $recordV[$childrenV['required'][0]]) {
                                                                                $tabScale = PayrollSalaryTabulatorScale::where('payroll_salary_tabulator_id', $payrollSalaryTabulator->id)
                                                                                    ->where('payroll_horizontal_scale_id', $scale['id'])
                                                                                    ->where('payroll_vertical_scale_id', $scaleV['id'])->first();
                                                                                $formula = json_decode($tabScale['value']);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            /* Se carga la propiedad payrollConceptType para determinar si clasificar el concepto como asignación o deducción */
            $concept['field']->load('payrollConceptType');
            if ($concept['field']->payrollConceptType['sign'] == '+') {
                array_push(
                    $assignments,
                    [
                        'name' => $concept['field']->name,
                        'value' => $formula ? str_eval($formula) : str_eval($concept['formula'])
                    ]
                );
            } elseif ($concept['field']->payrollConceptType['sign'] == '-') {
                array_push(
                    $deductions,
                    [
                        'name' => $concept['field']->name,
                        'value' => $formula ? str_eval($formula) : str_eval($concept['formula'])
                    ]
                );
            }
        }

        $result = 0;
        foreach ($assignments as $assignment) {
            $result += $assignment['value'];
        }
        return response()->json(['result' => $result, 'message' => 'Success'], 200);
    }
}
