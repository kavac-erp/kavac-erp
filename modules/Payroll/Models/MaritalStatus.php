<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\MaritalStatus as BaseMaritalStatus;

/**
 * @class      MaritalStatus
 * @brief      Modelo que extiende las funcionalidades del modelo base MaritalStatus
 *
 * Modelo que extiende las funcionalidades del modelo base MaritalStatus
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class MaritalStatus extends BaseMaritalStatus
{
    /**
     * Método que obtiene el estado civil asociado a muchas informaciones socioeconómicas del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollSocioecomics()
    {
        return $this->hasMany(PayrollSocioeconomic::class);
    }
}
