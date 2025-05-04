<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Institution as BaseInstitution;

/**
 * @class Institution
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Institution extends BaseInstitution
{
    /**
     * institution has many PurchaseDirectHire.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseDirectHire()
    {
        // hasMany(RelatedModel, foreignKeyOnRelatedModel = institution_id, localKey = id)
        return $this->hasMany(PurchaseDirectHire::class);
    }
}
