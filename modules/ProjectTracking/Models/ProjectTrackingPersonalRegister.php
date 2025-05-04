<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class ProjectTrackingPersonalRegister
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Oscar González <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingPersonalRegister extends Model implements Auditable
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
        'name', 'last_name', 'id_number', 'position_id'
    ];

    /**
     * Obtiene el nombre completo de la persona
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return    string    Nombre completo de la persona
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->name} {$this->last_name}";
    }

    /**
     * Establece la relación con el cargo
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function position()
    {
        return $this->belongsTo(ProjectTrackingPosition::class);
    }

    /**
     * Establece la relación con los proyectos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function projects()
    {
        return $this->belongsToMany(ProjectTrackingProject::class);
    }

    /**
     * Establece la relación con los subproyectos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function subProject()
    {
        return $this->belongsToMany(ProjectTrackingSubProject::class);
    }

    /**
     * Establece la relación con los productos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    /**
     * Establece la relación con el equipo asociado al plan de actividades
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function activityPlanTeam()
    {
        return $this->belongsToMany(ProjectTrackingActivityPlanTeam::class);
    }
}
