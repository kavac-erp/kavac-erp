<?php

namespace Modules\Budget\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetCentralizedAction
 * @brief Datos de Acciones Centralizadas
 *
 * Gestiona el modelo de datos para las Acciones Centralizadas
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetCentralizedAction extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista con campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name', 'code', 'custom_date', 'active', 'ca_description', 'from_date', 'to_date',
        'department_id', 'payroll_position_id', 'payroll_staff_id'
    ];

    /**
     * Crea un campo para obtener datos de la acción centralizada
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return string Devuelve el código y nombre de la acción centralizada
     */
    public function getDescriptionAttribute()
    {
        return "{$this->code} - {$this->name}";
    }

    /**
     * Establece la relación con el departamento de la institución
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Establece la relación con el cargo establecido en el expediente de talento humano
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPosition()
    {
        /** OJO: Independizar esta relación para que exista un módulo sin el otro */
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollPosition::class) : [];
    }

    /**
     * Establece la relacion con el personal del modulo de talento humano
     *
     * @return array|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        /** OJO: Independizar esta relación para que exista un módulo sin el otro */
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : [];
    }

    /**
     * Obtiene la relación con las acciones especificas
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function specificActions()
    {
        return $this->morphMany(BudgetSpecificAction::class, 'specificable');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->from_date;
    }
}
