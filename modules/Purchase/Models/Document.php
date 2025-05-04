<?php

namespace Modules\Purchase\Models;

use App\Models\Document as BaseDocument;

class Document extends BaseDocument
{
    /**
     * Document has many UserPurchaseRequirements.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchasePlans()
    {
        return $this->hasMany(PurchasePlans::class);
    }

    /**
     * Get the purchaseDocumentRequiredDocument associated with the Document
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function purchaseDocumentRequiredDocument()
    {
        return $this->hasOne(PurchaseDocumentRequiredDocument::class);
    }
}
