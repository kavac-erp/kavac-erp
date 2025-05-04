<?php

namespace Modules\Purchase\Models;

use App\Models\FiscalYear as BaseFiscalYear;

/**
 * @class FiscalYear
 * @brief Modelo que extiende las funcionalidades del modelo base FiscalYear
 *
 * Modelo que extiende las funcionalidades del modelo base FiscalYear
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FiscalYear extends BaseFiscalYear
{
    /**
     * Establece la relación con los requerimientos de compra de un año fiscal
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRequirements()
    {
        return $this->hasMany(PurchaseRequirement::class);
    }
}
