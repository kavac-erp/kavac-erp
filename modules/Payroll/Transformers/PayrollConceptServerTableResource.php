<?php

namespace Modules\Payroll\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class PayrollConceptServerTableResource
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptServerTableResource extends JsonResource
{
    /**
     * Transforma el recurso de colección en un arreglo.
     *
     * @method toArray
     *
     * @param  \Illuminate\Http\Request     Objeto con datos de la petición
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
