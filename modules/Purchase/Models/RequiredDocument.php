<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Models;

use App\Models\RequiredDocument as BaseRequiredDocument;

/**
 * @class RequiredDocument
 * @brief Modelo que extiende de la aplicacion base
 *
 * Modelo que extiende de la aplicacion base
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class RequiredDocument extends BaseRequiredDocument
{
    /**
     * Get all of the purchaseDocumentRequiredDocument for the RequiredDocument
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseDocumentRequiredDocument()
    {
        return $this->hasMany(PurchaseDocumentRequiredDocument::class);
    }
}
