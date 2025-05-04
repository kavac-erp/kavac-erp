<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Profession as BaseProfession;

/**
 * @class      Profession
 * @brief      Extiende del modelo de Profession de la aplicación base
 *
 * @author     William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Profession extends BaseProfession
{
    /**
     * Método que obtiene las profesiones asociadas a muchas informaciones profesionales del trabajador
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payrollProfessionals()
    {
        return $this->belongsToMany(PayrollProfessional::class)->withTimestamps();
    }
}
