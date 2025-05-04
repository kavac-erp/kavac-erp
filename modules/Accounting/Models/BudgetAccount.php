<?php

namespace Modules\Accounting\Models;

use Modules\Budget\Models\BudgetAccount as BaseBudgetAccount;

/**
 * @class BudgatAccount
 * @brief Datos de cuentas del Clasificador Presupuestario
 *
 * Gestiona el modelo de datos para las cuentas del Clasificador Presupuestales desde el modulo de contabilidad
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAccount extends BaseBudgetAccount
{
    /**
     * Establece la relación con las cuentas contables
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function accountable()
    {
        return $this->morphMany(Accountable::class, 'accountable');
    }

    /**
     * Establece la relacion con las entradas contables
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function accountingEntryable()
    {
        return $this->morphMany(AccountingEntryable::class, 'accounting_entryable');
    }
}
