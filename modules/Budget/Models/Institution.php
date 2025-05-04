<?php

namespace Modules\Budget\Models;

use App\Models\Institution as BaseInstitution;

/**
 * @class Institution
 * @brief Modelo que extiende las funcionalidades del modelo base Institution
 *
 * Modelo que extiende las funcionalidades del modelo base Institution
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Institution extends BaseInstitution
{
    /**
     * Obtiene la relación con las formulaciones presupuestarias asociadas a una institución
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetSubSpecificFormulations()
    {
        return $this->hasMany(BudgetSubSpecificFormulation::class);
    }

    /**
     * Obtiene la relación con las modificaciones presupuestarias asociadas a una institución
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetModifications()
    {
        return $this->hasMany(BudgetModification::class);
    }
}
