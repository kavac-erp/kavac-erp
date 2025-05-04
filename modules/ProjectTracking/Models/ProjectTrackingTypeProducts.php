<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * @class ProjectTrackingTypeProducts
 * @brief Modelo encargado de manejar la comunicacion con la tabla project_tracking_type_products
 *
 * Gestiona el modelo de datos de los tipos de productos
 *
 * @author    Francisco Escala <fjescala@gmail.com>
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
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['name', 'description'];

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

    /**
     * Método que obtiene la información de los proyectos asociados a un tipo de producto
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function projects(): MorphToMany
    {
        return $this->morphedByMany(ProjectTrackingProject::class, 'typeable');
    }

    /**
     * Método que obtiene la información de los subproyectos asociados a un tipo de producto
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function subProjects(): MorphToMany
    {
        return $this->morphedByMany(ProjectTrackingSubProject::class, 'typeable');
    }

    /**
     * Método que obtiene la información de los productos asociados a un tipo de producto
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function products(): MorphToMany
    {
        return $this->morphedByMany(ProjectTrackingProduct::class, 'typeable');
    }
}
