<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution as BaseInstitution;

/**
 * @class Institution
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Institution extends BaseInstitution
{
    /**
     * Establece la relación con las ordenes de compra asociadas a una institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseDirectHire()
    {
        return $this->hasMany(PurchaseDirectHire::class);
    }
}
