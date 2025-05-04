<?php

namespace Modules\CitizenService\Models;

use App\Models\City as BaseCity;

/**
 * @class City
 * @brief Extiende del modelo City de la aplicación base
 *
 * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class City extends BaseCity
{
    /**
     * Método que obtiene la ciudad asociado con solicitudes
     *
     * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceRequests()
    {
        return $this->hasMany(CitizenServiceRequest::class);
    }
}
