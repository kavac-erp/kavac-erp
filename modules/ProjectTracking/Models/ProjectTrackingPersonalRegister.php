<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class ProjectTrackingPersonalRegister
 * @brief [descripción detallada]
 *
 * [descripción corta]
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
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'name', 'last_name', 'id_number', 'position_id'
    ];

    public function position()
    {
        return $this->belongsTo(ProjectTrackingPosition::class);
    }

    public function projects()
    {
        return $this->belongsToMany(ProjectTrackingProject::class);
    }

    public function subProject()
    {
        return $this->belongsToMany(ProjectTrackingSubProject::class);
    }

    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    public function activityPlanTeam()
    {
        return $this->belongsToMany(ProjectTrackingActivityPlanTeam::class);
    }
}
