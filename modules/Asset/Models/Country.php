<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Country as BaseCountry;

/**
 * @class Country
 * @brief Extension de la clase Country de la aplicación base
 *
 * Extension de la clase Country de la aplicación base
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Country extends BaseCountry implements Auditable
{
    /**
     * Obtiene la relación con los proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetSuppliers()
    {
        return $this->hasMany(AssetSupplier::class);
    }
}
