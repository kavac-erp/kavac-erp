<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\Parameter;
use Auth;
use Carbon\Carbon;
use App\Models\Source;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Payroll\Models\Payroll;
use Modules\Payroll\Models\Institution;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollConcept;
use Illuminate\Contracts\Support\Renderable;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollPaymentType;
use Modules\Payroll\Models\PayrollVacationPolicy;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Repositories\ReportRepository;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\DigitalSignature\Repositories\ReportRepositorySign;
use Modules\Payroll\Http\Resources\PayrollRelationshipConceptResource;
use Modules\Payroll\Models\PayrollConceptType;

/**
 * @class      PayrollReportController
 * @brief      Controlador que gestiona los reportes del módulo de talento humano
 *
 * Clase que gestiona los reportes del módulo de talento humano
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollReportController extends Controller
{
    use ValidatesRequests;

    /**
     * Define la configuración de la clase
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     */
    public function __construct()
    {
        /** Establece permisos de acceso para cada método del controlador */
        // $this->middleware('permission:payroll.reports.create', ['only' => 'create']);
        // $this->middleware('permission:payroll.reports.vacationRequests', ['only' => 'vacationRequests']);
        // $this->middleware('permission:payroll.reports.employment-status', ['only' => 'employmentStatus']);
        // $this->middleware('permission:payroll.reports.staff', ['only' => 'staffs']);
        // $this->middleware('permission:payroll.reports.staffVacationEnjoyment', ['only' => 'staffVacationEnjoyment']);
        // $this->middleware('permission:payroll.reports.concepts', ['only' => 'concepts']);
        // $this->middleware('permission:payroll.reports.relationship-concepts', ['only' => 'relationship-concepts']);
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create(Request $request)
    {
        $user = Auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $pdf = new ReportRepository();
        $filename = 'payroll-report-' . Carbon::now()->format('Y-m-d') . '.pdf';

        if ($request->current == 'registers') {
            $body = 'payroll::pdf.payroll-registers';
        } elseif ($request->current == 'employment-status') {
            $body = 'payroll::pdf.payroll-employment-status';
        } elseif ($request->current == 'staff-vacation-enjoyment') {
            $body = 'payroll::pdf.payroll-staff-vacation-enjoyment';
        } elseif ($request->current == 'vacation-requests') {
            $body = 'payroll::pdf.payroll-vacation-requests';
        } elseif ($request->current == 'staffs') {
            $body = 'payroll::pdf.payroll-staffs';
        } elseif ($request->current == 'concepts') {
            $body = 'payroll::pdf.payroll-concepts';
        } elseif ($request->current == 'relationship-concepts') {
            $body = 'payroll::pdf.payroll-relationship-concepts';
        } else {
            $body = '';
        }

        $pdf->setConfig(
            [
                'institution' => $institution,
                'urlVerify' => url(''),
                'orientation' => 'P',
                'filename' => $filename,
            ]
        );

        if ($request->current == 'vacation-requests') {
            $records = PayrollVacationRequest::find($request->input('id'));
            $pdf->setHeader("Reporte de solicitudes de vacaciones");
        } elseif ($request->current == 'registers') {
            $payrollRegister = Payroll::find($request->input('id'));
            $records = $payrollRegister->payrollStaffPayrolls;
            $pdf->setHeader("Reporte de registros de nómina");
        } elseif ($request->current == 'employment-status') {
            $records = PayrollStaff::with(
                [
                    'payrollProfessional.payrollStudies.professions',
                    'payrollBloodType',
                    'phones',
                    'parish'
                ]
            )->find($request->input('id'));
            $pdf->setHeader("Reporte detallado de trabajadores");
        } elseif ($request->current == 'staff-vacation-enjoyment') {
            if (!is_null($request->end_date)) {
                $records = PayrollVacationRequest::whereBetween(
                    'start_date',
                    [$request->start_date, $request->end_date]
                )
                    ->where('status', 'approved')
                    ->where('institution_id', $institution->id)->get();
            } else {
                $records = PayrollVacationRequest::whereBetween('start_date', [$request->start_date, now()])
                    ->where('status', 'approved')
                    ->where('institution_id', $institution->id)->get();
            }

            $pdf->setHeader("Reporte de Personal en Disfrute de Vacaciones");
        } elseif ($request->current == 'staffs') {
            //$records = PayrollStaff::get();
            $records = $request->records;

            $pdf->setHeader("Reporte de trabajadores");
        } elseif ($request->current == 'concepts') {
            try {
                $validateMessage = $request->payroll_concepts == null &&
                    $request->payroll_concept_types == null &&
                    $request->payroll_payment_types == null;
                throw_if($validateMessage == true, 'Debe seleccionar al menos un parámetro para generar el reporte.');
            } catch (\Throwable $th) {
                return response()->json(['errors' => ['payroll_concepts' => [
                    $th->getMessage()
                ]]], 422);
            }

            $records = PayrollConcept::with([
                'payrollPaymentTypes',
                'accountingAccount',
                'budgetAccount'
            ])->orderBy('name', 'asc')->get();

            if ($request->payroll_concept_types) {
                $concept_type = [];
                $all = false;
                foreach ($request->payroll_concept_types as $key => $value) {
                    if (in_array('todos', $value)) {
                        $all = true;
                        break;
                    } else {
                        array_push($concept_type, $value['id']);
                    }
                }
                if (!$all) {
                    $records = $records->whereIn('payroll_concept_type_id', $concept_type);
                }
            }
            if ($request->payroll_concepts) {
                $concept = [];
                $all = false;
                foreach ($request->payroll_concepts as $key => $value) {
                    if (in_array('todos', $value)) {
                        $all = true;
                        break;
                    } else {
                        array_push($concept, $value['text']);
                    }
                }
                if (!$all) {
                    $records = $records->whereIn('name', $concept);
                }
            }
            if ($request->payroll_payment_types) {
                $payment_types = [];
                $all = false;
                foreach ($request->payroll_payment_types as $key => $value) {
                    if ($value['id'] == 'todos') {
                        $all = true;
                        break;
                    } else {
                        array_push($payment_types, $value['id']);
                    }
                }
                if (!$all) {
                    $records = PayrollConcept::with(['payrollPaymentTypes'])
                        ->whereHas('payrollPaymentTypes', function ($query) use ($payment_types) {
                            $query->whereIn('payroll_payment_type_id', $payment_types);
                        })
                        ->orderBy('name', 'asc')
                        ->get();
                }
            }
            foreach ($records as $record) {
                $source = Source::with('receiver.associateable')->where('sourceable_id', $record->id)
                    ->where('sourceable_type', PayrollConcept::class)->first();
                $record->receiver = null;

                if ($source) {
                    $text = $source->receiver->description . (!empty($source->receiver->associateable?->code)
                        ? (' - ' . $source->receiver->associateable->code)
                        : '');
                    $record->receiver = [
                        'id' => $source->receiver->id,
                        'text' => $text,
                        'class' => $source->receiver->receiverable_type,
                        'group' => $source->receiver->group,
                        'description' => $source->receiver->description ?? '',
                        'accounting_account_id' => $source->receiver->associateable_id ?? null,
                        'accounting_account' => $source->receiver->associateable?->code ?? '',
                        'denomination' => $source->receiver->associateable->denomination ?? ''
                    ];
                }
            }
            $pdf->setHeader("Reporte de conceptos");
        } elseif ($request->current == 'relationship-concepts') {
            try {
                $validateMessage = $request->payroll_payment_types == null &&
                   $request->payroll_concept_types == null &&
                   $request->payroll_concepts == null &&
                   $request->payroll_staffs == null &&
                   $request->start_date == null &&
                   $request->end_date == null;
                throw_if($validateMessage == true, 'Debe seleccionar al menos un parámetro para generar el reporte.');
            } catch (\Throwable $th) {
                return response()->json(['errors' => ['payroll_relationshipConcepts' => [
                   $th->getMessage()
                ]]], 422);
            }
            $pdf->setHeader("Relación de Conceptos");
            $payrollPaymentTypeIds = array_column($request->payroll_payment_types ?? [], 'id');
            $payrollConceptTypeIds = array_column($request->payroll_concept_types ?? [], 'id');
            $payrollConceptIds = array_column($request->payroll_concepts ?? [], 'id');
            $payrollStaffIds = array_column($request->payroll_staffs ?? [], 'id');
            $startDate = $request->start_date;
            $endDate = $request->end_date;

            $payrollPaymentTypes = PayrollPaymentType::query()
                ->when((count($payrollPaymentTypeIds) > 0) && !in_array('todos', $payrollPaymentTypeIds), function ($query) use ($payrollPaymentTypeIds) {
                    $query->whereIn('id', $payrollPaymentTypeIds);
                })
                ->get();
            $payrollConceptTypes = PayrollConceptType::query()
                ->when((count($payrollConceptTypeIds) > 0) && !in_array('todos', $payrollConceptTypeIds), function ($query) use ($payrollConceptTypeIds) {
                    $query->whereIn('id', $payrollConceptTypeIds);
                })
                ->get()
                ->pluck('id')
                ->toArray();
            $payrollConceptTypeSign = PayrollConceptType::query()
                ->when((count($payrollConceptTypeIds) > 0) && !in_array('todos', $payrollConceptTypeIds), function ($query) use ($payrollConceptTypeIds) {
                    $query->whereIn('id', $payrollConceptTypeIds);
                })
                ->get()
                ->pluck('sign')
                ->toArray();
            $payrollConcepts = PayrollConcept::query()
                ->when((count($payrollConceptIds) > 0) && !in_array('todos', $payrollConceptIds), function ($query) use ($payrollConceptIds) {
                    $query->whereIn('id', $payrollConceptIds);
                })
                ->get()
                ->pluck('id')
                ->toArray();
            $payrollStaffs = PayrollStaff::query()
                ->when((count($payrollStaffIds) > 0) && !in_array('todos', $payrollStaffIds), function ($query) use ($payrollStaffIds) {
                    $query->whereIn('id', $payrollStaffIds);
                })
                ->get()
                ->pluck('id')
                ->toArray();

            $records = $payrollPaymentTypes->map(function ($paymentType) use ($payrollConceptTypes, $payrollConceptTypeSign, $payrollConcepts, $payrollStaffs, $startDate, $endDate) {
                $items = [];
                $paymentType->payrollConcepts->toBase()->map(function ($concept) use (&$items, $payrollConceptTypes, $payrollConcepts) {
                    if (
                        in_array($concept->payrollConceptType->id, $payrollConceptTypes) &&
                        in_array($concept->id, $payrollConcepts)
                    ) {
                        $items[$concept->name] = [
                            'id' => $concept->id,
                            'name' => $concept->name,
                            'payroll_concept_type_id' => $concept->payrollConceptType->id,
                            'payroll_concept_type' => $concept->payrollConceptType->name,
                            'payroll_staffs' => [],
                        ];
                    }
                });
                /** @var PayrollPaymentPeriod $payrollPaymentPeriods Períodos asociados al pago de nómina generado */
                $payrollPaymentPeriods = $paymentType
                    ->payrollPaymentPeriods()
                    ->where('payment_status', 'generated')
                    ->when(!empty($startDate), function ($query) use ($startDate) {
                        $query->whereDate('start_date', '>=', $startDate);
                    })
                    ->when(!empty($endDate), function ($query) use ($endDate) {
                        $query->whereDate('end_date', '<=', $endDate);
                    })
                    ->orderBy('id')
                    ->get();

                $round = Parameter::where('p_key', 'round')->where('required_by', 'payroll')->first();
                $numberDecimals = Parameter::where('p_key', 'number_decimals')->where('required_by', 'payroll')->first();

                $payrollPaymentPeriods->map(function ($payrollPaymentPeriod) use (&$items, $payrollStaffs, $payrollConceptTypeSign, $payrollConcepts, $round, $numberDecimals) {
                    /** @var PayrollStaff $payrollStafs Trabajadores asociados al pago de nómina */
                    $payrollStafs = $payrollPaymentPeriod->payroll?->payrollStaffPayrolls;

                    $payrollStafs->map(function ($payroll) use ($payrollPaymentPeriod, &$items, $payrollStaffs, $payrollConceptTypeSign, $payrollConcepts, $round, $numberDecimals) {
                        /** @var PayrollStaff $payroll Trabajador */
                        $payrollStaff = $payroll->payrollStaff;
                        /** @var array $paymentConcepts Conceptos asociados al tipo de pago de nómina */
                        $paymentConcepts = $payroll->concept_type;

                        if (in_array($payrollStaff->id, $payrollStaffs)) {
                            collect($paymentConcepts)->map(function ($paymentConcept) use ($payrollPaymentPeriod, $payrollStaff, &$items, $payrollConceptTypeSign, $payrollConcepts, $round, $numberDecimals) {
                                array_map(function ($concept) use ($payrollStaff, &$items, $payrollPaymentPeriod, $payrollConceptTypeSign, $payrollConcepts, $round, $numberDecimals) {
                                    if (
                                        in_array($concept['sign'], $payrollConceptTypeSign) &&
                                        in_array($concept['id'], $payrollConcepts)
                                    ) {
                                        $nameDecimalFunction = $round->p_value == 'false' ? 'currency_format' : 'round';
                                        if (isset($items[$concept['name']]) && !isset($items[$concept['name']]['payroll_staffs'][$payrollStaff->id_number])) {
                                            $items[$concept['name']]['payroll_staffs'][$payrollStaff->id_number] = [
                                                'id_number' => $payrollStaff->id_number,
                                                'name' => $payrollStaff->first_name . ' ' . $payrollStaff->last_name,
                                                'payroll_payment_periods' => [
                                                    [
                                                        'period' => Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->start_date)->format('d-m-Y') .
                                                            ' - ' .
                                                            Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->end_date)->format('d-m-Y'),
                                                        'value' => $nameDecimalFunction($concept['value'], $numberDecimals->p_value),
                                                    ],
                                                ]
                                            ];
                                        } elseif (isset($items[$concept['name']])) {
                                            $items[$concept['name']]['payroll_staffs'][$payrollStaff->id_number]['payroll_payment_periods'][] = [
                                                'period' => Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->start_date)->format('d-m-Y') .
                                                    ' - ' .
                                                    Carbon::createFromFormat('Y-m-d', $payrollPaymentPeriod->end_date)->format('d-m-Y'),
                                                'value' => $nameDecimalFunction($concept['value'], $numberDecimals->p_value),
                                            ];
                                        }
                                    };
                                }, $paymentConcept);
                            });
                        }
                    });
                });
                return [
                        'id' => $paymentType->id,
                        'name' => $paymentType->name,
                        'payroll_concepts' => array_filter($items, function ($option) {
                            return !empty($option['payroll_staffs']);
                        })
                    ];
            })->filter(function ($option) {
                return !empty($option['payroll_concepts']);
            })->values();

            if ($records->isEmpty()) {
                return response()->json(['errors' => ['payroll_relationshipConcepts' => [
                    'No es posible generar el reporte, no existen cálculos asociados a los parámetros']]], 422);
            }
        }

        $pdf->setFooter(true, strip_tags($institution->legal_address));

        $pdf->setBody(
            $body,
            true,
            [
                'pdf' => $pdf,
                'field' => $records,
            ]
        );
        $url = route('payroll.reports.show', [$filename]);
        return response()->json(['result' => true, 'redirect' => $url], 200);
    }

    /**
     * Show the specified resource.
     * @param string filename
     * @return Renderable
     */
    public function show($filename)
    {
        $file = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $filename
            ?? 'payroll-report-' . Carbon::now() . '.pdf';
        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Show the specified resource.
     * @param string filename
     * @return Renderable
     */
    public function showPdfSign($filename)
    {
        $file = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR
            . $filename ?? 'payroll-report-' . Carbon::now() . '-sign.pdf';
        return response()->download($file, $filename, [], 'inline');
    }

    public function staffs()
    {
        return view('payroll::reports.payroll-report-staffs');
    }

    public function vacationRequests()
    {
        return view('payroll::reports.payroll-report-vacation-requests');
    }

    public function vacationBonusCalculations()
    {
        return view('payroll::reports.payroll-report-vacation-bonus-calculations');
    }

    public function benefitsAdvance()
    {
        return view('payroll::reports.benefits.payroll-report-benefit-advances');
    }
    /**
     * Funcion publica para los reportes de empleados.
     *
     * @author Ezequiel Baptista <ebaptista@cenditel.gob.ve>
     */
    public function employmentStatus()
    {
        return view('payroll::reports.payroll-report-employment-status');
    }

    public function staffVacationEnjoyment()
    {
        return view('payroll::reports.payroll-report-staff-vacation-enjoyment');
    }
    public function concepts()
    {
        return view('payroll::reports.payroll-report-concepts');
    }
    public function relationshipConcepts()
    {
        return view('payroll::reports.payroll-report-relationship-concepts');
    }

    /**
     * Muestra un listado para la generación de reportes según sea el caso
     *
     * @method    vueList
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Http\JsonResponse    Objeto con los registros a mostrar
     */
    public function vueList(Request $request)
    {
        $startDate = $endDate = '';
        if ($request->start_date) {
            $start_date = explode('-', $request->start_date);
            $startDate = date('Y-m-d', mktime(0, 0, 0, $start_date[1], 1, $start_date[0]));
        }
        if ($request->end_date) {
            $end_date = explode('-', $request->end_date);
            $end_day = date("d", mktime(0, 0, 0, $end_date[1], 0, $end_date[0]));
            $endDate = date('Y-m-d', mktime(0, 0, 0, $end_date[1], $end_day, $end_date[0]));
        }

        $user = Auth()->user();
        $profileUser = $user->profile;
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }
        if ($request->current == "vacation-requests") {
            if ($user->hasRole('admin')) {
                if ($request->payroll_staff_id != '') {
                    $records = PayrollVacationRequest::where(
                        'payroll_staff_id',
                        $request->payroll_staff_id
                    )->where('institution_id', $institution->id)->get();
                } else {
                    $records = [];
                }
            } else {
                if ($request->payroll_staff_id != '') {
                    $records = PayrollVacationRequest::where(
                        'payroll_staff_id',
                        $request->payroll_staff_id
                    )->where('institution_id', $institution->id)->get();
                } else {
                    $records = [];
                }
            }
        } elseif ($request->current == "vacation-enjoyment-summaries") {
            if ($request->need_institution_start_year) {
                $institution = Institution::where('active', true)->where('default', true)->first();

                $date = new Carbon($institution->start_operations_date);

                return response()->json(['institutionStartYear' => $date->year]);
            }

            $holiday_period = $request->input('holiday_period');
            $payroll_staff_id = $request->input('payroll_staff_id');

            $records = PayrollVacationRequest::where('status', 'approved');

            if ($holiday_period) {
                $records->where('vacation_period_year', $holiday_period);
            }

            if ($payroll_staff_id) {
                $records->where('payroll_staff_id', $payroll_staff_id);
            }

            $vacationPolicy = PayrollVacationPolicy::where('active', true)->firstOrFail();

            $records->orderBy('vacation_period_year', 'DESC');

            $payrollStaff = PayrollStaff::find($request->input('payroll_staff_id'));
            $date = new Carbon($payrollStaff->payrollEmployment->start_date);
            $payrollStaffYear = $date->year;
            $currentYear = Carbon::now()->year;
            $years = [];

            while ($payrollStaffYear <= $currentYear) {
                $years[] = $payrollStaffYear++;
            }

            $records = $records->get();

            foreach ($records as $record) {
                $index = array_search($record->vacation_period_year, $years);

                $record->days_old = $index * $vacationPolicy->additional_days_per_year
                    + $vacationPolicy->vacation_days;
                $record->pending_days = $index * $vacationPolicy->additional_days_per_year
                    + $vacationPolicy->vacation_days - $record->days_requested;
            }
        } elseif ($request->current == "employment-status") {
            if ($request->payroll_staff_id != '') {
                $records = PayrollEmployment::with([
                    'payrollStaff',
                    'payrollInactivityType',
                    'payrollPositionType',
                    'payrollPositions',
                    'payrollStaffType',
                    'department',
                    'payrollContractType',
                    'payrollPreviousJob',
                ])->where('payroll_staff_id', $request->payroll_staff_id)->get();
            } else {
                $records = PayrollEmployment::with([
                    'payrollStaff',
                    'payrollInactivityType',
                    'payrollPositionType',
                    'payrollPositions',
                    'payrollStaffType',
                    'department',
                    'payrollContractType',
                    'payrollPreviousJob',
                ])->get();
            }
        } elseif ($request->current == "staff-vacation-enjoyment") {
            if (!is_null($request->end_date)) {
                $records = PayrollVacationRequest::whereBetween(
                    'start_date',
                    [
                        $request->start_date,
                        $request->end_date
                    ]
                )
                    ->where('status', 'approved')
                    ->where('institution_id', $institution->id)->get();
            } else {
                $records = PayrollVacationRequest::whereBetween('start_date', [$request->start_date, now()])
                    ->where('status', 'approved')
                    ->where('institution_id', $institution->id)->get();
            }
        } elseif ($request->current == "staffs") {
            $all = false;
            $payroll_staff_id = [];
            $payroll_gender_id = [];
            $payroll_disability_id = [];
            $has_driver_license_id = [];
            $payroll_blood_type_id = [];
            $payroll_instruction_degree_id = [];
            $payroll_professions_id = [];
            $marital_status_id = [];
            $payroll_schooling_levels_id = [];
            $payroll_inactivity_types_id = [];
            $payroll_position_types_id = [];
            $payroll_positions_id = [];
            $payroll_staff_types_id = [];
            $payroll_contract_types_id = [];
            $departments_id = [];
            $records = [];

            $payroll_staffs = PayrollStaff::with([
                'payrollEmployment',
                'payrollBloodType',
                'payrollProfessional.payrollStudies',
                'payrollSocioeconomic.maritalStatus',
                'payrollSocioeconomic.payrollChildrens.payrollSchoolingLevel',
            ]);

            if ($request->payroll_staffs) {
                foreach ($request->payroll_staffs as $payroll_staff) {
                    if (in_array('todos', $payroll_staff)) {
                        $all = true;
                        break;
                    } else {
                        array_push($payroll_staff_id, $payroll_staff['id']);
                    }
                }
                if (!$all) {
                    $payroll_staffs = $payroll_staffs->whereIn('id', $payroll_staff_id);
                }
            }

            if ($request->personal_data) {
                $all = false;
                if ($request->payroll_genders) {
                    foreach ($request->payroll_genders as $payroll_gender) {
                        if (in_array('todos', $payroll_gender)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_gender_id, $payroll_gender['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereIn('payroll_gender_id', $payroll_gender_id);
                    }
                }
                $all = false;
                if ($request->payroll_disabilities) {
                    foreach ($request->payroll_disabilities as $payroll_disability) {
                        if (in_array('todos', $payroll_disability)) {
                            $all = true;
                            $payroll_staffs = $payroll_staffs->where('has_disability', true);
                            break;
                        } else {
                            array_push($payroll_disability_id, $payroll_disability['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereIn('payroll_disability_id', $payroll_disability_id);
                    }
                }
                $all = false;
                if ($request->payroll_license_degrees) {
                    foreach ($request->payroll_license_degrees as $payroll_license_degree) {
                        if (in_array('todos', $payroll_license_degree)) {
                            $all = true;
                            $payroll_staffs = $payroll_staffs->where('has_driver_license', true);
                            break;
                        } else {
                            array_push($has_driver_license_id, $payroll_license_degree['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereIn('payroll_license_degree_id', $has_driver_license_id);
                    }
                }
                $all = false;
                if ($request->payroll_blood_types) {
                    foreach ($request->payroll_blood_types as $payroll_blood_type) {
                        if (in_array('todos', $payroll_blood_type)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_blood_type_id, $payroll_blood_type['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereIn('payroll_blood_type_id', $payroll_blood_type_id);
                    }
                }
                if (!is_null($request->min_age) && !is_null($request->max_age)) {
                    $current_date = Carbon::now();
                    $current_year = date("Y", strtotime($current_date));
                    $min_year = $current_year - $request->max_age ?? 0;
                    $min_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, $min_year));
                    $max_date = $current_date->subYears($request->min_age);

                    $payroll_staffs = $payroll_staffs->whereBetween('birthdate', [$min_date, $max_date]);
                }
            }

            if ($request->professional_data) {
                $all = false;
                if ($request->payroll_instruction_degrees) {
                    foreach ($request->payroll_instruction_degrees as $payroll_instruction_degree) {
                        if (in_array('todos', $payroll_instruction_degree)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_instruction_degree_id, $payroll_instruction_degree['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollProfessional',
                            function ($query) use ($payroll_instruction_degree_id) {
                                $query->whereIn('payroll_instruction_degree_id', $payroll_instruction_degree_id);
                            }
                        );
                    }
                }

                $all = false;
                if ($request->payroll_professions) {
                    foreach ($request->payroll_professions as $payroll_professions) {
                        if (in_array('todos', $payroll_professions)) {
                            $all = true;
                            $payroll_staffs = $payroll_staffs->whereHas('payrollProfessional', function ($query) {
                                $query->whereHas('payrollStudies', function ($q) {
                                    $q->whereHas('professions');
                                });
                            });
                            break;
                        } else {
                            array_push($payroll_professions_id, $payroll_professions['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollProfessional',
                            function ($query) use ($payroll_professions_id) {
                                $query->whereHas('payrollStudies', function ($q) use ($payroll_professions_id) {
                                    $q->whereIn('profession_id', $payroll_professions_id);
                                });
                            }
                        );
                    }
                }

                if ($request->is_study) {
                    $payroll_staffs = $payroll_staffs->whereHas('payrollProfessional', function ($query) {
                        $query->where('is_student', true);
                    });
                }
            }

            if ($request->socioeconomic_data) {
                $all = false;
                if ($request->marital_status) {
                    foreach ($request->marital_status as $marital_status) {
                        if (in_array('todos', $marital_status)) {
                            $all = true;
                            break;
                        } else {
                            array_push($marital_status_id, $marital_status['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollSocioeconomic',
                            function ($query) use ($marital_status_id) {
                                $query->whereIn('marital_status_id', $marital_status_id);
                            }
                        );
                    }
                }

                if ($request->has_childs) {
                    $payroll_staffs = $payroll_staffs->whereHas('payrollSocioeconomic', function ($query) {
                        $query->whereHas('payrollChildrens');
                    });

                    $all = false;
                    if ($request->has_childs && $request->payroll_schooling_levels) {
                        foreach ($request->payroll_schooling_levels as $payroll_schooling_levels) {
                            if (in_array('todos', $payroll_schooling_levels)) {
                                $all = true;
                                break;
                            } else {
                                array_push($payroll_schooling_levels_id, $payroll_schooling_levels['id']);
                            }
                        }
                        if (!$all) {
                            $payroll_staffs = $payroll_staffs->whereHas(
                                'payrollSocioeconomic',
                                function ($query) use ($payroll_schooling_levels_id) {
                                    $query->whereHas(
                                        'payrollChildrens',
                                        function ($qq) use ($payroll_schooling_levels_id) {
                                            $qq->whereIn(
                                                'payroll_schooling_level_id',
                                                $payroll_schooling_levels_id
                                            );
                                        }
                                    );
                                }
                            );
                        }
                    }

                    if (
                        $request->has_childs && !is_null($request->min_childs_age)
                        && !is_null($request->max_childs_age)
                    ) {
                        $now = now();
                        $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->copy()
                            ->subYears($request->max_childs_age ?? 0)->format('Y')));
                        $max = $now->copy()->subYears($request->min_childs_age ?? 0);
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollSocioeconomic',
                            function ($query) use ($min, $max) {
                                $query->whereHas(
                                    'payrollChildrens',
                                    function ($qq) use ($min, $max) {
                                        $qq->whereBetween('birthdate', [$min, $max]);
                                    }
                                );
                            }
                        );
                    }
                }
            }

            if ($request->employment_data) {
                if (!$request->is_active) {
                    $all = false;
                    $payroll_staffs = $payroll_staffs->whereHas('payrollEmployment', function ($query) {
                        $query->where('active', false);
                    });
                    if ($request->payroll_inactivity_types) {
                        foreach ($request->payroll_inactivity_types as $payroll_inactivity_types) {
                            if (in_array('todos', $payroll_inactivity_types)) {
                                $all = true;
                                break;
                            } else {
                                array_push($payroll_inactivity_types_id, $payroll_inactivity_types['id']);
                            }
                        }
                        if (!$all) {
                            $payroll_staffs = $payroll_staffs->whereHas(
                                'payrollEmployment',
                                function ($query) use ($payroll_inactivity_types_id) {
                                    $query->whereIn(
                                        'payroll_inactivity_type_id',
                                        $payroll_inactivity_types_id
                                    );
                                }
                            );
                        }
                    }
                } else {
                    $payroll_staffs = $payroll_staffs->whereHas('payrollEmployment', function ($query) {
                        $query->where('active', true);
                    });
                }

                $all = false;
                if ($request->payroll_position_types) {
                    foreach ($request->payroll_position_types as $payroll_position_types) {
                        if (in_array('todos', $payroll_position_types)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_position_types_id, $payroll_position_types['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollEmployment',
                            function ($query) use ($payroll_position_types_id) {
                                $query->whereIn('payroll_position_type_id', $payroll_position_types_id);
                            }
                        );
                    }
                }

                $all = false;
                if ($request->payroll_positions) {
                    foreach ($request->payroll_positions as $payroll_positions) {
                        if (in_array('todos', $payroll_positions)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_positions_id, $payroll_positions['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollEmployment.payrollPositions',
                            function ($query) use ($payroll_positions_id) {
                                $query->whereIn(
                                    'payroll_position_id',
                                    $payroll_positions_id
                                );
                            }
                        );
                    }
                }

                $all = false;
                if ($request->payroll_staff_types) {
                    foreach ($request->payroll_staff_types as $payroll_staff_types) {
                        if (in_array('todos', $payroll_staff_types)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_staff_types_id, $payroll_staff_types['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollEmployment',
                            function ($query) use ($payroll_staff_types_id) {
                                $query->whereIn('payroll_staff_type_id', $payroll_staff_types_id);
                            }
                        );
                    }
                }

                $all = false;
                if ($request->payroll_contract_types) {
                    foreach ($request->payroll_contract_types as $payroll_contract_types) {
                        if (in_array('todos', $payroll_contract_types)) {
                            $all = true;
                            break;
                        } else {
                            array_push($payroll_contract_types_id, $payroll_contract_types['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollEmployment',
                            function ($query) use ($payroll_contract_types_id) {
                                $query->whereIn('payroll_contract_type_id', $payroll_contract_types_id);
                            }
                        );
                    }
                }

                $all = false;
                if ($request->departments) {
                    foreach ($request->departments as $departments) {
                        if (in_array('todos', $departments)) {
                            $all = true;
                            break;
                        } else {
                            array_push($departments_id, $departments['id']);
                        }
                    }
                    if (!$all) {
                        $payroll_staffs = $payroll_staffs->whereHas(
                            'payrollEmployment.payrollPositions',
                            function ($query) use ($payroll_positions_id) {
                                $query->whereIn(
                                    'payroll_position_id',
                                    $payroll_positions_id
                                );
                            }
                        );
                    }
                }
                if (!is_null($request->min_time_worked) && !is_null($request->max_time_worked)) {
                    $current_date = Carbon::now();
                    $current_year = date("Y", strtotime($current_date));
                    $min_year = $current_year - $request->max_time_worked ?? 0;
                    $min_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, $min_year));
                    $max_date = $current_date->subYears($request->min_time_worked);
                    $payroll_staffs = $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($min_date, $max_date) {
                            $query->whereBetween('start_date', [$min_date, $max_date]);
                        }
                    );
                    $time_worked = true;
                }
                if (!is_null($request->min_time_service) && !is_null($request->max_time_service)) {
                    $payroll_time_service_id = [];

                    if (isset($time_worked)) {
                        if ($payroll_staffs) {
                            foreach ($payroll_staffs->get() as $payroll) {
                                $payroll = $payroll->toArray();
                                $years_apn = $payroll['payroll_employment']['years_apn']
                                    ? (is_numeric($payroll['payroll_employment']['years_apn'])
                                    ? $payroll['payroll_employment']['years_apn']
                                    : explode(' ', $payroll['payroll_employment']['years_apn']))
                                    : '';
                                $time_service = intval(
                                    $this->calculateAge($payroll['payroll_employment']['start_date'])
                                )
                                    + ($years_apn ? (is_numeric($years_apn)
                                    ? intval($years_apn)
                                    : intval($years_apn[1]))
                                    : 0);

                                if (
                                    $time_service >= $request->min_time_service
                                    && $time_service <= $request->max_time_service
                                ) {
                                    array_push($payroll_time_service_id, $payroll['id']);
                                }
                            }
                        }
                    } else {
                        $payroll_staffs = $payroll_staffs->whereHas('payrollEmployment');
                        if ($payroll_staffs) {
                            foreach ($payroll_staffs->get() as $payroll) {
                                $payroll = $payroll->toArray();
                                $years_apn = $payroll['payroll_employment']['years_apn']
                                    ? (is_numeric($payroll['payroll_employment']['years_apn'])
                                    ? $payroll['payroll_employment']['years_apn']
                                    : explode(' ', $payroll['payroll_employment']['years_apn']))
                                    : '';
                                $time_service = intval(
                                    $this->calculateAge($payroll['payroll_employment']['start_date'])
                                )
                                    + ($years_apn ? (is_numeric($years_apn)
                                    ? intval($years_apn) : intval($years_apn[1])) : 0);

                                if (
                                    $time_service >= $request->min_time_service
                                    && $time_service <= $request->max_time_service
                                ) {
                                    array_push($payroll_time_service_id, $payroll['id']);
                                }
                            }
                        }
                    }
                    $payroll_staffs = $payroll_staffs->whereIn('id', $payroll_time_service_id);
                }
            }
            $payroll_staffs = $payroll_staffs->get();
            $schooling_level = [];
            $allLevel = $this->searchAllToLevels($request->payroll_schooling_levels);

            foreach ($payroll_staffs as $payroll) {
                $payroll = $payroll->toArray();
                $years_apn = isset($payroll['payroll_employment'])
                    && $payroll['payroll_employment']['years_apn']
                    ? (is_numeric($payroll['payroll_employment']['years_apn'])
                    ? $payroll['payroll_employment']['years_apn']
                    : explode(' ', $payroll['payroll_employment']['years_apn']))
                    : '';

                $min = '';
                $max = '';

                //Filtro con opciones de hijo, minimo y maximo
                if ($request->has_childs && !is_null($request->min_childs_age) && !is_null($request->max_childs_age)) {
                    $payrollChildrens = [];
                    $now = now();
                    $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->copy()
                        ->subYears($request->min_childs_age ?? 0)->format('Y')));
                    $max = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->copy()
                        ->subYears($request->max_childs_age ?? 0)->format('Y')));
                    foreach ($payroll['payroll_socioeconomic']['payroll_childrens'] as $payroll_childrens) {
                        if ($payroll_childrens['birthdate'] > $max && $payroll_childrens['birthdate'] < $min) {
                            //Filtro con la opción el nivel de escolaridad diferente a la opción "Todos"
                            if (
                                empty($allLevel) && $request->payroll_schooling_levels
                                && $payroll_childrens['payroll_schooling_level']
                            ) {
                                $schooling_level = $payroll_childrens['payroll_schooling_level'];
                                foreach ($request->payroll_schooling_levels as $payroll_schooling_level) {
                                    if ($schooling_level['id'] == $payroll_schooling_level['id']) {
                                        array_push($payrollChildrens, $payroll_childrens);
                                    }
                                }
                            } elseif (!empty($allLevel) && !$request->payroll_schooling_levels) {
                                //Filtro sin la opción del nivel de escolaridad o con la opción "Todos"
                                array_push($payrollChildrens, $payroll_childrens);
                            }
                        }
                    }
                } elseif (
                    $request->has_childs && !is_null($request->min_childs_age)
                    && is_null($request->max_childs_age)
                ) {
                    //Filtro con opciones de hijo solo con minimo
                    $payrollChildrens = [];
                    $now = now();
                    $min = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->copy()
                        ->subYears($request->min_childs_age ?? 0)->format('Y')));

                    foreach ($payroll['payroll_socioeconomic']['payroll_childrens'] as $payroll_childrens) {
                        if ($payroll_childrens['birthdate'] < $min) {
                            //Filtro con la opción el nivel de escolaridad diferente a la opción "Todos"
                            if (
                                empty($allLevel) && $request->payroll_schooling_levels
                                && $payroll_childrens['payroll_schooling_level']
                            ) {
                                $schooling_level = $payroll_childrens['payroll_schooling_level'];
                                foreach ($request->payroll_schooling_levels as $payroll_schooling_level) {
                                    if ($schooling_level['id'] == $payroll_schooling_level['id']) {
                                        array_push($payrollChildrens, $payroll_childrens);
                                    }
                                }
                            } elseif (!empty($allLevel) && !$request->payroll_schooling_levels) {
                                //Filtro sin la opción del nivel de escolaridad o con la opción "Todos"
                                array_push($payrollChildrens, $payroll_childrens);
                            }
                        }
                    }
                } elseif (
                    $request->has_childs && is_null($request->min_childs_age)
                    && !is_null($request->max_childs_age)
                ) {
                    //Filtro con opciones de hijo solo con maximo
                    $payrollChildrens = [];
                    $now = now();
                    $max = date('Y-m-d', mktime(0, 0, 0, 1, 1, $now->copy()
                        ->subYears($request->max_childs_age ?? 0)->format('Y')));

                    foreach ($payroll['payroll_socioeconomic']['payroll_childrens'] as $payroll_childrens) {
                        if ($payroll_childrens['birthdate'] > $max) {
                            //Filtro con la opción el nivel de escolaridad diferente a la opción "Todos"
                            if (
                                empty($allLevel) && $request->payroll_schooling_levels
                                && $payroll_childrens['payroll_schooling_level']
                            ) {
                                $schooling_level = $payroll_childrens['payroll_schooling_level'];
                                foreach ($request->payroll_schooling_levels as $payroll_schooling_level) {
                                    if ($schooling_level['id'] == $payroll_schooling_level['id']) {
                                        array_push($payrollChildrens, $payroll_childrens);
                                    }
                                }
                            } elseif (!empty($allLevel) && !$request->payroll_schooling_levels) {
                                //Filtro sin la opción del nivel de escolaridad o con la opción "Todos"
                                array_push($payrollChildrens, $payroll_childrens);
                            }
                        }
                    }
                } elseif (
                    $request->has_childs && is_null($request->min_childs_age)
                    && is_null($request->max_childs_age)
                ) {
                    //Filtro con opciones de hijo sin rango de edad
                    $payrollChildrens = [];
                    foreach ($payroll['payroll_socioeconomic']['payroll_childrens'] as $payroll_childrens) {
                        //Filtro con la opción el nivel de escolaridad diferente a la opción "Todos"
                        if (
                            empty($allLevel) && $request->payroll_schooling_levels
                            && $payroll_childrens['payroll_schooling_level']
                        ) {
                            $schooling_level = $payroll_childrens['payroll_schooling_level'];
                            foreach ($request->payroll_schooling_levels as $payroll_schooling_level) {
                                if ($schooling_level['id'] == $payroll_schooling_level['id']) {
                                    array_push($payrollChildrens, $payroll_childrens);
                                }
                            }
                        } elseif (!empty($allLevel) && is_null($request->payroll_schooling_levels)) {
                            //Filtro sin la opción del nivel de escolaridad o con la opción "Todos"
                            array_push($payrollChildrens, $payroll_childrens);
                        }
                    }
                }

                array_push($records, [
                    'payroll_staff' => $payroll['first_name'] . ' ' . $payroll['last_name'],
                    'payroll_gender' => $payroll['payroll_gender']['name'] ?? 'N/A',
                    'payroll_disability' => $payroll['payroll_disability_id']
                        ? $payroll['payroll_disability']['name']
                        : 'N/A',
                    'payroll_license' => $payroll['payroll_license_degree_id']
                        ? $payroll['payroll_license_degree']['name']
                        : 'N/A',
                    'payroll_blood_type' => $payroll['payroll_blood_type_id']
                        ? $payroll['payroll_blood_type']['name']
                        : 'N/A',
                    'payroll_age' => $payroll['birthdate']
                        ? $this->calculateAge($payroll['birthdate']) . ' años'
                        : 'N/A',
                    'payroll_instruction_degree' => $payroll['payroll_professional']
                        ? ($payroll['payroll_professional']['payroll_instruction_degree_id']
                            ? $payroll['payroll_professional']['payroll_instruction_degree']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_profession' => $payroll['payroll_professional']
                        ? ($payroll['payroll_professional']['payroll_studies']
                            ? $payroll['payroll_professional']['payroll_studies'][0]['professions']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_study' => $payroll['payroll_professional']
                        ? ($payroll['payroll_professional']['is_student']
                            ? 'Si'
                            : 'No')
                        : 'N/A',
                    'payroll_marital_status' => $payroll['payroll_socioeconomic']
                        ? ($payroll['payroll_socioeconomic']['marital_status_id']
                            ? $payroll['payroll_socioeconomic']['marital_status']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_childs' => $payroll['payroll_socioeconomic']
                        ? ($payroll['payroll_socioeconomic']['payroll_childrens']
                        ? 'Si' : 'No') : 'N/A',
                    'payroll_childs_arrays' => $payrollChildrens ?? 'N/A',
                    'schooling_levels' => $request->schooling_levels,
                    'payroll_is_active' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['active']
                            ? 'Si'
                            : 'No')
                        : 'N/A',
                    'payroll_inactivity_type' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['payroll_inactivity_type_id']
                            ? $payroll['payroll_employment']['payroll_inactivity_type']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_position_type' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['payroll_position_type_id']
                            ? $payroll['payroll_employment']['payroll_position_type']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_position' => $payroll['payroll_employment']
                        ? $payroll['payroll_employment']['payrollPosition']['name']
                        : 'N/A',
                    'payroll_staff_type' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['payroll_staff_type_id']
                            ? $payroll['payroll_employment']['payroll_staff_type']['name']
                            : 'N/A')
                        : 'N/A',
                    'payroll_contract_type' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['payroll_contract_type_id']
                            ? $payroll['payroll_employment']['payroll_contract_type']['name']
                            : 'N/A')
                        : 'N/A',
                    'department' => $payroll['payroll_employment']
                        ? ($payroll['payroll_employment']['department_id']
                            ? $payroll['payroll_employment']['department']['name']
                            : 'N/A')
                        : 'N/A',
                    'time_worked' => $payroll['payroll_employment']
                        ? $this->calculateAge($payroll['payroll_employment']['start_date']) . ' años'
                        : 'N/A',
                    'start_date' => $payroll['payroll_employment']
                        ? $payroll['payroll_employment']['start_date']
                        : 'N/A',
                    'time_service' => $payroll['payroll_employment']
                        ? (intval($this->calculateAge($payroll['payroll_employment']['start_date']))
                        + ($years_apn ? (is_numeric($years_apn) ? intval($years_apn) : intval($years_apn[1])) : 0))
                        . ' años' : 'N/A',
                ]);
            }

            array_push($records, [
                'conditions' => [
                    'payroll_gender' => $request->payroll_genders ? true : false,
                    'payroll_disability' => $request->payroll_disabilities ? true : false,
                    'has_driver_license' => $request->payroll_license_degrees ? true : false,
                    'payroll_blood_type' => $request->payroll_blood_types ? true : false,
                    'payroll_age' => !is_null($request->min_age) ? true : false,
                    'payroll_instruction_degree' => $request->payroll_instruction_degrees ? true : false,
                    'payroll_professions' => $request->payroll_professions ? true : false,
                    'marital_status' => $request->marital_status ? true : false,
                    'payroll_inactivity_types' => $request->payroll_inactivity_types ? true : false,
                    'payroll_position_types' => $request->payroll_position_types ? true : false,
                    'payroll_positions' => $request->payroll_positions ? true : false,
                    'payroll_staff_types' => $request->payroll_staff_types ? true : false,
                    'payroll_contract_types' => $request->payroll_contract_types ? true : false,
                    'departments' => $request->departments ? true : false,
                    'payroll_study' => $request->is_study ? true : false,
                    'payroll_childs' => $request->has_childs ? true : false,
                    'payroll_is_active' => (($request->employment_data && $request->is_active) ||
                        ($request->employment_data && !$request->is_active)) ? true : false,
                    'time_worked' => '',
                    'time_service' => !is_null($request->min_time_service) ? true : false,

                ],
            ]);

            /**/
        }

        return response()->json(['records' => $records], 200);
    }

    public function calculateAge($birth_date)
    {

        $age = Carbon::parse($birth_date)->age;
        return $age;
        // $age = Carbon::now();

        // return $age->diffForHumans($birth_date, $age);
    }

    /**
     * Devuelve all si el nivel de escolaridad es la opción todos -> Todos o no
     * fue seleccionada recibe el arreglo de  $schooling_levels que corresponde
     * al nivel de escolaridad del forumario
     *
     * @method    searchAllToLevels
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    string 'all' o ''
     */
    public function searchAllToLevels($schooling_levels)
    {
        $level = '';
        if ($schooling_levels) {
            foreach ($schooling_levels as $schooling_level) {
                if ($schooling_level['id'] == 'todos' || $schooling_level['text'] == 'Todos') {
                    $level = 'all';
                }
            }
        } else {
            $level = 'all';
        }
        return $level;
    }
}
