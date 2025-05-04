<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tax as BaseTax;

/**
 * @class Tax
 * @brief Extiende del modelo de impuestos de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Tax extends BaseTax
{
    /**
     * Establece la relación con el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseBaseBudget()
    {
        return $this->hasMany(PurchaseBaseBudget::class);
    }
}
