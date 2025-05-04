<?php

namespace Modules\Payroll\Models;

use App\Models\Parish as BaseParish;

/**
 * @class      Parish
 * @brief      Modelo que extiende las funcionalidades del modelo base Parish
 *
 * Modelo que extiende las funcionalidades del modelo base Parish
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Parish extends BaseParish
{
    /**
     * Método que obtiene la parroquia asociada a muchas informaciones personales del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffs()
    {
        return $this->hasMany(PayrollStaff::class);
    }
}
