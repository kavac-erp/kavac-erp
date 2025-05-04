<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use App\Models\Gender as BaseGender;

/**
 * @class      PayrollGender
 * @brief      Datos de género
 *
 * Gestiona el modelo de géneros
 *
 * @author     William Páez <wpaez@cenditel.gob.ve>
 *
 * @property integer $id ID del registro
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollGender extends BaseGender
{
    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = "genders";

    /**
     * Método que obtiene el género que está asociado a muchas informaciones personales del trabajador
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffs()
    {
        return $this->hasMany(PayrollStaff::class);
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un género
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'assignable');
    }
}
