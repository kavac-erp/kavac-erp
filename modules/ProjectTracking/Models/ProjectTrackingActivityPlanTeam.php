<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingActivityPlanTeam
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityPlanTeam extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['employers_id', 'staff_classification_id', 'activity_plan_id'];

    public function projectTrackingPersonalRegister()
    {
        return(Module::has('Payroll') && Module::isEnabled('Payroll')) ?
                $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class, 'employers_id', 'id') :
                $this->belongsTo(ProjectTrackingPersonalRegister::class, 'employers_id', 'id');
    }

    public function projectTrackingStaffClassification()
    {
        return $this->belongsTo(ProjectTrackingStaffClassification::class, 'staff_classification_id', 'id');
    }

    public function projectTrackingActivityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'employers_id', 'id');
    }
}
