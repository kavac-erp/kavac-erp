<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProduct
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Oscar González <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingProduct extends Model implements Auditable
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
        "project_id",
        "subproject_id",
        "name",
        "description",
        "code",
        "dependency_id",
        "responsable_id",
        "type_product_id",
        "start_date",
        "end_date"
    ];

    /**
     * Establece la relación con la dependencia
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dependency()
    {
        return $this->belongsTo(ProjectTrackingDependency::class);
    }

    /**
     * Establece la relación con el responsable
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
     * Establece la relación con el tipo de producto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function typeProduct()
    {
        return $this->belongsTo(ProjectTrackingTypeProducts::class);
    }

    /**
     * Método que obtiene la información de los tipos de productos asociados a un tipo de producto
     *
     * @author    Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function productTypes(): MorphToMany
    {
        return $this->morphToMany(ProjectTrackingTypeProducts::class, 'typeable');
    }

    /**
     * Establece la relación con el proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class);
    }

    /**
     * Establece la relación con el subproyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subProject()
    {
        return $this->belongsTo(ProjectTrackingSubProject::class, 'subproject_id');
    }

    /**
     * Establece la relación con el plan de actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'product_name');
    }

    /**
     * Establece la relación con el equipo asociado al plan de actividad
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlanTeam()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'id', 'employers_id');
    }

    /**
     * Retorna los identificadores de los tipos de productos asociados al producto  actual
     *
     * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
     *
     * @return array
     */
    public function getProductTypeIds(): array
    {
        return $this->productTypes->pluck('id')->toArray();
    }
}
