<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class ProjectTrackingActivityPlanActivity
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityPlanActivity extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = 'project_tracking_activity_plans_activity';
    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['activity_id', 'responsable_activity_id', 'start_date',
    'end_date', 'activity_plan_id', 'percentage'];

    public function projectTrackingActivities()
    {
        return $this->belongsTo(ProjectTrackingActivity::class, 'activity_id', 'id');
    }

    public function projectTrackingTeamMember()
    {
        return $this->hasMany(ProjectTrackingActivityPlanTeam::class, 'activity_plan_id', 'id');
    }

    public function projectTrackingActivityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(ProjectTrackingTask::class);
    }
}
