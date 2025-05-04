<?php

namespace Modules\Purchase\Models;

use App\Models\Department as BaseDepartment;

/**
 * @class Department
 * @brief Extension de la clase Department de la aplicación base
 *
 * Extension de la clase Department de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Department extends BaseDepartment
{
    /**
     * Establece la relación con los requerimientos de compra asociados a un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contratingPurchaseRequirements()
    {
        return $this->hasMany(PurchaseRequirement::class, 'contracting_department_id');
    }

    /**
     * Establece la relacion con los requerimientos de compra asociados a un usuario
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function userPurchaseRequirements()
    {
        return $this->hasMany(PurchaseRequirement::class, 'user_department_id');
    }
}
