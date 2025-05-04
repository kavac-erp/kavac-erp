<?php

namespace Modules\Purchase\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class PurchaseBudgetAvailabilityPayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $currency = $this->resource->payrollPaymentPeriod->payrollPaymentType->payrollConcepts->first()->currency;

        $common_fields = [ 
            'id' => $this->resource->id,
            'code' => $this->resource->code ?? '',
            'description' => $this->resource->name ?? '',
            'currency_name' => $currency->name ?? '',
            'available' => $this->resource->payrollPaymentPeriod?->availability_status ?? '',
            'module' => 'Payroll'
        ];

        return $common_fields;
    }
}