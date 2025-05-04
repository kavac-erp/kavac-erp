<?php

/** [descripción del namespace] */

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class ProjectTrackingProduct
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [Oscar González] <xxmaestroyixx@gmail.com/ojgonzalez@cenditel.gob.ve>
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
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
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

    public function dependency()
    {
        return $this->belongsTo(ProjectTrackingDependency::class);
    }

    public function responsable()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
               ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class)
               : $this->belongsTo(ProjectTrackingPersonalRegister::class);
    }

    public function typeProduct()
    {
        return $this->belongsTo(ProjectTrackingTypeProducts::class);
    }

    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class);
    }

    public function subProject()
    {
        return $this->belongsTo(ProjectTrackingSubProject::class, 'subproject_id');
    }

    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'product_name');
    }

    public function activityPlanTeam()
    {
        return $this->belongsTo(ProjectTrackingActivityPlanTeam::class, 'id', 'employers_id');
    }
}
