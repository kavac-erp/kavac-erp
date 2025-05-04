<?php

namespace Modules\Asset\Transformers;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @class FloorCollection
 * @brief Transforma un objeto en una colección de recursos
 *
 * @author Natanael Rojo <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FloorCollection extends ResourceCollection
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
        return parent::toArray($request);
    }
}
