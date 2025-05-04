<?php

namespace Modules\Accounting\Models;

use App\Models\Institution as BaseInstitution;

/**
 * @class Institution
 * @brief Clase que extiende la clase Institution de la aplicación base
 *
 * Gestiona la clase que extiende la clase Institution de la aplicación base
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Institution extends BaseInstitution
{
    /**
     * Establece la relación con las entradas contables
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accoutingEntry()
    {
        return $this->hasMany(AccountingEntry::class);
    }

    /**
     * Establece la relación con el historial de reportes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingReportHistory()
    {
        return $this->hasMany(AccountingReportHistory::class);
    }
}
