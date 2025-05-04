<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payroll\Models\PayrollSupervisedGroup;

/**
 * @class TimeSheetPendingResource
 * @brief Representa un recurso para la hoja de tiempo pendiente
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class TimeSheetPendingResource extends JsonResource
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
            'institution' => $this->resource->institution,
            'document_status_id' => $this->resource->document_status_id,
            'document_status' => $this->resource->documentStatus,
            'from_date' => $this->resource->from_date,
            'to_date' => $this->resource->to_date,
            'payroll_supervised_group_id' => $payrollSuperviedGroup->id,
            'payroll_supervised_group' => !empty($payrollSuperviedGroup)
                ? [
                    'code' => $payrollSuperviedGroup->code,
                    'supervisor_id' => $payrollSuperviedGroup->supervisor_id,
                    'supervisor' => !empty($payrollSuperviedGroup->supervisor)
                        ? [
                            'code' => $payrollSuperviedGroup->supervisor->code,
                            'first_name' => $payrollSuperviedGroup->supervisor->first_name,
                            'last_name' => $payrollSuperviedGroup->supervisor->last_name,
                            'id_number' => $payrollSuperviedGroup->supervisor->id_number,
                        ]
                        : null,
                    'approver_id' => $payrollSuperviedGroup->approver_id,
                    'approver' => !empty($payrollSuperviedGroup->approver)
                        ? [
                            'code' => $payrollSuperviedGroup->approver->code,
                            'first_name' => $payrollSuperviedGroup->approver->first_name,
                            'last_name' => $payrollSuperviedGroup->approver->last_name,
                            'id_number' => $payrollSuperviedGroup->approver->id_number,
                        ]
                        : null,
                ]
                : null,
            'payroll_time_sheet_parameter_id' => $this->resource->payroll_time_sheet_parameter_id,
            'payroll_time_sheet_parameter' => $this->resource->payrollTimeSheetParameter,
            'time_sheet_data' => $this->resource->time_sheet_data,
            'time_sheet_columns' => $this->resource->time_sheet_columns,
            'observations' => $this->resource->observations,
            'updated_at' => $this->resource->updated_at,
        ];
    }

    /**
     * Obtiene el grupo supervisado de nómina.
     *
     * @return array|object
     */
    protected function getPayrollSuperviedGroup(): ?PayrollSupervisedGroup
    {
        $data = $this->resource->payrollSupervisedGroup;
        if ("CE" == $this->resource->documentStatus->action) {
            $lastUpdate = $this->resource->updated_at;
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
