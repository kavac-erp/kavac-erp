<?php

namespace Modules\Finance\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class FinanceBank
 * @brief Datos de las cuentas bancarias
 *
 * Gestiona el modelo de datos para las cuentas bancarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceBankAccount extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'opened_at'];

    protected $fillable = [
        'ccc_number', 'description', 'opened_at', 'finance_banking_agency_id', 'finance_account_type_id', 'accounting_account_id', 'finance_bank_id'
    ];

    protected $appends = ['formated_ccc_number'];

    public function getFormatedCccNumberAttribute()
    {
        if (empty($this->ccc_number)) {
            return '';
        }

        $newCccNumber = '';
        for ($i = 0; $i < strlen($this->ccc_number); $i++) {
            $newCccNumber .= $this->ccc_number[$i];
            if (in_array($i, [3, 7, 9])) {
                $newCccNumber .= "-";
            }
        }
        return $newCccNumber;
    }

    /**
     * FinanceBankAccount belongs to FinanceBankingAgency.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBankingAgency()
    {
        return $this->belongsTo(FinanceBankingAgency::class);
    }

    /**
     * FinanceBankAccount belongs to FinanceAccountType.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeAccountType()
    {
        return $this->belongsTo(FinanceAccountType::class);
    }

    /**
     * FinanceBankAccount has many FinanceCheckBooks.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeCheckBooks()
    {
        return $this->hasMany(FinanceCheckBook::class);
    }

    /**
     * Get all of the financePayOrders for the FinanceBankAccount
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financePayOrders()
    {
        return $this->hasMany(FinancePayOrder::class);
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada al concepto
     *
     * @author    Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (Module::has('Accounting'))
            ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }

    /**
     * Método que obtiene el banco al que pertenece la cuenta bancaria
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function bank()
    {
        return $this->belongsTo(FinanceBank::class, 'finance_bank_id', 'id');
    }
}
