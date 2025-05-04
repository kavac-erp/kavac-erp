<?php

namespace Modules\Purchase\Models;

use App\Models\Document as BaseDocument;

/**
 * @class Document
 * @brief Extension de la clase Document de la aplicación base
 *
 * Extension de la clase Document de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Document extends BaseDocument
{
    /**
     * Establece la relación con los planes de compra asociados a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasePlans()
    {
        return $this->hasMany(PurchasePlan::class);
    }

    /**
     * Establece la relación con un requerimiento de compra asociado a un documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchaseDocumentRequiredDocument()
    {
        return $this->hasOne(PurchaseDocumentRequiredDocument::class);
    }
}
