<?php

namespace Modules\Accounting\Models;

use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AccountingEntryAccount
 * @brief Clase que gestiona las cuentas del asiento contable
 *
 * Gestiona las cuentas del asiento contable
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntryAccount extends Model implements Auditable
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
     * Lista de campos agregados a los resultados de las consultas
     *
     * @var array $appends
     */
    protected $appends = ['amount'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'accounting_entry_id',
        'accounting_account_id',
        'bank_reference',
        'debit',
        'assets'
    ];

    /**
     * Obtiene el monto del asiento
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return float
     */
    public function getAmountAttribute()
    {
        return $this->debit != 0 ? $this->debit : $this->assets;
    }

    /**
     * Establece la relación con el asiento contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entries()
    {
        return $this->belongsTo(AccountingEntry::class, 'accounting_entry_id');
    }

    /**
     * Establece la relacion con la cuenta contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    /**
     * Establece la relacion con el movimiento de la conciliación bancaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function financeConciliationBankMovement()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->hasOne(\Modules\Finance\Models\FinanceConciliationBankMovement::class) : [];
    }
}
