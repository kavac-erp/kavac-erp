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
 * @class ProjectTrackingProject
 * @brief [descripción detallada]
 *
 * [descripción corta]
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
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
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

    protected $appends = ['currency'];

    public function getCurrencyAttribute()
    {
        return(\app\Models\Currency::find($this->currency_id));
    }

    public function responsable()
    {
        return(Module::has('Payroll') && Module::isEnabled('Payroll')) ?
                $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) :
                $this->belongsTo(ProjectTrackingPersonalRegister::class);
    }

    public function projectType()
    {
        return $this->belongsTo(ProjectTrackingProjectType::class);
    }

    public function typeProduct()
    {
        return $this->belongsTo(ProjectTrackingTypeProducts::class);
    }

    public function dependency()
    {
        return $this->belongsTo(ProjectTrackingDependency::class);
    }

    public function subprojects()
    {
        return $this->hasMany(ProjectTrackingSubProject::class, 'id', 'project_id');
    }

    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    public function tasks()
    {
        return $this->belongsToMany(ProjectTrackingTask::class);
    }

    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'project_name');
    }
}
