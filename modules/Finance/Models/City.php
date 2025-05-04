<?php

namespace Modules\Finance\Models;

use App\Models\City as BaseCity;

/**
 * @class City
 * @brief Clase que extiende del modelo de Ciudad de la aplicación base
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class City extends BaseCity
{
    /**
     * Método que obtiene las agencias bancarias de una Ciudad
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bankingAgencies()
    {
        return $this->hasMany(FinanceBankingAgency::class);
    }
}
