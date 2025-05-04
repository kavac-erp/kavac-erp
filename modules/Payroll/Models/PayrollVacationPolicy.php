<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class      PayrollVacationPolicy
 * @brief      Datos de las políticas vacacionales
 *
 * Gestiona el modelo de datos de las políticas vacacionales
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @author     Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollVacationPolicy extends Model implements Auditable
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
        'name', 'start_date', 'end_date', 'vacation_periods', 'vacation_type', 'active',
        'vacation_periods_accumulated_per_year', 'vacation_days', 'vacation_period_per_year',
        'additional_days_per_year', 'minimum_additional_days_per_year', 'maximum_additional_days_per_year',
        'payment_calculation', 'salary_type', 'vacation_advance', 'vacation_postpone', 'staff_antiquity',
        'institution_id', 'payroll_payment_type_id', 'assign_to',
        'on_scale',
        'worker_arises',
        'generate_worker_arises',
        'min_days_advance',
        'max_days_advance',
        'old_jobs',
        'vacation_pay_days',
        'business_days',
        'from_year',
        'years_for_additional_days',

        // Usados por la funcionalidad de Agrupar por:
        'group_by', 'type',

        'days_on_scale', 'days_group_by', 'days_type',
    ];

    /**
     * Lista de atributos de relacion consultados automaticamente
     *
     * @var    array    $with
     */
    protected $with = ['institution'];

    /**
     * Método que obtiene la información de la institución asociada a la política vacacional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene la información del tipo de pago asociado a la política vacacional
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPaymentType()
    {
        return $this->belongsTo(PayrollPaymentType::class);
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un género
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'applicable');
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un género
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function assignToOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'applicable');
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a Los días de bonificación se establecen de acuerdo a un escalafón
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollScales()
    {
        return $this->morphMany(PayrollScale::class, 'relationable');
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a Los días de disfrute se establecen de acuerdo a un escalafón
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollDaysScales()
    {
        return $this->morphMany(PayrollScale::class, 'relationable');
    }
}
