<?php

namespace Modules\Purchase\Models;

use App\Models\City as BaseCity;

/**
 * @class City
 * @brief Extension de la clase City de la aplicación base
 *
 * Extension de la clase City de la aplicación base
 *
 * @property string $algo
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class City extends BaseCity
{
    /**
     * Establece la relación con los proveedores ubicados en una ciudad
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseSuppliers()
    {
        return $this->hasMany(PurchaseSupplier::class);
    }
}
