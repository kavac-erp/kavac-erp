<?php

namespace Modules\Purchase\Models;

use Nwidart\Modules\Facades\Module;
use App\Models\DocumentStatus as BaseDocumentStatus;

/**
 * @class DocumentStatus
 * @brief Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * Modelo que extiende las funcionalidades del modelo base DocumentStatus
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DocumentStatus extends BaseDocumentStatus
{
    /**
     * Establece la relación con las formulaciones presupuestarias del módulo de presupuesto si esta presente
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetSubSpecificFormulations()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetSubSpecificFormulation::class) : [];
    }

    /**
     * Establece la relación con las modificaciones presupuestarias del módulo de presupuesto si esta presente
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>

     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetModifications()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetModification::class) : [];
    }

    /**
     * Establece la relación con los compromisos presupuestarios del módulo de presupuesto si esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCompromise()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(BudgetCompromise::class) : [];
    }
}
