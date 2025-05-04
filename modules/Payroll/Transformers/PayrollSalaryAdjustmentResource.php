<?php

/**
 * Transformación del recurso para obtener colección de un ajuste en tablas salariales
 * */

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Payroll\Transformers\PayrollSalaryScaleResource;
use Modules\Payroll\Transformers\PayrollSalaryTabulatorScalesResource;

/**
 * @class PayrollSalaryAdjustmentResource
 * @brief Transformación del recurso de colección en un arreglo para uno o más ajustes en tablas salariales
 *
 * Clase para obtener una colección del ajuste en tablas salariales
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryAdjustmentResource extends JsonResource
{
    /**
     * Transforma el recurso de colección en un arreglo.
     *
     * @param  \Illuminate\Http\Request     Datos de la petición
     *
     * @return array    Devuelve un arreglo con los datos de la colección
     */
    public function toArray($request)
    {
        $common_fields = [
            'id' => $this->id,
            'increase_of_date' => $this->increase_of_date ?? '',
            'increase_of_type' => $this->increase_of_type ?? '',
            'value' => $this->value ?? '',
            'created_at' => $this->created_at ?? '',
            'payroll_salary_tabulator' => [
                'id' => $this->payrollSalaryTabulator?->id,
                'name' => $this->payrollSalaryTabulator?->name ?? '',
                'description' => $this->payrollSalaryTabulator?->description ?? '',
                'code' => $this->payrollSalaryTabulator?->code ?? '',
                'payroll_salary_tabulator_type' => $this->payrollSalaryTabulator?->payroll_salary_tabulator_type ?? '',
                'payroll_salary_tabulator_scales' =>
                    PayrollSalaryTabulatorScalesResource::collection(
                        $this->payrollsalaryTabulator?->payrollSalaryTabulatorScales
                    ),
                'payroll_vertical_salary_scale' => $this->payrollSalaryTabulator?->payrollVerticalSalaryScale ?
                new PayrollSalaryScaleResource(
                    $this->payrollSalaryTabulator->payrollVerticalSalaryScale
                ) : '',
                'payroll_vertical_scales' => $this->payrollSalaryTabulator?->payrollVerticalSalaryScale ?
                    PayrollScaleResource::collection(
                        $this->payrollSalaryTabulator->payrollVerticalSalaryScale->payrollScales
                    ) : '',
                'payroll_horizontal_salary_scale' => $this->payrollSalaryTabulator?->payrollHorizontalSalaryScale ?
                new PayrollSalaryScaleResource(
                    $this->payrollSalaryTabulator->payrollHorizontalSalaryScale
                ) : '',
                'payroll_horizontal_scales' => $this->payrollSalaryTabulator?->payrollHorizontalSalaryScale ?
                PayrollScaleResource::collection(
                    $this->payrollSalaryTabulator->payrollHorizontalSalaryScale->payrollScales
                ) : '',
            ]
        ];

        return $common_fields;
    }
}
