<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payroll\Models\PayrollConcept;

/**
 * @class PayrollArcResource
 * @brief Representa un recurso para la planilla ARC
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollArcResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request datos de la petición
     *
     * @return array
     */
    public function toArray($request)
    {
        $arcConceptIds = PayrollConcept::where('arc', true)->toBase()->pluck('id')->toArray();
        $dataPayrolls = $this->resource->payrollStaffPayrolls->map(function ($item) use ($arcConceptIds) {
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
        $institution = $this->resource->payrollEmployment?->department?->institution;

        return [
            'id' => $this->resource->id,
            'payroll_staff' => [
                'id_number' => $this->resource->id_number,
                'rif' => empty($this->resource->rif) ? '' : substr($this->resource->rif, 0, 1) . '-' . substr($this->resource->rif, 1, 8) . '-' . substr($this->resource->rif, 9),
                'name' => $this->resource->last_name . ' ' . $this->resource->first_name,
                'email' => $this->resource->payrollEmployment?->institution_email ?? $this->resource->email,
                'worksheet_code' => $this->resource->payrollEmployment?->worksheet_code ?? '',
            ],
            'institution' => [
                'rif' => empty($institution?->rif) ? '' : substr($institution->rif, 0, 1) . '-' . substr($institution->rif, 1, 8) . '-' . substr($institution->rif, 9),
                'name'=> $this->resource->payrollEmployment?->department?->institution?->name ?? '',
            ],
            'retention_agent' => [
                'address' => strip_tags($this->resource->payrollEmployment?->department?->institution?->legal_address ?? ''),
                'city' => $this->resource->payrollEmployment?->department?->institution?->city?->name ?? '',
                'estate' => $this->resource->payrollEmployment?->department?->institution?->city?->estate?->name ?? '',
                'postal_code' => $this->resource->payrollEmployment?->department?->institution?->postal_code ?? '',
                'po_box' => '',
                'phone' => '',
            ],
            'arc_responsible' => [
                'rif' => empty($this->resource->rif_arc_responsible) ? '' : substr($this->resource->rif_arc_responsible, 0, 1) . '-' . substr($this->resource->rif_arc_responsible, 1, 8) . '-' . substr($this->resource->rif_arc_responsible, 9),
                'name' => $this->resource->name_arc_responsible,
            ],
            'symbol' => $this->resource->currency_symbol,
            'year' => $this->resource->fiscal_year,
            'start_date' => Carbon::create($this->resource->fiscal_year)->startOfYear()->format('d/m/Y'),
            'end_date' => Carbon::create($this->resource->fiscal_year)->endOfYear()->format('d/m/Y'),
            'arc' => $groupedData->sum('total_value'),
            'remuneration_paid' => $groupedData->sum('total_value'),
            'months_remunerations' => $groupedData->pluck('total_value', 'month'),
        ];
    }
}
