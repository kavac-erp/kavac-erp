<?php

namespace Modules\Asset\Models;

use App\Models\Document as BaseDocument;

/**
 * @class Document
 * @brief Extiende del modelo de documento de la aplicación base
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Document extends BaseDocument
{
    /**
     * Obtiene la relación con el documento requerido
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function assetDocumentRequiredDocument()
    {
        return $this->hasOne(AssetDocumentRequiredDocument::class);
    }
}
