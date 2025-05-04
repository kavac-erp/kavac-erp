<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollResource
 * @brief Representa un recurso para la nómina
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollResource extends JsonResource
{
    /**
     * Transforma el recurso a un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $payrollConcepts = [];
        $payrollConceptAccount = false;
        foreach ($this->resource->payrollPaymentPeriod?->payrollPaymentType?->payrollConcepts as $payrollConcept) {
            if ($payrollConcept['accounting_account_id'] == null || $payrollConcept['budget_account_id'] == null) {
                $payrollConcepts[] = [
                    'name' => $payrollConcept?->name ?? '',
                    'accounting_account_id' => $payrollConcept?->accounting_account_id ?? '',
                    'budget_account_id' => $payrollConcept?->budget_account_id ?? '',
                    'concept_account' => false,
                ];

                $payrollConceptAccount = true;
            } else {
                $payrollConcepts[] = [
                    'accounting_account_id' => $payrollConcept?->accounting_account_id ?? '',
                    'budget_account_id' => $payrollConcept?->budget_account_id ?? '',
                    'concept_account' => true,
                ];
            }
        }

        $result = array_values(array_filter($payrollConcepts, function ($item) {
            return $item['concept_account'] === false;
        }));

        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'created_at' => $this->resource->created_at,
            'name' => $this->resource->name,
            'status' => $this->resource->status,
            'payroll_payment_period' => [
                'id' => $this->resource->payrollPaymentPeriod->id,
                'payment_status' => $this->resource->payrollPaymentPeriod?->payment_status ?? '',
                'availability_status' => $this->resource->payrollPaymentPeriod?->availability_status ?? '',
                'start_date' => $this->resource->payrollPaymentPeriod?->start_date ?? '',
                'end_date' => $this->resource->payrollPaymentPeriod?->end_date ?? '',
                'payroll_payment_type' => ! empty($this->resource->payrollPaymentPeriod?->payrollPaymentType)
                    ? [
                        'id' => $this->resource->payrollPaymentPeriod->payrollPaymentType->id,
                        'name' => $this->resource->payrollPaymentPeriod->payrollPaymentType->name,
                        'skip_moments' => $this->resource->payrollPaymentPeriod->payrollPaymentType->skip_moments,
                        'receipt' => $this->resource->payrollPaymentPeriod->payrollPaymentType->receipt,
                    ]
                    : null,
                'payroll_concepts' => $payrollConceptAccount == false ? $payrollConcepts : $result,
                'payroll_concept_account' => $payrollConceptAccount,
            ],
        ];
    }
}
