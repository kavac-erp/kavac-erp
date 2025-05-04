<?php

namespace Modules\ProjectTracking\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
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
        'project_id', 'name', 'description', 'code', 'responsable_id', 'start_date', 'end_date', 'financement_amount', 'currency_id'
    ];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     * @var array $appends
     */
    protected $appends = ['currency'];

    /**
     * Obtiene la información de la moneda
     *
     * @return \App\Models\Currency[]|null
     */
    public function getCurrencyAttribute()
    {
        return (\app\Models\Currency::find($this->currency_id));
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
     * Establece la relación con el proyecto
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectTrackingProject::class, 'project_id', 'id');
    }

    /**
     * Establece la relación con los tipos de productos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function productTypes(): MorphToMany
    {
        return $this->morphToMany(ProjectTrackingTypeProducts::class, 'typeable');
    }

    /**
     * Establece la relación con productos

     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function products()
    {
        return $this->belongsToMany(ProjectTrackingProduct::class);
    }

    /**
     * Establece la relación con el plan de actividades
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function activityPlan()
    {
        return $this->belongsTo(ProjectTrackingActivityPlan::class, 'id', 'subproject_name');
    }

    /**
     * Retorna los identificadores de los tipos de productos asociados al subproyecto actual
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
