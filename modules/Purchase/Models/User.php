<?php

namespace Modules\Purchase\Models;

use App\Models\User as BaseTax;

/**
 * @class User
 * @brief Extiende del modelo de usuario de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class User extends BaseTax
{
    /**
     * Establece la relación con el plan de compras
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasePlans()
    {
        // hasMany(RelatedModel, foreignKeyOnRelatedModel = currency_id, localKey = id)
        return $this->hasMany(PurchasePlan::class);
    }
}
