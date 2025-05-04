<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollPermissionRequest
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo de solicitudes de permisos
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPermissionRequest extends Model implements Auditable
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
        'status', 'date', 'payroll_staff_id', 'payroll_permission_policy_id', 'start_date',
        'end_date', 'time_permission', 'motive_permission', 'start_time', 'end_time'
    ];

    /**
     * Método que obtiene la información del trabajador asociado a la solicitud de permiso
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Obtiene la relación con políticas de permisos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPermissionPolicy()
    {
        return $this->belongsTo(PayrollPermissionPolicy::class);
    }
}
