<?php

/**
 * Transformación del recurso para obtener colección del escalafón salarial
 * */

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollSalaryScaleResource
 * @brief Transformación del recurso de colección en un arreglo para uno o más escalafones salariales
 *
 * Clase para obtener una colección del escalafón salarial
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSalaryScaleResource extends JsonResource
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
            'name' => $this->name ?? '',
            'description' => $this->description ?? '',
        ];

        return $common_fields;
    }
}
