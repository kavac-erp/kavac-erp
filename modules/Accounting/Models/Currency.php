<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Currency as BaseCurrency;

/**
 * @class Currency
 * @brief Clase que extiende la clase Currency de la aplicación base
 *
 * Gestiona la clase que extiende la clase Currency de la aplicación base
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Currency extends BaseCurrency
{
    /**
     * Establece la relación con el asiento contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function accountingEntry()
    {
        return $this->hasOne(AccountingEntry::class);
    }

    /**
     * Establece la relación con el historial de reportes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingReportHistory()
    {
        return (
            Model::has('Accounting') && Model::isEnabled('Accounting')
        ) ? $this->hasMany(AccountingReportHistory::class) : [];
    }
}
