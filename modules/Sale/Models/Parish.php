<?php

namespace Modules\Sale\Models;

use App\Models\Parish as BaseParish;

/**
 * @class Parish
 * @brief Extiende de la clase Parish de la aplicación base
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parish extends BaseParish
{
    /**
     * Método que obtiene la parroquia asociado con clientes
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleClient()
    {
        return $this->hasMany(SaleClient::class);
    }
}
