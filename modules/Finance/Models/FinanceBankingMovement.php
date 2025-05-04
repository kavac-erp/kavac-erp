<?php

namespace Modules\Finance\Models;

use App\Models\Currency;
use App\Models\Institution;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class FinanceBankingMovement
 * @brief Movimientos bancarios
 *
 * Gestiona el modelo de datos para los movimientos bancarios
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBankingMovement extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'payment_date'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'payment_date', 'transaction_type', 'reference', 'concept', 'amount',
        'finance_bank_account_id', 'currency_id', 'institution_id', 'code',
        'document_status_id'
    ];

    /**
     * Lista de relaciones cargadas por defecto
     *
     * @var array $with
     */
    protected $with = ['documentStatus'];

    /**
     * Lista de campos personalizados a retornar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['is_payment_executed'];

    /**
     * Método que obtiene la cuenta bancaria
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBankAccount()
    {
        return $this->belongsTo(FinanceBankAccount::class);
    }

    /**
     * Método que obtiene el asiento contable
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function accountingEntryPivot()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->morphOne(\Modules\Accounting\Models\AccountingEntryable::class, 'accounting_entryable') : [];
    }

    /**
     * Método que obtiene el compromiso
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function budgetCompromise()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->morphOne(\Modules\Budget\Models\BudgetCompromise::class, 'compromiseable') : [];
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
        return $this->belongsTo(Currency::class);
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
        return $this->belongsTo(Institution::class);
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
     * Obtiene la relación con las conciliaciones bancarias
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeConciliationBankMovements()
    {
        return $this->hasMany(FinanceConciliationBankMovement::class);
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->payment_date;
    }

    /**
     * Obtiene las siglas del tipo de transacción
     *
     * @author Ing. Roldan Vargas <rvargas at cenditel.gob.ve>
     *
     * @return string
     */
    public function getAcronymTransactionTypeAttribute()
    {
        $transactionTypes = [
            'Balance inicial' => 'BI',
            'Nota de crédito' => 'NC',
            'Transferencia o depósito' => 'TR',
            'Nota de débito' => 'ND'
        ];
        return $transactionTypes[$this->attributes['transaction_type']];
    }

    /**
     * Obtiene el valor de la propiedad isPaymentExecuted
     * para saber si el movimiento bancario proviene de una emisión de pago
     *
     * @return boolean
     */
    public function getIsPaymentExecutedAttribute()
    {
        //Obtenemos la el código de configuración del modelo FinancePaymentExecute
        $CodePaymentExecute = \App\Models\CodeSetting::query()
        ->where(
            "model",
            FinancePaymentExecute::class
        )->first();

        if (isset($CodePaymentExecute)) {
            /*
             | Regex para compararla con la referencia del movimiento bancario
             | y saber si vine de una emisión de pago
             */
            $pattern = "/^" . $CodePaymentExecute->format_prefix . "-\\d+-\\d+$/";
            if (preg_match($pattern, $this->reference)) {
                $paymentExecute = FinancePaymentExecute::query()
                ->where(
                    'code',
                    $this->reference
                )->first();

                if (isset($paymentExecute)) {
                    return true;
                }
            }
        }
        return false;
    }
}
