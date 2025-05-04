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
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBankAccount extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'opened_at'];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'ccc_number', 'description', 'opened_at', 'finance_banking_agency_id', 'finance_account_type_id', 'accounting_account_id', 'finance_bank_id'
    ];

    /**
     * Lista de campos personalizados a retornar en las consultas
     *
     * @var array $appends
     */
    protected $appends = ['formated_ccc_number'];

    /**
     * Obtiene el código de la cuenta cliente en formato del banco
     *
     * @return string
     */
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
     * Obtiene la relación con la agencia bancaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBankingAgency()
    {
        return $this->belongsTo(FinanceBankingAgency::class);
    }

    /**
     * Obtiene la relación con el tipo de cuenta
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeAccountType()
    {
        return $this->belongsTo(FinanceAccountType::class);
    }

    /**
     * Obtiene la relación con las chequeras
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeCheckBooks()
    {
        return $this->hasMany(FinanceCheckBook::class);
    }

    /**
     * Obtiene la relación con las ordenes de pago
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
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
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
