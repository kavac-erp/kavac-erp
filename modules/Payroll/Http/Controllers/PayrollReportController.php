<?php

namespace Modules\Payroll\Http\Controllers;

use App\Models\DocumentStatus;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Source;
use App\Models\Parameter;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use Modules\Payroll\Models\Payroll;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Payroll\Models\Institution;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Models\PayrollEmployment;
use Modules\Payroll\Models\PayrollConceptType;
use Modules\Payroll\Models\PayrollPaymentType;
use Modules\Payroll\Models\PayrollStaffPayroll;
use Modules\Payroll\Models\PayrollPaymentPeriod;
use Modules\Payroll\Models\PayrollVacationPolicy;
use Modules\Payroll\Models\PayrollVacationRequest;
use Modules\Payroll\Repositories\ReportRepository;
use Modules\Payroll\Exports\PayrollReportStaffsExport;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Modules\Payroll\Jobs\PayrollReportConceptExportJob;
use Modules\Payroll\Jobs\PayrollSendRequestedReceiptsJob;
use Modules\Payroll\Jobs\PayrollStaffPdfReportExportJob;
use Modules\Payroll\Jobs\PayrollSendStaffPdfReportEmailJob;
use Modules\Payroll\Models\PayrollExceptionType;
use Modules\Payroll\Models\PayrollSupervisedGroup;
use Modules\Payroll\Models\PayrollSupervisedGroupStaff;
use Modules\Payroll\Models\PayrollTimeSheet;
use Modules\Payroll\Models\PayrollSocioeconomic;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * @class      PayrollReportController
 * @brief      Controlador que gestiona los reportes del módulo de talento humano
 *
 * Clase que gestiona los reportes del módulo de talento humano
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
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
     *
     * @return void
     */
    public function __construct()
    {
        // Establece permisos de acceso para cada método del controlador
        // $this->middleware('permission:payroll.reports.create', ['only' => 'create']);
        $this->middleware('permission:payroll.reports.vacationrequests', ['only' => 'vacationRequests']);
        $this->middleware('permission:payroll.reports.employment.status', ['only' => 'employmentStatus']);
        $this->middleware('permission:payroll.reports.staff', ['only' => 'staffs']);
        $this->middleware('permission:payroll.reports.staffvacationenjoyment', ['only' => 'staffVacationEnjoyment']);
        $this->middleware('permission:payroll.reports.concepts', ['only' => 'concepts']);
        $this->middleware('permission:payroll.reports.relationship.concepts', ['only' => 'relationshipConcepts']);
        $this->middleware('permission:payroll.reports.payment.receipts', ['only' => 'paymentReceipt']);
    }

    /**
     * Genera el reporte solicitado
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportPdf(Request $request): JsonResponse
    {
        /* Obtiene el usuario */
        $user = User::without(['roles', 'permissions'])->where('id', auth()->user()->id)->first();

        /* Obtiene el perfil del usuario */
        $profileUser = $user->profile;

        /* Obtiene la institución por defecto */
        if (($profileUser) && isset($profileUser->institution_id)) {
            $institution = Institution::find($profileUser->institution_id);
        } else {
            $institution = Institution::where('active', true)->where('default', true)->first();
        }

        $pdf            = new ReportRepository();
        $filename       = 'payroll-report-' . Carbon::now()->format('Y-m-d') . '.pdf';
        $pdfBody        = 'payroll::pdf.payroll-staffs';
        $columns        = $this->getReportColumns($request);

        $data = [
            $institution,
            $filename,
            $pdfBody,
            $pdf,
            $columns,
            $request->toArray()
        ];

        dispatch(new PayrollStaffPdfReportExportJob($data))->chain(
            [
                new PayrollSendStaffPdfReportEmailJob($user, $filename)
            ]
        );

        return response()->json(['result' => true], 200);
    }

    /**
     * Muestra el formulario para crear un nuevo reporte
     *
     * @param \Illuminate\Http\Request $request datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $user = auth()->user();
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
        } elseif ($request->current == 'family-burden') {
            $body = 'payroll::pdf.payroll-family-burden';
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
            $records = $request->records;
            $pdf->setHeader("Reporte de trabajadores");
        } elseif ($request->current == 'concepts') {
            try {
                $validateMessage = $request->payroll_concepts == null &&
                    $request->payroll_concept_types == null &&
                    $request->payroll_payment_types == null;
                throw_if($validateMessage == true, 'Debe seleccionar al menos un parámetro para generar el reporte.');
            } catch (\Throwable $th) {
                Log::error($th->getMessage());
                return response()->json(['errors' => ['payroll_concepts' => [
                    $th->getMessage()
                ]]], 422);
            }

            $requestArray = $request->toArray();

            $conceptIds = null;
            $conceptTypeIds = null;
            $conceptPaymentTypeIds = null;
            $all = false;

            if (isset($requestArray["payroll_concepts"])) {
                $conceptIds = array_column($requestArray["payroll_concepts"], "id");
                if (in_array('todos', $conceptIds)) {
                    $all = true;
                }
            }
            if (isset($requestArray["payroll_concept_types"])) {
                $conceptTypeIds = array_column($requestArray["payroll_concept_types"], "id");
                if (in_array('todos', $conceptTypeIds)) {
                    $all = true;
                }
            }
            if (isset($requestArray["payroll_payment_types"])) {
                $conceptPaymentTypeIds = array_column($requestArray["payroll_payment_types"], "id");
                if (in_array('todos', $conceptPaymentTypeIds)) {
                    $all = true;
                }
            }

            if ($all != false) {
                $records = PayrollConcept::query()->orderBy('name', 'ASC')->get();
            } else {
                $records = PayrollConcept::when($conceptIds, function ($query) use ($conceptIds) {
                    $query->whereIn('id', $conceptIds);
                })
                    ->when($conceptTypeIds, function ($query) use ($conceptTypeIds) {
                        $query->whereIn('payroll_concept_type_id', $conceptTypeIds);
                    })
                    ->when($conceptPaymentTypeIds, function ($query) use ($conceptPaymentTypeIds) {
                        $query->whereHas('payrollPaymentTypes', function ($query) use ($conceptPaymentTypeIds) {
                            $query->whereIn('payroll_payment_type_id', $conceptPaymentTypeIds);
                        });
                    })
                    ->orderBy('name', 'ASC')
                    ->get();
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
                Log::error($th->getMessage());
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
                /* Períodos asociados al pago de nómina generado */
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
                    /* Trabajadores asociados al pago de nómina */
                    $payrollStafs = $payrollPaymentPeriod->payroll?->payrollStaffPayrolls;

                    $payrollStafs->map(function ($payroll) use ($payrollPaymentPeriod, &$items, $payrollStaffs, $payrollConceptTypeSign, $payrollConcepts, $round, $numberDecimals) {
                        /* Trabajador */
                        $payrollStaff = $payroll->payrollStaff;
                        /* Conceptos asociados al tipo de pago de nómina */
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
                    'No es posible generar el reporte, no existen cálculos asociados a los parámetros'
                ]]], 422);
            }
        } elseif ($request->current == 'family-burden') {
            if (empty($request->payroll_staffs) && empty($request->payroll_relationships)) {
                return response()->json(['errors' => [
                    'payroll_familyBurden' => [
                        'Debe seleccionar un trabajador para poder genera el reporte',
                    ],
                    'payroll_relationships' => [
                        'Debe seleccionar un parentesco para poder genera el reporte',
                    ],
                ]], 422);
            } elseif (empty($request->payroll_staffs) || count($request->payroll_staffs) == 0) {
                return response()->json(['errors' => ['payroll_familyBurden' => [
                    'Debe seleccionar un trabajador para poder genera el reporte'
                ]]], 422);
            } elseif (empty($request->payroll_relationships) || count($request->payroll_relationships) == 0) {
                return response()->json(['errors' => ['payroll_familyBurden' => [
                    'Debe seleccionar un parentesco para poder genera el reporte'
                ]]], 422);
            }

            $allStaffs = array_search('todos', array_column($request->payroll_staffs, 'id'));
            $allRelationships = array_search('todos', array_column($request->payroll_relationships, 'id'));

            if ($allStaffs !== false) {
                if ($allRelationships !== false) {
                    $records = PayrollSocioeconomic::has('payrollChildrens')->get();
                } else {
                    $realtionshipsIds = array_column($request->payroll_relationships, 'id');

                    $records = PayrollSocioeconomic::query()
                        ->whereHas('payrollChildrens', function ($query) use ($realtionshipsIds) {
                            $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                        })
                        ->get();
                }
            } else {
                if ($allRelationships !== false) {
                    $staffIds = array_column($request->payroll_staffs, 'id');

                    $records = PayrollSocioeconomic::query()
                        ->has('payrollChildrens')
                        ->whereIn('payroll_staff_id', $staffIds)
                        ->get();
                } else {
                    $staffIds = array_column($request->payroll_staffs, 'id');
                    $realtionshipsIds = array_column($request->payroll_relationships, 'id');

                    $records = PayrollSocioeconomic::query()
                        ->whereIn('payroll_staff_id', $staffIds)
                        ->whereHas('payrollChildrens', function ($query) use ($realtionshipsIds) {
                            $query->whereIn('payroll_relationships_id', $realtionshipsIds);
                        })
                        ->get();
                }
            }

            if (count($records) == 0) {
                return response()->json(['result' => 'empty'], 200);
            }

            $pdf->setHeader('Reporte de carga familiar');
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
     * Muestra información de un reporte
     *
     * @param string filename Nombre del archivo
     *
     * @return BinaryFileResponse
     */
    public function show($filename)
    {
        $file = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR . $filename
            ?? 'payroll-report-' . Carbon::now() . '.pdf';
        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Muestra el reporte firmado digitalmente
     *
     * @param string filename Nombre del archivo
     *
     * @return BinaryFileResponse
     */
    public function showPdfSign($filename)
    {
        $file = storage_path() . DIRECTORY_SEPARATOR . 'reports' . DIRECTORY_SEPARATOR
            . $filename ?? 'payroll-report-' . Carbon::now() . '-sign.pdf';
        return response()->download($file, $filename, [], 'inline');
    }

    /**
     * Reporte de personal
     *
     * @return \Illuminate\View\View
     */
    public function staffs()
    {
        return view('payroll::reports.payroll-report-staffs');
    }

    /**
     * Reporte de solicitud de vacaciones
     *
     * @return \Illuminate\View\View
     */
    public function vacationRequests()
    {
        return view('payroll::reports.payroll-report-vacation-requests');
    }

    /**
     * Reporte de bonos vacacionales
     *
     * @return \Illuminate\View\View
     */
    public function vacationBonusCalculations()
    {
        return view('payroll::reports.payroll-report-vacation-bonus-calculations');
    }

    /**
     * Reporte de avance de beneficios
     *
     * @return \Illuminate\View\View
     */
    public function benefitsAdvance()
    {
        return view('payroll::reports.benefits.payroll-report-benefit-advances');
    }

    /**
     * Reporte de empleados.
     *
     * @author Ezequiel Baptista <ebaptista@cenditel.gob.ve>
     *
     * @return \Illuminate\View\View
     */
    public function employmentStatus()
    {
        return view('payroll::reports.payroll-report-employment-status');
    }

    /**
     * Reporte de disfrute de vacaciones
     *
     * @return \Illuminate\View\View
     */
    public function staffVacationEnjoyment()
    {
        return view('payroll::reports.payroll-report-staff-vacation-enjoyment');
    }

    /**
     * Reporte de conceptos
     *
     * @return \Illuminate\View\View
     */
    public function concepts()
    {
        return view('payroll::reports.payroll-report-concepts');
    }

    /**
     * Reporte de relación de conceptos
     *
     * @return \Illuminate\View\View
     */
    public function relationshipConcepts()
    {
        return view('payroll::reports.payroll-report-relationship-concepts');
    }

    /**
     * Reporte de hojas de tiempo
     *
     * @return \Illuminate\View\View
     */
    public function timeSheets()
    {
        return view('payroll::reports.payroll-report-time-sheets');
    }

    /**
     * Método mostrar el formulario para filtrar trabajadores por nómina.
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\View\View
     */
    public function workersByPayroll()
    {
        return view('payroll::reports.payroll-report-workers-by-payroll');
    }

    /**
     * Reporte de recibos de pago
     *
     * @return \Illuminate\View\View
     */
    public function paymentReceipt(): View
    {
        return view('payroll::reports.payroll-report-payment-receipt');
    }

    /**
     * Genera el reporte de recibos de pago
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return JsonResponse
     */
    public function paymentReceiptCreate(Request $request)
    {
        $institution = get_user_institution(auth()->user());
        $currency = get_default_currency();
        $filter = [];
        $payrollPaymentTypes = [];
        $payrollStaffs = [];

        $payrollStaffPayroll = PayrollStaffPayroll::query()->where(
            function ($query) use ($request) {
                if ($request->payroll_staffs && count($request->payroll_staffs) > 0) {
                    $query->whereIn('payroll_staff_id', array_column($request->payroll_staffs, 'id'));
                }
            }
        )->whereHas(
            'payroll',
            function ($query) use ($request) {
                $query->whereHas(
                    'payrollPaymentPeriod',
                    function ($q) use ($request) {
                        $q->select('id', 'start_date', 'end_date')->where('id', $request->period)->whereHas('payrollPaymentType', function ($q) use ($request) {
                            $q->where('id', $request->payroll_payment_type);
                        });
                    }
                );
            }
        );

        if ($payrollStaffPayroll->get()->isEmpty()) {
            // Mensaje al usuario al no encontrar registros con los parámetros de búsqueda
            return response()->json([
                'result' => false, 'message' => 'No existen registros con los parámetros de búsqueda indicados'
            ], 200);
        }

        PayrollSendRequestedReceiptsJob::dispatch(
            $payrollStaffPayroll,
            $institution,
            $currency,
            auth()->user()->email
        );

        return response()->json([
            'result' => true,
            'message' => 'La generación de recibos de pago esta en proceso, le será enviado por correo electrónico en algunos minutos'
        ], 200);
    }

    /**
     * Método para filtrar y agrupar las estadísticas de trabajadores por tipo de nómina y periodo.
     *
     * @author Juan Rosas <juan.rosasr01@gmail.com>
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filterWorkersByPayroll(Request $request)
    {
        $this->validate(
            $request,
            [
                'payroll_payment_types' => ['required'],
                'start_date' => ['required', 'date'],
                'end_date' => ['required', 'date'],
            ],
            [
                'payroll_payment_types.required' => 'El campo tipos de nómina es obligatorio.',
                'start_date.required' => 'El campo desde es obligatorio.',
                'end_date.required' => 'El campo hasta es obligatorio.',
            ]
        );
        $paymentTypeGroups = [];
        $startDate = $endDate = '';

        $payrollPaymentTypeIds = array_map(function ($v) {
            return $v["id"];
        }, $request->payroll_payment_types);

        if ($request->start_date) {
            $start_date = explode('-', $request->start_date);

            $startDate = date('Y-m-d', mktime(0, 0, 0, $start_date[1], 1, $start_date[0]));
        }
        if ($request->end_date) {
            $end_date = explode('-', $request->end_date);

            $end_day = date("d", mktime(0, 0, 0, $end_date[1], 0, $end_date[0]));

            $endDate = date('Y-m-d', mktime(0, 0, 0, $end_date[1], $end_day, $end_date[0]));
        }

        if (in_array("todos", $payrollPaymentTypeIds)) {
            $payrollPaymentTypes = PayrollPaymentType::select("id", "name")->get()->toArray();
        } else {
            $payrollPaymentTypes = PayrollPaymentType::whereIn("id", $payrollPaymentTypeIds)->select("id", "name")->get()->toArray();
        }

        foreach ($payrollPaymentTypes as $paymentType) {
            $paymentTypeGroups[$paymentType["name"]] = [];

            $periods = PayrollPaymentPeriod::has("payroll.payrollStaffPayrolls")
                ->where("payroll_payment_type_id", $paymentType["id"])
                ->where("start_date", ">=", $startDate)
                ->where("end_date", "<=", $endDate)
                ->where("payment_status", "generated")
                ->get();

            foreach ($periods as $period) {
                $paymentTypeGroups[$paymentType["name"]][$period->end_date] = $period->payroll->payrollStaffPayrolls->count();
            }
        }

        return response()->json(['records' => $paymentTypeGroups], 200);
    }

    /**
     * Muestra un listado para la generación de reportes según sea el caso
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

        $user = auth()->user();
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

            /* Filtrar por personal */
            $request->payroll_staffs
                && ($payroll_staff = Arr::flatten($request->payroll_staffs))
                && !in_array('todos', $payroll_staff)
                && ($payroll_staff_id = array_column($request->payroll_staffs, 'id'))
                && ($payroll_staffs->whereIn('id', $payroll_staff_id));

            /* Filtrar por datos personales */
            if ($request->personal_data) {
                /* Genero */
                $request->payroll_genders
                    && $payroll_staffs->with('payrollGender')
                    && ($payroll_gender = Arr::flatten($request->payroll_genders))
                    && !in_array('todos', $payroll_gender)
                    && ($payroll_gender_id = array_column($request->payroll_genders, 'id'))
                    && ($payroll_staffs->whereIn('payroll_gender_id', $payroll_gender_id));

                /* Discapacidad */
                $request->payroll_disabilities && Log::info("Aqui")
                    && $payroll_staffs->with('payrollDisability')
                    && ($payroll_disability = Arr::flatten($request->payroll_disabilities))
                    && (in_array('todos', $payroll_disability)
                        ? $payroll_staffs->where('has_disability', true)
                        : (($payroll_disability_id = array_column($request->payroll_disabilities, 'id'))
                            && ($payroll_staffs->whereIn('payroll_disability_id', $payroll_disability_id))));

                /* Licencia de conducir */
                $request->payroll_license_degrees
                    && ($payroll_staffs->with('payrollLicenseDegree'))
                    && ($payroll_license_degree = Arr::flatten($request->payroll_license_degrees))
                    && (in_array('todos', $payroll_license_degree)
                        ? $payroll_staffs->where('has_driver_license', true)
                        : (($payroll_license_degree_id = array_column($request->payroll_license_degrees, 'id'))
                            && ($payroll_staffs->whereIn('payroll_license_degree_id', $payroll_license_degree_id))));

                /* Tipo de sangre */
                $request->payroll_blood_types
                    && $payroll_staffs->with('payrollBloodType')
                    && ($payroll_blood_type = Arr::flatten($request->payroll_blood_types))
                    && !in_array('todos', $payroll_blood_type)
                    && ($payroll_blood_type_id = array_column($request->payroll_blood_types, 'id'))
                    && ($payroll_staffs->whereIn('payroll_blood_type_id', $payroll_blood_type_id));

                /* Rango de edad */

                //Edad minima
                !is_null($request->min_age)
                    && ($max_date = Carbon::now()->subYears($request->min_age))
                    && $payroll_staffs->where('birthdate', '<=', $max_date);

                //Edad maxima
                !is_null($request->max_age)
                    && ($min_year = date("Y", strtotime(Carbon::now()->subYears($request->max_age))))
                    && ($min_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, $min_year)))
                    && $payroll_staffs->where('birthdate', '>=', $min_date);
            }

            if ($request->professional_data) {
                $payroll_staffs->with('payrollProfessional');

                /* Grado de instrucción */
                $request->payroll_instruction_degrees
                    && ($payroll_instruction_degree = Arr::flatten($request->payroll_instruction_degrees))
                    && !in_array('todos', $payroll_instruction_degree)
                    && ($payroll_instruction_degree_id = array_column($request->payroll_instruction_degrees, 'id'))
                    && ($payroll_staffs->whereHas(
                        'payrollProfessional',
                        function ($query) use ($payroll_instruction_degree_id) {
                            $query->whereIn('payroll_instruction_degree_id', $payroll_instruction_degree_id);
                        }
                    ));

                /* Profesiones */
                $request->payroll_professions
                    && $payroll_staffs->with('payrollProfessional.payrollStudies')
                    && ($payroll_professions = Arr::flatten($request->payroll_professions))
                    && (in_array('todos', $payroll_professions)
                        ? $payroll_staffs->whereHas('payrollProfessional', function ($query) {
                            $query->whereHas('payrollStudies', function ($q) {
                                $q->whereHas('professions');
                            });
                        })
                        : (($payroll_professions_id = array_column($request->payroll_professions, 'id'))
                            && $payroll_staffs->whereHas(
                                'payrollProfessional',
                                function ($query) use ($payroll_professions_id) {
                                    $query->whereHas('payrollStudies', function ($q) use ($payroll_professions_id) {
                                        $q->whereIn('profession_id', $payroll_professions_id);
                                    });
                                }
                            )));

                /* Es estudiante */
                $request->is_study
                    && $payroll_staffs->whereHas('payrollProfessional', function ($query) {
                        $query->where('is_student', true);
                    });
            }

            if ($request->socioeconomic_data) {
                $payroll_staffs->with('payrollSocioeconomic');

                /* Estado civil */
                $request->marital_status
                    && $payroll_staffs->with('payrollSocioeconomic.maritalStatus')
                    && ($marital_status = Arr::flatten($request->marital_status))
                    && !in_array('todos', $marital_status)
                    && ($marital_status_id = array_column($request->marital_status, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollSocioeconomic',
                        function ($query) use ($marital_status_id) {
                            $query->whereIn('marital_status_id', $marital_status_id);
                        }
                    );

                if ($request->has_childs) {
                    $payroll_staffs = $payroll_staffs->whereHas('payrollSocioeconomic', function ($query) {
                        $query->whereHas('payrollChildrens');
                    });

                    //Edad minima de los hijos
                    !is_null($request->min_childs_age)
                        && ($child_max_date = Carbon::now()->subYears($request->min_childs_age))
                        && $payroll_staffs->whereHas(
                            'payrollSocioeconomic',
                            function ($query) use ($child_max_date) {
                                $query->whereHas(
                                    'payrollChildrens',
                                    function ($qq) use ($child_max_date) {
                                        $qq->where('birthdate', '<=', $child_max_date);
                                    }
                                );
                            }
                        )->with(
                            'payrollSocioeconomic',
                            function ($query) use ($child_max_date) {
                                $query->with(
                                    'payrollChildrens',
                                    function ($qq) use ($child_max_date) {
                                        $qq->where('birthdate', '<=', $child_max_date);
                                    }
                                );
                            }
                        );

                    //Edad maxima de los hijos
                    !is_null($request->max_childs_age)
                        && ($child_min_year = date("Y", strtotime(Carbon::now()->subYears($request->max_childs_age))))
                        && ($child_min_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, $child_min_year)))
                        && $payroll_staffs->whereHas(
                            'payrollSocioeconomic',
                            function ($query) use ($child_min_date) {
                                $query->whereHas(
                                    'payrollChildrens',
                                    function ($qq) use ($child_min_date) {
                                        $qq->where('birthdate', '>=', $child_min_date);
                                    }
                                );
                            }
                        )->with(
                            'payrollSocioeconomic',
                            function ($query) use ($child_min_date) {
                                $query->with(
                                    'payrollChildrens',
                                    function ($qq) use ($child_min_date) {
                                        $qq->where('birthdate', '>=', $child_min_date);
                                    }
                                );
                            }
                        );

                    /* Niveles de escolaridad */
                    $request->payroll_schooling_levels
                        && $payroll_staffs->with('payrollSocioeconomic.payrollChildrens.payrollSchoolingLevel')
                        && ($payroll_schooling_levels = Arr::flatten($request->payroll_schooling_levels))
                        && !in_array('todos', $payroll_schooling_levels)
                        && ($payroll_schooling_levels_id = array_column($request->payroll_schooling_levels, 'id'))
                        && $payroll_staffs->whereHas(
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
                        )->with(
                            'payrollSocioeconomic.payrollChildrens',
                            function ($qq) use ($payroll_schooling_levels_id) {
                                $qq->whereIn(
                                    'payroll_schooling_level_id',
                                    $payroll_schooling_levels_id
                                );
                            }
                        );
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

                /* Trabajadores activos o inactivos */
                $is_active = isset($request->is_active) && $request->is_active;

                $is_active ? $payroll_staffs->whereHas('payrollEmployment', function ($query) use ($is_active) {
                    $query->where('active', true);
                }) : $payroll_staffs->whereHas('payrollEmployment', function ($query) use ($is_active) {
                    $query->where('active', false);
                })
                    /* Trabajadores inactivos */
                    && !$is_active
                    /* Tipo de Inactividad */
                    && $request->payroll_inactivity_types
                    && $payroll_staffs->whereHas('payrollEmployment.payrollInactivityType')
                    && ($payroll_inactivity_types = Arr::flatten($request->payroll_inactivity_types))
                    && !in_array('todos', $payroll_inactivity_types)
                    && ($payroll_inactivity_types_id = array_column($request->payroll_inactivity_types, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($payroll_inactivity_types_id) {
                            $query->whereIn(
                                'payroll_inactivity_type_id',
                                $payroll_inactivity_types_id
                            );
                        }
                    );

                /* Tipo de Cargo */
                $request->payroll_position_types
                    && $payroll_staffs->whereHas('payrollEmployment.payrollPositionType')
                    && ($payroll_position_types = Arr::flatten($request->payroll_position_types))
                    && !in_array('todos', $payroll_position_types)
                    && ($payroll_position_types_id = array_column($request->payroll_position_types, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($payroll_position_types_id) {
                            $query->whereIn('payroll_position_type_id', $payroll_position_types_id);
                        }
                    );

                /* Cargos */
                $request->payroll_positions
                    && $payroll_staffs->whereHas('payrollEmployment.payrollPositions')
                    && ($payroll_positions = Arr::flatten($request->payroll_positions))
                    && !in_array('todos', $payroll_positions)
                    && ($payroll_positions_id = array_column($request->payroll_positions_id, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment.payrollPositions',
                        function ($query) use ($payroll_positions_id) {
                            $query->whereIn(
                                'payroll_position_id',
                                $payroll_positions_id
                            );
                        }
                    );

                /* Tipos de personal */
                $request->payroll_staff_types
                    && $payroll_staffs->whereHas('payrollEmployment.payrollStaffType')
                    && ($payroll_staff_types = Arr::flatten($request->payroll_staff_types))
                    && !in_array('todos', $payroll_staff_types)
                    && ($payroll_staff_types_id = array_column($request->payroll_staff_types, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($payroll_staff_types_id) {
                            $query->whereIn('payroll_staff_type_id', $payroll_staff_types_id);
                        }
                    );

                /* Tipo de contrato */
                $request->payroll_contract_types
                    && $payroll_staffs->whereHas('payrollEmployment.payrollContractType')
                    && ($payroll_contract_types = Arr::flatten($request->payroll_contract_types))
                    && !in_array('todos', $payroll_contract_types)
                    && ($payroll_contract_types_id = array_column($request->payroll_contract_types, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($payroll_contract_types_id) {
                            $query->whereIn('payroll_contract_type_id', $payroll_contract_types_id);
                        }
                    );

                /* Departamentos */
                $request->departments
                    && $payroll_staffs->whereHas('payrollEmployment.department')
                    && ($departments = Arr::flatten($request->departments))
                    && !in_array('todos', $departments)
                    && ($departments_id = array_column($request->departments, 'id'))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment.department',
                        function ($query) use ($departments_id) {
                            $query->whereIn(
                                'department_id',
                                $departments_id
                            );
                        }
                    );

                /* Filtrar por tiempo laborado en la institución */

                /* Tiempo minimo laborado */
                !is_null($request->min_time_worked)
                    && ($max_date = Carbon::now()->subYears($request->min_time_worked))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($max_date) {
                            $query->where('start_date', '<=', $max_date);
                        }
                    );

                /* Tiempo máximo laborado */
                !is_null($request->max_time_worked)
                    && ($min_year = date("Y", strtotime(Carbon::now()->subYears($request->max_time_worked))))
                    && ($min_date = date('Y-m-d', mktime(0, 0, 0, 1, 1, $min_year)))
                    && $payroll_staffs->whereHas(
                        'payrollEmployment',
                        function ($query) use ($min_date) {
                            $query->where('start_date', '>=', $min_date);
                        }
                    );

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
                            'payrollEmployment.department',
                            function ($query) use ($departments_id) {
                                $query->whereIn(
                                    'department_id',
                                    $departments_id
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

            foreach ($payroll_staffs->items() as $payroll) {
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
                    'payroll_id_number' => $payroll['id_number'],
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
                    'payroll_position' => $payroll['payroll_employment'] && $payroll['payroll_employment']['payrollPosition']
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
        }

        return response()->json(['records' => $records], 200);
    }

    /**
     * Cálculo de la edad laboral
     *
     * @param string $birth_date Fecha de nacimiento
     *
     * @return integer
     */
    public function calculateAge($birth_date)
    {

        $age = Carbon::parse($birth_date)->age;
        return $age;
    }

    /**
     * Devuelve all si el nivel de escolaridad es la opción todos -> Todos o no
     * fue seleccionada recibe el arreglo de  $schooling_levels que corresponde
     * al nivel de escolaridad del forumario
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

    /**
     * Exporta el reporte de conceptos
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportReportConcepts(Request $request)
    {
        try {
            $validateMessage = $request->payroll_concepts == null &&
                $request->payroll_concept_types == null &&
                $request->payroll_payment_types == null;
            throw_if($validateMessage == true, 'Debe seleccionar al menos un parámetro para generar el reporte.');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['errors' => ['payroll_concepts' => [
                $th->getMessage()
            ]]], 422);
        }

        $requestArray = $request->toArray();

        dispatch(new PayrollReportConceptExportJob($requestArray));

        request()->session()->flash('message', [
            'type' => 'other', 'title' => '¡Éxito!',
            'text' => 'Su solicitud esta en proceso, esto puede tardar unos ' .
                'minutos. Se le notificara al terminar la operación',
            'icon' => 'screen-ok',
            'class' => 'growl-primary'
        ]);

        return response()->json(['result' => true], 200);
    }

    /**
     * Obtiene las columnas del reporte
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return array
     */
    public function getReportColumns(Request $request): array
    {
        return [
            'payroll_gender'                => $request->payroll_genders ? true : false,
            'payroll_disability'            => $request->payroll_disabilities ? true : false,
            'has_driver_license'            => $request->payroll_license_degrees ? true : false,
            'payroll_blood_type'            => $request->payroll_blood_types ? true : false,
            'payroll_age'                   => !is_null($request->min_age) || !is_null($request->max_age) ? true : false,
            'payroll_instruction_degree'    => $request->payroll_instruction_degrees ? true : false,
            'payroll_professions'           => $request->payroll_professions ? true : false,
            'marital_status'                => $request->marital_status ? true : false,
            'payroll_inactivity_types'      => $request->payroll_inactivity_types ? true : false,
            'payroll_position_types'        => $request->payroll_position_types ? true : false,
            'payroll_positions'             => $request->payroll_positions ? true : false,
            'payroll_staff_types'           => $request->payroll_staff_types ? true : false,
            'payroll_contract_types'        => $request->payroll_contract_types ? true : false,
            'departments'                   => $request->departments ? true : false,
            'payroll_study'                 => $request->is_study ? true : false,
            'payroll_childs'                => $request->has_childs ? true : false,
            'payroll_is_active'             => (($request->employment_data && $request->is_active) ||
                ($request->employment_data && !$request->is_active)) ? true : false,
            'time_worked'                   => !is_null($request->min_time_worked) || !is_null($request->max_time_worked),
            'time_service'                  => !is_null($request->min_time_service) || !is_null($request->max_time_service) ? true : false,
        ];
    }

    /**
     * Exporta el reporte del personal
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse|BinaryFileResponse
     */
    public function exportReportStaffs(Request $request)
    {
        $columns = $this->getReportColumns($request);

        // ini_set('max_execution_time', 300);
        /* 5min */
        try {
            $export = new PayrollReportStaffsExport($columns);
            return Excel::download($export, 'payroll_report_staffs' . '.xlsx');
        } catch (\Throwable $th) {
            request()->session()->flash('message', [
                'type' => 'other', 'title' => 'Alerta', 'icon' => 'screen-error', 'class' => 'growl-danger',
                'text' => 'No se puede generar el archivo porque se ha presentando un error en el reporte de trabajadores.',
            ]);
            return redirect()->route('payroll.reports.staffs');
        }
    }

    /**
     * Lista del personal para generar reportes
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function vueListReport(Request $request): JsonResponse
    {
        try {
            if (request('staffs')) {
                $payrollStaff = new PayrollStaff();
                $data = $payrollStaff->filterPayrollStaff()->paginate(request('limit', 10));
                $count = $data->total();

                return response()->json([
                    'data' => $data->items(),
                    'count' => $count,
                ]);
            }
            return response()->json(['data' => [], 'count' => 0,], 200);
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['data' => [], 'count' => 0, 'message' => $th->getMessage()], 200);
        }
    }

    /**
     * Genera el reporte pdf de la hoja de tiempo
     *
     * @param \Illuminate\Http\Request $request Datos de la petición
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function timeSheetsPdf(Request $request)
    {
        try {
            $validateMessage = $request->from_date == null &&
                $request->to_date == null &&
                $request->payroll_staffs == null &&
                $request->document_status == null &&
                $request->payroll_time_parameters == null &&
                $request->payroll_supervised_groups == null;
            throw_if($validateMessage == true, 'Debe seleccionar al menos un parámetro para generar el reporte.');
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            return response()->json(['errors' => ['payroll_time_sheets' => [
                $th->getMessage()
            ]]], 422);
        }

        $request = $request->toArray();

        $allPayrollSupervisedGroups = false;
        $allpayrollStaffs = false;
        $allDocumentStatus = false;
        $payrollSupervisedGroupIds = null;
        $payrollStaffsIds = null;
        $documentStatus = null;

        if (isset($request["payroll_supervised_groups"]) && count($request["payroll_supervised_groups"]) > 0) {
            $payrollSupervisedGroupIds = array_column($request["payroll_supervised_groups"], "id");
            if (in_array('todos', $request["payroll_supervised_groups"][0])) {
                $allPayrollSupervisedGroups = true;
            }
        }

        if (isset($request["payroll_staffs"]) && count($request["payroll_staffs"]) > 0) {
            $payrollStaffsIds = array_column($request["payroll_staffs"], "id");
            if (in_array('todos', $request["payroll_staffs"][0])) {
                $allpayrollStaffs = true;
            }
        }

        if (isset($request["document_status"]) && count($request["document_status"]) > 0) {
            $documentStatus = array_column($request["document_status"], "text");
            if (in_array('todos', $request["document_status"][0])) {
                $allDocumentStatus = true;
            }
        }

        $time_sheet_query = PayrollTimeSheet::query()->with(['documentStatus', 'payrollSupervisedGroup' => function ($query) {
            $query->with(['payrollSupervisedGroupStaff' => function ($query) {
                $query->with(['payrollStaff' => function ($query) {
                    $query->without([
                        'payrollGender',
                        'payrollDisability',
                        'payrollLicenseDegree',
                        'payrollBloodType',
                        'payrollProfessional',
                        'payrollSocioeconomic',
                        'payrollEmployment',
                        'payrollNationality',
                        'payrollFinancial',
                        'payrollStaffUniformSize',
                        'payrollResponsibility'
                    ]);
                }]);
            }, 'supervisor' => function ($query) {
                $query->without([
                    'payrollGender',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollBloodType',
                    'payrollProfessional',
                    'payrollSocioeconomic',
                    'payrollEmployment',
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollStaffUniformSize',
                    'payrollResponsibility'
                ]);
            }, 'approver' => function ($query) {
                $query->without([
                    'payrollGender',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollBloodType',
                    'payrollProfessional',
                    'payrollSocioeconomic',
                    'payrollEmployment',
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollStaffUniformSize',
                    'payrollResponsibility'
                ]);
            }]);
        }]);


        if ($request['from_date'] != null && $request['to_date'] != null) {
            $timeSheets = $time_sheet_query->whereBetween("from_date", [$request["from_date"], $request["to_date"]])->get();
        }

        $timeSheets = $time_sheet_query
            ->when($payrollSupervisedGroupIds, function ($query) use ($payrollSupervisedGroupIds, $allPayrollSupervisedGroups) {
                if ($allPayrollSupervisedGroups != true) {
                    $query->whereIn('payroll_supervised_group_id', $payrollSupervisedGroupIds);
                }
            })
            ->when($payrollStaffsIds, function ($query) use ($payrollStaffsIds, $allpayrollStaffs) {
                if ($allpayrollStaffs != true) {
                    $supervisedGroupStaff = PayrollSupervisedGroupStaff::query()
                        ->whereIn('payroll_staff_id', $payrollStaffsIds)
                        ->get();
                    $supervisedGroup = PayrollSupervisedGroup::query()
                        ->whereIn('id', $supervisedGroupStaff
                        ->pluck('payroll_supervised_group_id'))
                        ->get();

                    $query->whereIn('payroll_supervised_group_id', $supervisedGroup->pluck('id'));
                }
            })
            ->when($documentStatus, function ($query) use ($documentStatus, $allDocumentStatus) {
                if ($allDocumentStatus != true) {
                    $documentStatusName = DocumentStatus::query()->whereIn('name', $documentStatus)->pluck('id');

                    $query->whereIn('document_status_id', $documentStatusName);
                }
            })->get();

        if ($timeSheets->isEmpty()) {
            return response()->json(['errors' => ['payroll_relationshipConcepts' => [
                'No es posible generar el reporte, no existen registros asociados a los parámetros seleccionados.'
            ]]], 422);
        }

        $payrollExceptionType = PayrollExceptionType::query()->get()->toArray();
        $payrollTimeParameters = $request["payroll_time_parameters"] ?? null;

        foreach ($timeSheets as $sheet => $timeSheet) {
            $draggableData = [];
            $index = 1;

            if ($payrollStaffsIds != null && $payrollStaffsIds[0] != 'todos') {
                $draggableData = $timeSheet->payrollSupervisedGroup->payrollSupervisedGroupStaff
                    ->filter(function ($groupPayrollStaff) use ($payrollStaffsIds) {
                        return in_array($groupPayrollStaff->payrollStaff->id, $payrollStaffsIds);
                    })
                    ->pluck('payrollStaff')
                    ->map(function ($payrollStaff) {
                        return [
                            'Ficha' => $payrollStaff->w62orksheet_code,
                            'Nombre' => $payrollStaff->getFullNameAttribute(),
                            'staff_id' => $payrollStaff->id,
                        ];
                    })
                    ->sortBy('Nombre')
                    ->values()
                    ->map(function ($item, $index) {
                        return array_merge(['N°' => $index + 1], $item);
                    });
            } else {
                $draggableData = $timeSheet->payrollSupervisedGroup->payrollSupervisedGroupStaff
                    ->pluck('payrollStaff')
                    ->map(function ($payrollStaff) {
                        return [
                            'Ficha' => $payrollStaff->worksheet_code,
                            'Nombre' => $payrollStaff->getFullNameAttribute(),
                            'staff_id' => $payrollStaff->id,
                        ];
                    })
                    ->sortBy('Nombre')
                    ->values()
                    ->map(function ($item, $index) {
                        return array_merge(['N°' => $index + 1], $item);
                    });
            }

            $draggableData = $draggableData->toArray();
            $groups = [];
            if ($payrollTimeParameters != null) {
                $timeSheetColumns = $timeSheet->time_sheet_columns;
                $parameterColumns = [];
                $groups = [];

                foreach ($payrollExceptionType as $i => $exceptionType) {
                    foreach ($payrollTimeParameters as $parameter) {
                        foreach ($timeSheetColumns as $key => $column) {
                            if (
                                !array_key_exists($column['name'], $parameterColumns) &&
                                ($column['name'] === $parameter['text'] ||
                                $column['name'] === 'N°' ||
                                $column['name'] === 'Ficha' ||
                                $column['name'] === 'Nombre') && !array_key_exists('group', $column)
                            ) {
                                $parameterColumns[$column['name']] = $timeSheetColumns[$key];
                            } elseif (
                                !array_key_exists($column['name'], $parameterColumns) &&
                                $column['name'] === $parameter['text'] &&
                                array_key_exists('group', $parameter) &&
                                $parameter['group'] === $exceptionType['name']
                            ) {
                                $parameterColumns[$column['name']] = $timeSheetColumns[$key];
                                $groups[$column['name']] = $parameter['group'];
                            }
                        }
                    }
                    foreach ($groups as $groupValue) {
                        if ($groupValue === $exceptionType['name']) {
                            $parameterColumns['subtotal - ' . $exceptionType['name']] = [
                                'position' => $exceptionType['name'],
                                'name' => 'subtotal - ' . $exceptionType['name'],
                                'group' => $exceptionType['name'],
                                'type' => 'subtotal',
                                'isDraggable' => 'false',
                                'max' => null,
                            ];
                        }
                    }
                }

                $parameterColumns['total'] = [
                    'position' => 'total',
                    'name' => 'total',
                    'group' => 'total',
                    'type' => 'total',
                    'isDraggable' => 'false',
                    'max' => null,
                ];

                foreach ($draggableData as &$draggable) {
                    foreach ($parameterColumns as $key => $column) {
                        if (!array_key_exists($column['name'], $draggable)) {
                            $draggable[$column['name']] = [
                                'name' => '',
                                'group' => $column['group'] ?? null,
                            ];
                        }
                    }

                    foreach ($payrollExceptionType as $exceptionType) {
                        $draggable['subtotal - ' . $exceptionType['name']]['name'] = 0;
                    }

                    $filteredData = array_filter($timeSheet->time_sheet_data, function ($key) use ($parameterColumns) {
                        $lastIndex = strrpos($key, '-');
                        if ($lastIndex !== false) {
                            $beforeHyphen = substr($key, 0, $lastIndex);
                            return array_key_exists($beforeHyphen, $parameterColumns);
                        }
                        return false;
                    }, ARRAY_FILTER_USE_KEY);

                    $subtotal = 0;
                    foreach ($filteredData as $formValue => $value) {
                        $lastIndex = strrpos($formValue, '-');
                        if ($lastIndex !== false) {
                            $beforeHyphen = substr($formValue, 0, $lastIndex);
                            $afterHyphen = substr($formValue, $lastIndex + 1);
                            if (trim($afterHyphen) == strval($draggable['staff_id'])) {
                                if ($beforeHyphen != 'subtotal - ' . $draggable[trim($beforeHyphen)]['group']) {
                                    $draggable[trim($beforeHyphen)]['name'] = $value ?? '';
                                }
                                if (
                                    array_key_exists('group', $draggable[trim($beforeHyphen)])
                                    && array_key_exists('subtotal - ' . $draggable[trim($beforeHyphen)]['group'], $draggable)
                                ) {
                                    $groupName = $draggable[trim($beforeHyphen)]['group'];
                                    $subtotalName = "subtotal - $groupName";

                                    $draggable[$subtotalName]['name'] += (int) $draggable[trim($beforeHyphen)]['name'] ?? 0;
                                    $subtotal += (int) $draggable[trim($beforeHyphen)]['name'] ?? 0;
                                }
                            }
                        }
                    }
                    $draggable['total']['name'] = $subtotal;
                }
            } else {
                foreach ($draggableData as &$draggable) {
                    foreach ($timeSheet->time_sheet_columns as $column) {
                        if (!array_key_exists($column['name'], $draggable)) {
                            $draggable[$column['name']] = '';
                        }
                    }

                    foreach ($timeSheet->time_sheet_data as $formValue => $value) {
                        $lastIndex = strrpos($formValue, '-');
                        if ($lastIndex !== false) {
                            $beforeHyphen = substr($formValue, 0, $lastIndex);
                            $afterHyphen = substr($formValue, $lastIndex + 1);

                            if (trim($afterHyphen) == strval($draggable['staff_id'])) {
                                $draggable[trim($beforeHyphen)] = $value ?? '';
                            }
                        }
                    }
                }
            }

            if (!empty($parameterColumns)) {
                $timeSheets[$sheet]['parameterColumns'] = $parameterColumns;
            }

            $timeSheets[$sheet]['draggableData'] = $draggableData;
        }

        $pdf = new ReportRepository();
        $institution = Institution::find(1);
        $filename = 'payroll-report-' . Carbon::now()->format('Y-m-d') . '.pdf';

        $pdf->setConfig([
            'institution' => $institution,
            'orientation' => 'L',
            'format' => 'A2 LANDSCAPE',
            'reportDate' => '',
            'urlVerify'   => url(''),
            'filename' => $filename
        ]);
        $pdf->setHeader('Reporte de Hoja de tiempo');
        $pdf->setFooter();
        $pdf->setBody('payroll::pdf.payroll-time-sheets', true, [
            'pdf' => $pdf,
            'records' => $timeSheets,
            'payrollTimeParameters' => $payrollTimeParameters,
            'from_date' => $request["from_date"],
            'to_date' => $request["to_date"],
            'institution' => $institution,
            "report_date" => Carbon::today()->format('d-m-Y'),
        ]);

        $url = route('payroll.reports.show', [$filename]);
        return response()->json(['result' => true, 'redirect' => $url], 200);
    }
    /**
     * Método mostrar el formulario para reporte de carga familiar.
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     */
    public function familyBurden()
    {
        return view('payroll::reports.payroll-report-family-burden');
    }
}
