<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payroll\Models\PayrollSupervisedGroup;

/**
 * @class GuardSchemaResource
 * @brief Representa un recurso para el esquema de guardia
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class GuardSchemaResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return array
     */
    public function toArray($request)
    {
        $payrollSuperviedGroup = $this->getPayrollSuperviedGroup();
        return [
            'id' => $this->resource->id,
            'institution_id' => $this->resource->institution_id,
            'institution' => $this->resource->institution?->acronym ?? '',
            'document_status' => $this->resource->document_status,
            'confirmed_periods' => $this->resource->confirmed_periods,
            'from_date' => $this->resource->from_date,
            'to_date' => $this->resource->to_date,
            'payroll_supervised_group_id' => $payrollSuperviedGroup->id,
            'payroll_supervised_group' => !empty($payrollSuperviedGroup)
                ? [
                    'code' => $payrollSuperviedGroup->code,
                    'supervisor_id' => $payrollSuperviedGroup->supervisor_id,
                    'supervisor' => !empty($payrollSuperviedGroup->supervisor)
                        ? [
                            'name' => (($payrollSuperviedGroup->supervisor->id_number ?? $payrollSuperviedGroup->supervisor->passport) .
                            ' - ' . $payrollSuperviedGroup->supervisor->first_name . ' ' . $payrollSuperviedGroup->supervisor->last_name),
                            'department' => $payrollSuperviedGroup->supervisor->payrollEmployment?->department?->name ?? '',
                        ]
                        : null,
                    'approver_id' => $payrollSuperviedGroup->approver_id,
                    'approver' => !empty($payrollSuperviedGroup->approver)
                        ? [
                            'name' => (($payrollSuperviedGroup->approver->id_number ?? $payrollSuperviedGroup->approver->passport) .
                                        ' - ' . $payrollSuperviedGroup->approver->first_name . ' ' . $payrollSuperviedGroup->approver->last_name),
                            'department' => $payrollSuperviedGroup->approver->payrollEmployment?->department?->name ?? '',
                        ]
                        : null,
                ]
                : null,
            'data_source' => $this->resource->data_source,
            'payroll_guard_scheme_periods' => !empty($this->resource->payrollGuardSchemePeriods)
                ? $this->resource->payrollGuardSchemePeriods()->orderBy('to_date')->get()->map(fn ($period) => [
                    'id' => $period->id,
                    'from_date' => $period->from_date,
                    'to_date' => $period->to_date,
                    'document_status_id' => $period->document_status_id,
                    'document_status' => $period->documentStatus,
                    'observations' => $period->observations ?? ''
                ])
                : [],
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
            'deleted_at' => $this->resource->deleted_at,
        ];
    }

    /**
     * Obtiene el grupo supervisado correspondiente al esquema de guardia
     *
     * @return PayrollSupervisedGroup|null
     */
    protected function getPayrollSuperviedGroup(): ?PayrollSupervisedGroup
    {
        $data = $this->resource->payrollSupervisedGroup;
        if ("CE" === $this->resource->document_status['action']) {
            $lastUpdate = $this->resource->document_status['last_period']->updated_at;
            $audit = $this->resource->payrollSupervisedGroup->audits()
                ->where('created_at', '>=', $lastUpdate)
                ->latest()
                ->get();
            foreach ($audit as $a) {
                $data = $data->transitionTo($a, true);
            }
        }
        return $data;
    }
}
