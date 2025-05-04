<?php

/** [descripción del namespace] */

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class FinancePayOrderFinancePaymentExecute
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePayOrderFinancePaymentExecute extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = 'finance_pay_order_finance_payment_execute';

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'finance_pay_order_id',
        'finance_payment_execute_id'
    ];

    /**
     * Get the financePayOrder that owns the FinancePayOrderFinancePaymentExecute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePayOrder()
    {
        return $this->belongsTo(FinancePayOrder::class);
    }

    /**
     * Get the financePaymentExecute that owns the FinancePayOrderFinancePaymentExecute
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePaymentExecute()
    {
        return $this->belongsTo(FinancePaymentExecute::class);
    }
}
