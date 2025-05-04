<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollHistorySalaryAdjustment
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollHistorySalaryAdjustment extends Model implements Auditable
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
        'increase_of_date', 'end_increase_date', 'salary_values', 'payroll_salary_adjustment_id'
    ];

    /**
     * Método que obtiene el ajuste en tablas salariales asociado
     *
     * @author  Fabian Palmera <fpalmera@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSalaryAdjustment()
    {
        return $this->belongsTo(PayrollSalaryAdjustment::class);
    }
}
