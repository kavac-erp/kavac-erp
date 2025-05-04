<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class AccountingReportHistory
 * @brief Clase que gestiona el reporte de las cuentas contables
 *
 * Gestiona el reporte de las cuentas contables
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingReportHistory extends Model
{
    /**
     * Fields that can be mass assigned.
     *
     * @var array $fillable
     */
    protected $fillable = ['report','url','currency_id','institution_id'];

    /**
     * Establece la relación con la moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con la institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Verifica el acceso al reporte
     *
     * @param integer|string $id
     *
     * @return boolean
     */
    public function queryAccess($id)
    {
        if ($id != $this->institution_id && !auth()->user()->isAdmin()) {
            return true;
        }
        return false;
    }
}
