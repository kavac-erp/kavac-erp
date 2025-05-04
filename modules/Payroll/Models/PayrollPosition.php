<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

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
     * PayrollPosition has many BudgetProjects.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetProjects()
    {
        return (Module::has('Budget')) ? $this->hasMany(\Modules\Budget\Models\BudgetProject::class) : [];
    }

    /**
     * PayrollPosition has many BudgetCentralizedAction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCentralizedActions()
    {
        return (Module::has('Budget')) ? $this->hasMany(\Modules\Budget\Models\BudgetCentralizedAction::class) : [];
    }

    /**
     * Método que obtiene el cargo asociado a muchos datos laborales
     *
     * @author William Páez <wpaez@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    /*
    public function payrollEmployments()
    {
        return $this->hasMany(PayrollEmployment::class);
    }
    */

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
