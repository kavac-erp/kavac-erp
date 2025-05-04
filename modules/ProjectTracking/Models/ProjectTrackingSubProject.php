<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class Priority
 * @brief Datos de subproyecto
 *
 * Gestiona el modelo de datos para las prioridades
 *
 * @author Pedro Contreras <pdrocont@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class ProjectTrackingSubProject extends Model implements Auditable
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
     *
     * @var array $fillable
     */
    protected $fillable = [
        'project_id', 'name', 'description', 'code', 'responsable_id', 'start_date', 'end_date', 'financement_amount', 'currency_id'
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

    public function project()
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'project_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'subproject_name');
    }
}
