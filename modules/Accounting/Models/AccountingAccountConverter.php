<?php

namespace Modules\Accounting\Models;

use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AccountingAccountConverter
 * @brief Datos del convertidor de cuentas
 *
 * Modelo de la tabla pivot entre budget_account y accounting_account
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingAccountConverter extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'accounting_account_id',
        'budget_account_id',
        'active'
    ];

    /**
     * Establece la relación con las cuentas del clasificador presupuestario si el módulo de Presupuesto está presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetAccount::class) : [];
    }

    /**
     * Establece la relación con las cuentas patrimoniales
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }
}
