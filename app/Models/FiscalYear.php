<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Nwidart\Modules\Facades\Module;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @class FiscalYear
 *
 * @brief Datos de años fiscales
 *
 * Gestiona el modelo de datos para los años fiscales
 *
 * @property  string  $year
 * @property  boolean $active
 * @property  string  $observations
 * @property  array   $entries
 * @property  boolean $closed
 * @property  array   $resource_entries
 * @property  array   $egress_entries
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FiscalYear extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas.
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array $casts
     */
    protected $casts = ['entries' => 'array'];

    /**
     * Lista de atributos que pueden ser asignados masivamente.
     *
     * @var array $fillable
     */
    protected $fillable = ['year', 'active', 'observations'];

    /**
     * Obtiene la institución asociada a un año fiscal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Obtiene los asientos contables de ingresos del año fiscal actual.
     *
     * @return array
     */
    public function resourceEntries()
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            if (!isset($this->entries)) {
                return [];
            }

            $entries = [];

            $closeFiscalYearEntries = \Modules\Accounting\Models\AccountingEntryAccount::query()
                ->with(['entries', 'account'])
                ->whereHas('entries', function ($query) {
                    $query->where('id', $this->entries[0]);
                })
                ->get();

            foreach ($closeFiscalYearEntries as $entry) {
                if (!array_key_exists($entry['accounting_account_id'], $entries)) {
                    $entries[$entry['accounting_account_id']] = $entry;
                } else {
                    $entries[$entry['accounting_account_id']]['debit'] += $entry['debit'];
                    $entries[$entry['accounting_account_id']]['assets'] += $entry['assets'];
                }
            }

            return $entries;
        }
        return [];
    }

    /**
     * Obtiene los asientos contables de egresos del año fiscal actual.
     *
     * @return array
     */
    public function egressEntries()
    {
        if (Module::has('Accounting') && Module::isEnabled('Accounting')) {
            if (!isset($this->entries)) {
                return [];
            }

            $entries = [];

            $closeFiscalYearEntries = \Modules\Accounting\Models\AccountingEntryAccount::query()
                ->with(['entries', 'account'])
                ->whereHas('entries', function ($query) {
                    $query->where('id', $this->entries[1]);
                })
                ->get();

            foreach ($closeFiscalYearEntries as $entry) {
                if (!array_key_exists($entry['accounting_account_id'], $entries)) {
                    $entries[$entry['accounting_account_id']] = $entry;
                } else {
                    $entries[$entry['accounting_account_id']]['debit'] += $entry['debit'];
                    $entries[$entry['accounting_account_id']]['assets'] += $entry['assets'];
                }
            }

            return $entries;
        }
        return [];
    }
}
