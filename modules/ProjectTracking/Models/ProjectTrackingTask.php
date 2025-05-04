<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class ProjectTrackingTask
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTask extends Model implements Auditable
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
    protected $fillable = ['project_name', 'activity_plan_id', 'subproject_name', 'product_name', 'name', 'description', 'employers_id', 'priority_id', 'start_date', 'end_date', 'activity_status_id', 'weight'];

    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'project_name', 'id');
    }

    public function subproject()
    {
        return $this->belongsTo(ProjectTrackingSubProject::class, 'subproject_name', 'id');
    }

    public function product()
    {
        return $this->belongsTo(ProjectTrackingProduct::class, 'product_name', 'id');
    }

    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    public function activity()
    {
        return $this->belongsTo(ProjectTrackingActivity::class);
    }

    public function activityStatus()
    {
        return $this->belongsTo(ProjectTrackingActivityStatus::class);
    }

    public function responsable()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class);
    }

    public function priority()
    {
        return $this->belongsTo(ProjectTrackingPriority::class);
    }
}
