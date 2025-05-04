<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class ProjectTrackingTask
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
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
        'project_name',
        'activity_plan_id',
        'subproject_name',
        'product_name',
        'name',
        'description',
        'employers_id',
        'priority_id',
        'start_date',
        'end_date',
        'activity_status_id',
        'weight'
    ];

    /**
     * Establece la relación con el proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'project_name', 'id');
    }

    /**
     * Establece la relación con el subproyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subproject()
    {
        return $this->belongsTo(ProjectTrackingSubProject::class, 'subproject_name', 'id');
    }

    /**
     * Establece la relación con el producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProjectTrackingProduct::class, 'product_name', 'id');
    }

    /**
     * Establece la relación con el plan de actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class);
    }

    /**
     * Establece la relación con la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activity()
    {
        return $this->belongsTo(ProjectTrackingActivity::class, 'activity_plan_id', 'id');
    }

    /**
     * Establece la relación con el estatus de la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityStatus()
    {
        return $this->belongsTo(ProjectTrackingActivityStatus::class);
    }

    /**
     * Establece la relación con el responsable de la actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsable()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'employers_id', 'id');
    }

    /**
     * Establece la relación con la prioridad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function priority()
    {
        return $this->belongsTo(ProjectTrackingPriority::class);
    }
}
