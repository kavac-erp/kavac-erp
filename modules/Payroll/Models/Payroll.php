<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class      Payroll
 * @brief      Datos de registros de nómina
 *
 * Gestiona el modelo de registros de nómina
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Payroll extends Model implements Auditable
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
    protected $fillable = ['code','status',  'name', 'payroll_parameters', 'payroll_payment_period_id', 'salary_tabulators', 'concept_types'];

    /**
     * Lista de atributos de relacion consultados automáticamente
     *
     * @var array $with
     */
    protected $with = ['payrollPaymentPeriod'];

    /**
     * Lista de atributos con el tipo de dato a retornar
     *
     * @var array
     */
    protected $casts = [
        'salary_tabulators' => 'array',
        'concept_types' => 'array',
    ];

    /**
     * Método que obtiene la información del período de pago asociado a la nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPaymentPeriod()
    {
        return $this->belongsTo(PayrollPaymentPeriod::class);
    }

    /**
     * Método que obtiene la información de los trabajadores asociados a la nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffPayrolls()
    {
        return $this->hasMany(PayrollStaffPayroll::class);
    }
}
