<?php

namespace Modules\Accounting\Models;

use Module;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AccountingEntry
 * @brief Datos del asiento contable
 *
 * Gestiona los datos del asiento contable
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AccountingEntry extends Model implements Auditable
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
        'from_date',
        'concept',
        'observations',
        'reference',
        'tot_debit',
        'tot_assets',
        'accounting_entry_category_id',
        'institution_id',
        'currency_id',
        'approved',
        'document_status_id',
        'reversed',
        'reversed_id',
        'reversed_at'
    ];

    /**
     * Carga relaciones de modelos
     *
     * @var array $with
     */
    protected $with = ['currency'];

    /**
     * Establece la relación con la cuenta contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingAccounts()
    {
        return $this->hasMany(AccountingEntryAccount::class, 'accounting_entry_id');
    }

    /**
     * Establece la relación con la categoría de asiento contable
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingEntryCategory()
    {
        return $this->belongsTo(AccountingEntryCategory::class, 'accounting_entry_category_id');
    }

    /**
     * Establece la relación con el modelo AccountingEntryable
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingEntryable()
    {
        return $this->belongsTo(AccountingEntryable::class);
    }


    /**
     * Indica si el asiento contable esta aprobado
     *
     * @author  Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return boolean
     */
    public function approved()
    {
        return ($this->approved);
    }

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
     * Método que obtiene los modelos morfológicos asociados a asientos contables
     *
     * @author    Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotEntryable()
    {
        return $this->hasMany(AccountingEntryable::class, 'accounting_entry_id');
    }

    /**
     * Establece la relación con el estatus del documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }

    /**
     * Scope de column
     *
     * @param
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  string $column nombre de la columna en la que se desea buscar
     * @param  string $search texto que se buscara
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function scopeColumn($query, $column, $search)
    {
        if ($column && $search === '') {
            return $query;
        } elseif ($column && $search) {
            return $query->orWhere($column, 'LIKE', "%$search%");
        }
    }

    /**
     * Valida que el institution_id del usuario corresponda al del registro.
     * En caso de que el usuario tenga institution_id igual a null se entiende que es el administrador global
     *
     * @param  integer $id Identificador de la institucion del usuario
     *
     * @return boolean
     */
    public function queryAccess($id)
    {
        if ($id != $this->institution_id && auth()->user()->institution_id != null) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date|string
     */
    public function getDate()
    {
        return $this->from_date;
    }

    /**
     * Establece la relación con el asiento contable de reverso
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entryReversed()
    {
        return $this->hasOne(AccountingEntry::class, 'reversed_id');
    }

    /**
     * Establece la relación con el asiento contable original
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryOrigin()
    {
        return $this->belongsTo(AccountingEntry::class, 'reversed_id');
    }
}
