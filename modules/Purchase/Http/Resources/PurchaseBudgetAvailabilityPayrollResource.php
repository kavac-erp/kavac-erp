<?php

namespace Modules\Purchase\Http\Resources;

use Nwidart\Modules\Facades\Module;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PurchaseBudgetAvailabilityPayrollResource
 * @brief Gestiona los recursos de las disponibilidades presupuestarias en compras asociadas a los conceptos de nómina
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseBudgetAvailabilityPayrollResource extends JsonResource
{
    /**
     * Transforma el recurso de colección en un arreglo.
     *
     * @param  \Illuminate\Http\Request  $request Datos de la petición
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        if (!Module::has('Payroll') || !Module::isEnabled('Payroll')) {
            return [];
        }
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