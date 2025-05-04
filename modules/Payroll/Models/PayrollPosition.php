<?php

namespace Modules\Payroll\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @class Position
 * @brief Datos de cargos
 *
 * Gestiona el modelo de datos para los cargos
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPosition extends Model implements Auditable
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
        'description',
        'number_positions_assigned',
        'responsible'
    ];

    /**
     * Obtiene la relación con la responsabilidad
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollResponsibility(): HasOne
    {
        return $this->hasOne(PayrollResponsibility::class);
    }

    /**
     * Obtiene la relación con proyectos de presupuesto si el módulo de presupuesto esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetProjects()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetProject::class) : [];
    }

    /**
     * Obtiene la acción centralizada de presupuesto si el módulo de presupuesto esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCentralizedActions()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->hasMany(\Modules\Budget\Models\BudgetCentralizedAction::class) : [];
    }

    /**
     * Método que establece una relación de "muchos a muchos" (belongsToMany)
     * entre el modelo actual y el modelo PayrollEmployment.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function payrollEmployments()
    {
        return $this->belongsToMany(PayrollEmployment::class, 'payroll_employment_payroll_position');
    }

    /**
     * Método que obtiene los requerimientos de las escalas asociados a un cargo
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>

     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollScaleRequirements()
    {
        return $this->morphMany(PayrollScale::class, 'clasificable');
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un cargo
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'assignable');
    }
}
