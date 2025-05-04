<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Payroll\Models\PayrollStaff;
use Modules\Finance\Models\FinanceBank;
use Modules\Finance\Models\FinanceAccountType;

/**
 * @class PayrollFinancial
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFinancial extends Model implements Auditable
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
    protected $fillable = ['payroll_staff_id', 'finance_bank_id', 'finance_account_type_id', 'payroll_account_number'];

    protected $with = ['financeAccountType'];

    /**
     * Método que obtiene el trabajador al que se le asigna información financiera
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * PayrollStaff
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Método que obtiene el banco asignado a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * FinanceBank
     */
    public function financeBank()
    {
        return $this->belongsTo(\Modules\Finance\Models\FinanceBank::class);
    }

    /**
     * Método que obtiene el tipo de cuenta asignado a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * FinanceBank
     */
    public function financeAccountType()
    {
        return $this->belongsTo(FinanceAccountType::class);
    }
}
