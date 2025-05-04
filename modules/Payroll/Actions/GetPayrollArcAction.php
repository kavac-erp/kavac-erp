<?php

declare(strict_types=1);

namespace Modules\Payroll\Actions;

use App\Models\Currency;
use App\Repositories\ReportRepository;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Payroll\Http\Resources\PayrollArcResource;
use Modules\Payroll\Models\PayrollArcResponsible;
use Modules\Payroll\Models\PayrollConcept;
use Modules\Payroll\Models\PayrollStaff;

/**
 * @class GetPayrollArcAction
 * @brief Acciones para obtener la lista de ARC
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class GetPayrollArcAction
{
    /**
     * Método constructor de la clase
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Obtiene la lista de ARC para el año fiscal seleccionado.
     *
     * @param Request $request La solicitud que contiene los datos necesarios para la consulta.
     *
     * @return JsonResponse La respuesta JSON con la lista de ARC.
     */
    public function all(Request $request): JsonResponse
    {
        $fiscalYear = $request->fiscal_year;
        $inicioAnho = Carbon::createFromDate($fiscalYear)->startOfYear()->format('Y-m-d');
        $finAnho = Carbon::createFromDate($fiscalYear)->endOfYear()->format('Y-m-d');

        $responsibleArc = $this->getPayrollArcResponsible($inicioAnho, $finAnho);

        if (is_null($responsibleArc)) {
            return response()->json(['result' => false, 'message' => 'Debe configurar previamente el responsable de ARC para el período seleccionado'], 400);
        };

        $nameResponsibleArc = $responsibleArc->payrollStaff->last_name . ' ' . $responsibleArc->payrollStaff->first_name;
        $rifResponsibleArc = $responsibleArc->payrollStaff->rif ?? '';

        $data = $this->getQueryBase(
            $fiscalYear,
            $finAnho,
            $nameResponsibleArc,
            $rifResponsibleArc
        )
            ->when(
                !in_array('todos', $request->payroll_staffs ?? []),
                fn ($query) => $query->whereIn('id', $request->payroll_staffs ?? [])
            )
            ->when(
                !empty($request->query('query')) && $request->query('query') !== "{}",
                fn ($query) => $query->search($request->query('query'))
            )
            ->when(
                !empty($request->orderBy),
                fn ($query) => $query->orderBy(
                    match ($request->orderBy) {
                        'payroll_staff.id_number' => 'payroll_staffs.id_number',
                        'payroll_staff.name' => 'payroll_staffs.last_name',
                        default => 'payroll_staffs.id'
                    },
                    ($request->ascending) ? 'asc' : 'desc'
                )
            )
            ->paginate((int) request()->limit);

        return response()->json(
            [
                'data' => !is_null($data)
                    ? PayrollArcResource::collection($data->items())
                    : [],
                'count' => $data->total()
            ],
            200
        );
    }

    /**
     * Genera un archivo PDF para el comprobante de retención de impuesto sobre la renta anual
     * o de cese de actividades para personas residentes perceptoras de sueldos, salarios y demás
     * remuneraciones similares (ARC).
     *
     * @param Request $request La solicitud que contiene los datos necesarios para generar el PDF.
     *
     * @return mixed El PDF generado.
     */
    public function export(Request $request)
    {
        $resp = $this->getFormated($request->input())->first();
        $fileName = $resp['payroll_staff']['name'] . '-ARC-' . (string) $request->fiscal_year . '.pdf';

        $pdf = new ReportRepository();
        $pdf->setConfig([
            'institution' => $resp['institution'],
            'filename' => $fileName,
            'titleIsHTML' => true,
            'reportDate' => ''
        ]);
        $pdf->setHeader(
            '<span style="text-transform: uppercase; font-size: smaller">Comprobante de retención de impuesto sobre la renta anual o de cese de actividades para personas residentes perceptoras de sueldos, salarios y demás remuneraciones similares (ARC)</span>'
        );

        return $pdf->setBody('payroll::pdf.payroll-arc', true, [
            'pdf' => $pdf,
            'record' => $resp
        ], 'D');
    }

    /**
     * Formatea los datos de la nómina para un año fiscal determinado.
     *
     * @param array $data Un arreglo que contiene el año fiscal y los datos del personal de nómina.
     * @param bool $encoded Indica si los datos del personal de nómina están codificados o no.
     *
     * @return \Illuminate\Database\Eloquent\Collection Una colección de datos de nómina formateados.
     */
    public function getFormated(array $data, bool $encoded = true)
    {
        $fiscalYear = $data['fiscal_year'];
        $inicioAnho = Carbon::createFromDate($fiscalYear)->startOfYear()->format('Y-m-d');
        $finAnho = Carbon::createFromDate($fiscalYear)->endOfYear()->format('Y-m-d');
        $payrollStaffs = $encoded
            ? (json_decode($data['payroll_staffs'] ?? '') ?? [])
            : $data['payroll_staffs'] ?? [];
        $arcConceptIds = PayrollConcept::where('arc', true)->toBase()->pluck('id')->toArray();

        $responsibleArc = $this->getPayrollArcResponsible($inicioAnho, $finAnho);
        $nameResponsibleArc = $responsibleArc->payrollStaff->last_name . ' ' . $responsibleArc->payrollStaff->first_name;
        $rifResponsibleArc = $responsibleArc->payrollStaff->rif ?? '';

        return $this->getQueryBase(
            $fiscalYear,
            $finAnho,
            $nameResponsibleArc,
            $rifResponsibleArc
        )
        ->when(
            !in_array('todos', $payrollStaffs),
            fn ($query) => $query->whereIn('id', $payrollStaffs)
        )
        ->get()->map(function ($resource) use ($arcConceptIds) {
            $dataPayrolls = $resource->payrollStaffPayrolls->map(function ($item) use ($arcConceptIds) {
                $assignmentsTotal = 0;
                $deductionsTotal = 0;

                foreach ($item->concept_type['Asignaciones'] as $concept) {
                    if (in_array($concept['id'], $arcConceptIds)) {
                        $assignmentsTotal += $concept['value'];
                    }
                }

                foreach ($item->concept_type['Deducciones'] as $concept) {
                    if (in_array($concept['id'], $arcConceptIds)) {
                        $deductionsTotal += $concept['value'];
                    }
                }

                return [
                    'payroll_id' => $item->payroll_id,
                    'payroll_payment_period' => Carbon::parse($item->payroll->payrollPaymentPeriod->end_date)->format('m'),
                    'total_value' => $assignmentsTotal - $deductionsTotal,
                ];
            });

            $groupedData = $dataPayrolls->groupBy('payroll_payment_period')->map(function ($items, $month) {
                $totalValue = $items->sum('total_value');

                return [
                    'month' => $month,
                    'total_value' => $totalValue,
                ];
            })->sortBy('month');

            $institution = $resource->payrollEmployment?->department?->institution ?? null;
            if (isset($institution) && Str::length($institution->rif) == 10) {
                $institution['rif'] = empty($institution->rif) ? '' : substr($institution->rif, 0, 1) . '-' . substr($institution->rif, 1, 8) . '-' . substr($institution->rif, 9);
            }

            return [
                'id' => $resource->id,
                'payroll_staff' => [
                    'id_number' => $resource->id_number,
                    'rif' => empty($resource->rif) ? '' : substr($resource->rif, 0, 1) . '-' . substr($resource->rif, 1, 8) . '-' . substr($resource->rif, 9),
                    'name' => $resource->last_name . ' ' . $resource->first_name,
                    'email' => $resource->payrollEmployment?->institution_email ?? $resource->email,
                    'worksheet_code' => $resource->payrollEmployment?->worksheet_code ?? '',
                ],
                'institution' => $institution,
                'retention_agent' => [
                    'address' => strip_tags($resource->payrollEmployment?->department?->institution?->legal_address ?? ''),
                    'city' => $resource->payrollEmployment?->department?->institution?->city?->name ?? '',
                    'estate' => $resource->payrollEmployment?->department?->institution?->city?->estate?->name ?? '',
                    'postal_code' => $resource->payrollEmployment?->department?->institution?->postal_code ?? '',
                    'po_box' => '',
                    'phone' => '',
                ],
                'arc_responsible' => [
                    'rif' => empty($resource->rif_arc_responsible) ? '' : substr($resource->rif_arc_responsible, 0, 1) . '-' . substr($resource->rif_arc_responsible, 1, 8) . '-' . substr($resource->rif_arc_responsible, 9),
                    'name' => $resource->name_arc_responsible,
                ],
                'symbol' => $resource->currency_symbol,
                'year' => $resource->fiscal_year,
                'start_date' => Carbon::create($resource->fiscal_year)->startOfYear()->format('d/m/Y'),
                'end_date' => Carbon::create($resource->fiscal_year)->endOfYear()->format('d/m/Y'),
                'arc' => $groupedData->sum('total_value'),
                'remuneration_paid' => $groupedData->sum('total_value'),
                'months_remunerations' => $groupedData->pluck('total_value', 'month'),
            ];
        });
    }

    /**
     * Obtiene la consulta base para obtener los datos de los empleados de nómina.
     *
     * @param int $fiscalYear Año fiscal.
     * @param string $finAnho Fecha de fin del año.
     * @param string $nameResponsibleArc Nombre del responsable del ARC.
     * @param string $rifResponsibleArc RIF del responsable del ARC.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getQueryBase(
        $fiscalYear,
        $finAnho,
        $nameResponsibleArc,
        $rifResponsibleArc
    ) {
        return PayrollStaff::query()
            ->select('*')
            ->addSelect([
                'currency_symbol' => Currency::select('symbol')->where('default', true)->limit(1),
                'fiscal_year' => DB::raw("'$fiscalYear' AS fiscal_year"),
                'rif_arc_responsible' => DB::raw("'$rifResponsibleArc' AS rif_arc_responsible"),
                'name_arc_responsible' => DB::raw("'$nameResponsibleArc' AS name_arc_responsible"),
            ])
            ->without(
                'payrollNationality',
                'payrollFinancial',
                'payrollGender',
                'payrollBloodType',
                'payrollDisability',
                'payrollLicenseDegree',
                'payrollStaffUniformSize',
                'payrollSocioeconomic',
                'payrollProfessional',
                'payrollResponsibility'
            )
            ->with(['payrollStaffPayrolls' => function ($query) {
                $query
                    ->without('payroll', 'payrollStaff');
            }, 'payrollEmployment' => function ($query) {
                $query->without(
                    'payrollPositionType',
                    'payrollPositions',
                    'payrollCoordination',
                    'payrollStaffType',
                    'payrollInactivityType',
                    'payrollContractType',
                    'payrollPreviousJob'
                );
            }])
            ->whereHas('payrollEmployment', function ($query) use ($finAnho) {
                $query->where('start_date', '<=', $finAnho);
            })
            ->when($fiscalYear, fn ($query) => $query->whereHas('payrollStaffPayrolls.payroll.payrollPaymentPeriod', function ($query) use ($fiscalYear) {
                $query->whereYear('end_date', $fiscalYear);
            }));
    }

    /**
     * Obtiene el responsable de la nómina de acuerdo a las fechas proporcionadas.
     *
     * @param string $inicioAnho Fecha de inicio del año.
     * @param string $finAnho Fecha de fin del año.
     *
     * @return ?PayrollArcResponsible El responsable de la nómina o null si no se encuentra.
     */
    public function getPayrollArcResponsible($inicioAnho, $finAnho): ?PayrollArcResponsible
    {
        return PayrollArcResponsible::query()
            ->with(['payrollStaff' => function ($query) {
                $query->without(
                    'payrollNationality',
                    'payrollFinancial',
                    'payrollGender',
                    'payrollBloodType',
                    'payrollEmployment',
                    'payrollDisability',
                    'payrollLicenseDegree',
                    'payrollStaffUniformSize',
                    'payrollSocioeconomic',
                    'payrollProfessional',
                    'payrollResponsibility'
                );
            }])
            ->where('start_date', '<=', $finAnho)
            ->where(function ($query) use ($inicioAnho) {
                $query->where('end_date', '>=', $inicioAnho)
                    ->orWhereNull('end_date');
            })->orderBy('start_date', 'desc')->first();
    }
}
