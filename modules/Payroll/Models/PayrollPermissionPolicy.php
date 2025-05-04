<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollPermissionPolicy
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo de políticas de permisos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPermissionPolicy extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name', 'anticipation_day', 'time_min', 'time_max', 'active', 'business_days', 'institution_id', 'time_unit'
    ];

    /**
     * Obtiene la relación con solicitudes de permisos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollPermissionRequests()
    {
        return $this->hasMany(PayrollPermissionRequest::class);
    }
}
