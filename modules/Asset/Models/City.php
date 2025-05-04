<?php

namespace Modules\Asset\Models;

use App\Models\City as BaseCity;

/**
 * @class City
 * @brief Extiende del modelo de ciudad de la aplicación base
 *
 * @author Pedro Contreras <pmcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class City extends BaseCity
{
    /**
     * Obtiene la relación con los proveedores de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetSuppliers()
    {
        return $this->hasMany(assetSupplier::class);
    }
}
