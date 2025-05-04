<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'created_at' => $this->resource->created_at,
            'name' => $this->resource->name,
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
                    ]
                    : null,
            ],
        ];
    }
}
