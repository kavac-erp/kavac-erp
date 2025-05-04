<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class ProjectTrackingActivityPlanActivity
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityPlanActivity extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = 'project_tracking_activity_plans_activity';

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
        'activity_id', 'responsable_activity_id', 'start_date',
        'end_date', 'activity_plan_id', 'percentage'
    ];

    /**
     * Establece la relación con la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectTrackingActivities()
    {
        return $this->belongsTo(ProjectTrackingActivity::class, 'activity_id', 'id');
    }

    /**
     * Establece la relación con los miembros del equipo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectTrackingTeamMember()
    {
        return $this->hasMany(ProjectTrackingActivityPlanTeam::class, 'activity_plan_id', 'id');
    }

    /**
     * Establece la relación con el plan de actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectTrackingActivityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    /**
     * Establece la relación con las tareas
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tasks()
    {
        return $this->belongsToMany(ProjectTrackingTask::class);
    }
}
