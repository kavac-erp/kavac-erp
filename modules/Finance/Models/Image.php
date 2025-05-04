<?php

namespace Modules\Finance\Models;

use App\Models\Image as BaseImage;

/**
 * @class Image
 * @brief Estiende de la clase Image de la aplicación base
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Image extends BaseImage
{
    /**
     * Obtiene la relación con las entidades bancarias
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeBanks()
    {
        return $this->hasMany(FinanceBank::class, 'logo_id');
    }
}
