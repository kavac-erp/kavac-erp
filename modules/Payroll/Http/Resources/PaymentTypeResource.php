<?php

declare(strict_types=1);

namespace Modules\Payroll\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PaymentTypeResource
 * @brief Representa un recurso para el tipo de pago
 *
 * @author Ing. Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PaymentTypeResource extends JsonResource
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
        return [
            'id'                    => $this->resource->id,
            'code'                  => $this->resource->code,
            'name'                  => $this->resource->name,
            'payment_periodicity'   => $this->resource->payment_periodicity,
            'periods_number'        => '',
            'order'                 => $this->resource->order,
            'receipt'               => $this->resource->receipt,
            'individual'            => $this->resource->individual,
            'skip_moments'          => $this->resource->skip_moments,
            'start_date'            => $this->resource->start_date,
            'finance_bank_account_id' => $this->resource->finance_bank_account_id,
            'finance_payment_method_id' => $this->resource->finance_payment_method_id,
            'accounting_entry_category_id' => $this->resource->accounting_entry_category_id,
            'payroll_concepts'      => $this->resource->payrollConcepts->map(fn ($model) => [
                'id' => $model->id,
                'text' => $model->name
            ]),
            'payroll_payment_periods' => $this->resource->payrollPaymentPeriods()
                ->orderBy('id')
                ->get()
                ->map(function ($model) {
                    //verifica si tiene una nomina asociada con ese periodo
                    $model['in_payroll'] = optional($model->payroll)->exists() ? true : false;
                    return $model;
                })
        ];
    }
}
