<?php

namespace Modules\Asset\Models;

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
     * Obtiene la relación con los documentos requeridos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetDocumentRequiredDocument()
    {
        return $this->hasMany(AssetDocumentRequiredDocument::class);
    }
}
