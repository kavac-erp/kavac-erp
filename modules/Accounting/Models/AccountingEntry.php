<?php

namespace Modules\Accounting\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Module;

class AccountingEntry extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

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
        'reversed_id'
    ];

    protected $with = ['currency'];

    /**
     * AccountingEntry has many AccountingAccounts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingAccounts()
    {
        // hasMany(RelatedModel, foreignKeyOnRelatedModel = accountingEntry_id, localKey = id)
        return $this->hasMany(AccountingEntryAccount::class, 'accounting_entry_id');
    }

    /**
     * AccountingEntry has many AccountingEntryCategory.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function accountingEntryCategory()
    {
        return $this->belongsTo(AccountingEntryCategory::class, 'accounting_entry_category_id');
    }

    /**
     * Get the accountingEntryable that owns the AccountingEntry
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
     * @author  Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
     * @return boolena
     */
    public function approved()
    {
        return ($this->approved);
    }

    /**
     * AccountingEntry belongs to Currency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        // belongsTo(RelatedModel, foreignKey = curr_id, keyOnRelatedModel = id)
        return $this->belongsTo(Currency::class);
    }
    /**
     * AccountingEntry belongs to Institution.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        // belongsTo(RelatedModel, foreignKey = institution_id, keyOnRelatedModel = id)
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
        // hasMany(RelatedModel, foreignKeyOnRelatedModel = accountingEntry_id, localKey = id)
        return $this->hasMany(AccountingEntryable::class, 'accounting_entry_id');
    }

    /**
     * Get the documentStatus that owns the FinancePayOrder
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }

    /**
     * Query scope Column.
     *
     * @param
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  String $column [nombre de la columna en la que se desea buscar]
     * @param  String $search [texto que se buscara]
     * @return \Illuminate\Database\Eloquent\Builder
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
     * valida que el institution_id del usuario corresponda al del registro
     * En caso de que el usuario tenga institution_id igual a null se entiende que es el administrador global
     *
     * @param  Integer $id Identificador de la institucion del usuario
     * @return \Illuminate\Database\Eloquent\Builder
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
     * @return Date
     */
    public function getDate()
    {
        return $this->from_date;
    }

    /**
     * Get the AccountingEntry Reversed associated with the Accountingentry
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function entryReversed()
    {
        return $this->hasOne(AccountingEntry::class, 'reversed_id');
    }

    /**
     * Get the original Accounting Entry that belongs to reverse entry
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entryOrigin()
    {
        return $this->belongsTo(AccountingEntry::class, 'reversed_id');
    }
}
