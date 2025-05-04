<?php

/**
 * Transformación del recurso para obtener colección de la escala de un tabulador salarial
 * */

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollSalaryTabulatorScalesResource
 * @brief Transformación del recurso de colección en un arreglo para uno o más escalas del tabulador salarial
 *
 * Clase para obtener una colección de la escala del tabulador salarial
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryTabulatorScalesResource extends JsonResource
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
            'id' =>    $this->id,
            'value' => $this->value ?? '',
            'payroll_vertical_scale_id' => $this->payroll_vertical_scale_id ?? '',
            'payroll_horizontal_scale_id' => $this->payroll_horizontal_scale_id ?? '',
        ];

        return $common_fields;
    }
}
