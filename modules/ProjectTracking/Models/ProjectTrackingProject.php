<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProject
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Oscar González <xxmaestroyixx@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProject extends Model implements Auditable
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
        "name",
        "description",
        "project_type_id",
        "code",
        "dependency_id",
        "type_product_id",
        "responsable_id",
        "financing_amount",
        "currency_id",
        "start_date",
        "end_date",
    ];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['currency'];

    /**
     * Obtiene los datos de la moneda
     *
     * @return \App\Models\Currency[]|null
     */
    public function getCurrencyAttribute()
    {
        return (\app\Models\Currency::find($this->currency_id));
    }

    /**
     * Establece la relación con el responsable del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsable()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class)
          : $this->belongsTo(ProjectTrackingPersonalRegister::class);
    }

    /**
     * Establece la relación con el tipo de proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function projectType()
    {
        return $this->belongsTo(ProjectTrackingProjectType::class);
    }

    /**
     * Establece la relación con el tipo de producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function typeProduct()
    {
        return $this->belongsTo(ProjectTrackingTypeProducts::class);
    }

    /**
     * Establece la relación morfológica con los tipos de productos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function productTypes(): MorphToMany
    {
        return $this->morphToMany(ProjectTrackingTypeProducts::class, 'typeable');
    }

    /**
     * Establece la relación con la dependencia del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dependency()
    {
        return $this->belongsTo(ProjectTrackingDependency::class);
    }

    /**
     * Establece la relación con los subproyectos del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function subprojects()
    {
        return $this->hasMany(ProjectTrackingSubProject::class, 'id', 'project_id');
    }

    /**
     * Establece la relación con los productos del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    /**
     * Establece la relación con las tareas del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function tasks()
    {
        return $this->belongsToMany(ProjectTrackingTask::class);
    }

    /**
     * Establece la relación con el plan de actividades del proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'project_name');
    }

    /**
     * Obtiene los IDs de los tipos de productos asociados al proyecto
     *
     * @return array
     */
    public function getProductTypeIds(): array
    {
        return $this->productTypes->pluck('id')->toArray();
    }
}
