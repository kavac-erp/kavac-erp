<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use App\Models\Institution;

/**
 * @class ProjectTrackingActivityPlan
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivityPlan extends Model implements Auditable
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
        "code",
        "project_name",
        "subproject_name",
        "product_name",
        "institution_id",
        "execution_year",
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
    public function subProject()
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
     * Establece la relación con la institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Establece la relación con las actividades
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activities()
    {
        return $this->hasMany(ProjectTrackingActivityPlanActivity::class, 'activity_plan_id', 'id');
    }

    /**
     * Establece la relación con los equipos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function teams()
    {
        return $this->hasMany(ProjectTrackingActivityPlanTeam::class, 'activity_plan_id', 'id');
    }
}
