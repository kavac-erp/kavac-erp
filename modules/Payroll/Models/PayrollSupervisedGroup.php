<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @class PayrollSupervisedGroup
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSupervisedGroup extends Model implements Auditable
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
    protected $fillable = ['code', 'supervisor_id', 'approver_id'];

    /**
     * Método que obtiene la información personal del trabajador asociada a un supervisor
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function supervisor()
    {
        return $this->belongsTo(PayrollStaff::class, 'supervisor_id');
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un aprobador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approver()
    {
        return $this->belongsTo(PayrollStaff::class, 'approver_id');
    }

    /**
     * Método que obtiene la información de los trabajadores asociados a un grupo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollSupervisedGroupStaff()
    {
        return $this->hasMany(PayrollSupervisedGroupStaff::class);
    }

    /**
     * Get all of the payrollGuardSchemes for the PayrollSupervisedGroup
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollGuardSchemes(): HasMany
    {
        return $this->hasMany(PayrollGuardScheme::class);
    }
}
