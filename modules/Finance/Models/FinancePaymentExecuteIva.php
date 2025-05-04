<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class FinancePaymentExecuteIva
 * @brief Modelo de datos para la ejecución de pagos de IVA
 *
 * Gestiona la informacion de la ejecución de pagos de IVA
 *
 * @author Ing. Francisco Escala <fescala@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentExecuteIva extends Model implements Auditable
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
        'code',
        'percentage',
        'finance_payment_execute_id',
        'finance_payment_deductions_id',
        'total_purchases_iva',
        'total_purchases_without_iva',
        'percentage_retained',
    ];

    /**
     * Obtiene la relación con el pago de deducciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePaymentDeductions()
    {
        return $this->belongsTo(FinancePaymentDeduction::class, 'finance_payment_deductions_id');
    }

    /**
     * Obtiene la relación con la ejecución de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePaymentExecute()
    {
        return $this->belongsTo(FinancePaymentExecute::class, 'finance_payment_execute_id');
    }
}
