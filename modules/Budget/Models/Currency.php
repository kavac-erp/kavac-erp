<?php

namespace Modules\Budget\Models;

use App\Models\Currency as BaseCurrency;

/**
 * @class Currency
 * @brief Modelo que extiende las funcionalidades del modelo base Currency
 *
 * Modelo que extiende las funcionalidades del modelo base Currency
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Currency extends BaseCurrency
{
    /**
     * Establece la relación con formulaciones presupuestarias asociadas a monedas
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetSubSpecificFormulations()
    {
        return $this->hasMany(BudgetSubSpecificFormulation::class);
    }
}
