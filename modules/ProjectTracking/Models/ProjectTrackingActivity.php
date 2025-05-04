<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Builder;

/**
 * @class ProjectTrackingActivity
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingActivity extends Model implements Auditable
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
        'name',
        'orden',
        'name_activity',
        'description',
        'project_tracking_type_products_id',
        'project_tracking_project_types_id'
    ];

    /**
     * Método que obtiene la información de tipo proyectos asociados al proyecto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectTrackingProjectTypes()
    {
        return $this->belongsTo(ProjectTrackingProjectType::class);
    }

    /**
     * Método que obtiene la información de los tipo productos asociados al producto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectTrackingTypeProducts()
    {
        return $this->belongsTo(ProjectTrackingTypeProducts::class);
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

    /**
     * Scope para filtrar actividades por tipo de producto
     *
     * @param \Illuminate\Database\Eloquent\Builder|string|array $query Query de Eloquent
     * @param integer $product_type_id ID de tipo de producto
     *
     * @return void
     */
    public function scopeFilterActivityesByProductType($query, ?int $product_type_id): void
    {
        $query()
            ->where('project_tracking_type_products_id', $product_type_id);
    }

    /**
     * Scope para filtrar actividades por tipo de producto
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Query de Eloquent
     * @param array $product_type_ids IDs de tipos de productos
     *
     * @return void
     */
    public function scopeFilterActivityesByProductTypes(Builder $query, array $product_type_ids): void
    {
        $query
            ->whereIn('project_tracking_type_products_id', $product_type_ids);
    }
}
