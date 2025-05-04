<?php

/**
 * Transformación del recurso para obtener colección de un tabulador salarial
 * */

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollSalaryTabulatorResource
 * @brief Transformación del recurso de colección en un arreglo para uno o más tabuladores salariales
 *
 * Clase para obtener una colección del tabulador salarial
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorResource extends JsonResource
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
        $fields  = [];
        $records = [];
        $info = [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'payroll_salary_tabulator_type' => $this->resource->payroll_salary_tabulator_type,
            'code' => $this->resource->code,
            'is_active' => $this->resource->is_active
        ];
        $headers = [];
        if ($this->resource->payroll_horizontal_salary_scale_id > 0) {
            array_push($headers, 'Nombre');
            $payrollHorizontalSalaryScale = $this->resource->payrollHorizontalSalaryScale;
            foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollScale) {
                array_push($headers, $payrollScale->name);
            }
        } elseif ($this->resource->payroll_vertical_salary_scale_id > 0) {
            array_push($headers, 'Nombre');
            array_push($headers, 'Incidencia');
        }

        foreach ($this->resource->payrollSalaryTabulatorScales as $payrollSalaryTabulatorScale) {
            if ($this->resource->payroll_salary_tabulator_type == 'mixed') {
                $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                $fields[$horizontalScale->name . '-' . $verticalScale->name] = $payrollSalaryTabulatorScale->value;
            } elseif ($this->payroll_salary_tabulator_type == 'horizontal') {
                $horizontalScale = $payrollSalaryTabulatorScale->payrollHorizontalScale;
                $fields[$horizontalScale->name] = $payrollSalaryTabulatorScale->value;
            } elseif ($this->resource->payroll_salary_tabulator_type == 'vertical') {
                $verticalScale = $payrollSalaryTabulatorScale->payrollVerticalScale;
                $fields[$verticalScale->name] = $payrollSalaryTabulatorScale->value;
            }
        }

        if ($this->resource->payroll_salary_tabulator_type == 'mixed') {
            $payrollHorizontalSalaryScale = $this->resource->payrollHorizontalSalaryScale;
            $payrollVerticalSalaryScale = $this->resource->payrollVerticalSalaryScale;

            foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                array_push($records, $payrollVerticalScale->name);
                foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                    array_push(
                        $records,
                        $fields[$payrollHorizontalScale->name . '-' . $payrollVerticalScale->name]
                    );
                }
            }
            $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
        } elseif ($this->payroll_salary_tabulator_type == 'horizontal') {
            $payrollHorizontalSalaryScale = $this->resource->payrollHorizontalSalaryScale;
            array_push($records, 'Incidencia');
            foreach ($payrollHorizontalSalaryScale->payrollScales as $payrollHorizontalScale) {
                array_push($records, $fields[$payrollHorizontalScale->name]);
            }
            $records = array_chunk($records, count($payrollHorizontalSalaryScale->payrollScales) + 1);
        } elseif ($this->resource->payroll_salary_tabulator_type == 'vertical') {
            $payrollVerticalSalaryScale = $this->resource->payrollVerticalSalaryScale;
            foreach ($payrollVerticalSalaryScale->payrollScales as $payrollVerticalScale) {
                array_push($records, $payrollVerticalScale->name);
                array_push($records, $fields[$payrollVerticalScale->name]);
            }
            $records = array_chunk($records, 2);
        }

        array_push($records, $headers);
        array_push($records, $info);
        return $records;
    }
}
