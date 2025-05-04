<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class ProjectTrackingTypeProducts
 * @brief Modelo encargado de manejar la comunicacion con la tabla project_tracking_type_products
 *
 * Gestiona el modelo de datos de los tipos de productos
 *
 * @author    [Francisco Escala] [fjescala@gmail.com]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingTypeProducts extends Model implements Auditable
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
    protected $fillable = ['name','description'];

    /**
     * Método que obtiene la información de las actividades asociados al tipo de proyecto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function projectTrackingActivities()
    {
        return $this->hasMany(ProjectTrackingActivity::class);
    }

    public function project()
    {
        return $this->belongsToMany(ProjectTrackingProject::class);
    }

    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }
}
