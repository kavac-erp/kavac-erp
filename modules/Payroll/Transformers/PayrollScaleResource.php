<?php

/**
 * Transformación del recurso para obtener colección de la escala o niveles de un escalafón
 * */

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollScaleResource
 * @brief Transformación del recurso de colección en un arreglo para uno o más escalas
 *
 * Clase para obtener una colección de la escala o niveles de un escalafón
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollScaleResource extends JsonResource
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
        ];

        return $common_fields;
    }
}
