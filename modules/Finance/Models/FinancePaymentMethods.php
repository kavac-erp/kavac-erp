<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class FinancePaymentMethods
 * @brief Modelo de datos para los métodos de pago
 *
 * Gestiona el modelo de datos para los métodos de pago
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @author Ing. Marco Ocanto
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentMethods extends Model implements Auditable
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
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = ['name','description'];

    /**
     * Obtiene la relación con las cuentas bancarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function bankAccounts()
    {
        return $this->hasMany(FinanceBankAccount::class);
    }

    /**
     * Obtiene la relación con las órdenes de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financePayOrders()
    {
        return $this->hasMany(FinancePayOrder::class, 'finance_payment_method_id');
    }
}
