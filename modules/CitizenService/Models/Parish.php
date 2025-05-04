<?php

namespace Modules\CitizenService\Models;

use App\Models\Parish as BaseParish;

/**
 * @class Parish
 * @brief Extiende del modelo Parish de la aplicación base
 *
 * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parish extends BaseParish
{
    /**
     * Método que obtiene la parroquia asociado con solicitudes
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
