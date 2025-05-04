<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Country as BaseCountry;

/**
 * @class Country
 * @brief Modelo que extiende de la aplicación base para la gestión de paises
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Country extends BaseCountry
{
    /**
     * Método que obtiene el país asociado a una nacionalidad
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollNationality()
    {
        return $this->hasOne(PayrollNationality::class);
    }
}
