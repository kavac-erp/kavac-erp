<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class FinanceConciliationBankMovement
 * @brief Modelo de conciliaciones bancarias
 *
 * Gestiona el modelo de datos para las conciliaciones bancarias
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceConciliationBankMovement extends Model implements Auditable
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
        'accounting_entry_account_id',
        'finance_conciliation_id',
        'concept',
        'debit',
        'assets',
        'current_balance'
    ];

    /**
     * Obtiene la relación con la conciliación bancaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeConciliation()
    {
        return $this->belongsTo(FinanceConciliation::class, 'finance_conciliation_id');
    }

    /**
     * Obtiene la relación con el asiento contable del módulo de contabilidad si esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingEntryAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingEntryAccount::class, 'accounting_entry_account_id') : null;
    }
}
