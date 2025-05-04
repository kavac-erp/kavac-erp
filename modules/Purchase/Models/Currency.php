<?php

namespace Modules\Purchase\Models;

use App\Models\Currency as BaseCurrency;

/**
 * @class Currency
 * @brief Extension de la clase Currency de la aplicación base
 *
 * Extension de la clase Currency de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Currency extends BaseCurrency
{
    /**
     * Establece la relación con los presupuestos base asociados a una moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseBaseBudget()
    {
        return $this->hasMany(PurchaseBaseBudget::class);
    }

    /**
     * Establece la relación con las ordenes de compra asociadas a una moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseOrder()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
