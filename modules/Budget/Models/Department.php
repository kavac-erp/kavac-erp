<?php

namespace Modules\Budget\Models;

use App\Models\Department as BaseDepartment;

/**
 * @class Department
 * @brief Modelo que extiende las funcionalidades del modelo base Department
 *
 * Modelo que extiende las funcionalidades del modelo base Department
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Department extends BaseDepartment
{
    /**
     * Establece la relación con proyectos de presupuesto asociados a un departamento.
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetProjects()
    {
        return $this->hasMany(BudgetProject::class);
    }

    /**
     * Establece la relación con acciones centralizadas de presupuesto asociados a un departamento.
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCentralizedActions()
    {
        return $this->hasMany(BudgetCentralizedAction::class);
    }
}
