<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use App\Models\Currency;
use App\Models\Institution;

/**
 * @class FinanceConciliation
 * @brief Modelo de conciliaciones bancarias
 *
 * Gestiona el modelo de datos para las conciliaciones bancarias
 *
 * @author Juan Rosas <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceConciliation extends Model implements Auditable
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
        'code',
        'finance_bank_account_id',
        'start_date',
        'end_date',
        'institution_id',
        'currency_id',
        'document_status_id',
        'bank_balance',
        'system_balance'
    ];

    /**
     * Lista de relaciones cargadas por defecto
     *
     * @var array $with
     */
    protected $with = ['documentStatus'];

    /**
     * Obtiene la relación con la cuenta bancaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBankAccount()
    {
        return $this->belongsTo(
            FinanceBankAccount::class,
            'finance_bank_account_id'
        );
    }

    /**
     * Obtiene la relación con el estatus del documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }

    /**
     * Método que obtiene el tipo de moneda
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    /**
     * Método que obtiene la institución
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }

    /**
     * Obtiene la relación con los movimientos de conciliación bancaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeConciliationBankMovements()
    {
        return $this->hasMany(FinanceConciliationBankMovement::class);
    }
}
