<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class FinanceBank
 * @brief Datos de las entidades bancarias
 *
 * Gestiona el modelo de datos para las entidades bancarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class FinanceBank extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $with = ['logo'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $fillable = ['code', 'name', 'short_name', 'website', 'logo_id'];

    /**
     * FinanceBank has many Agencies.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeAgencies()
    {
        return $this->hasMany(FinanceBankingAgency::class);
    }

    /**
     * Método que obtiene el logotipo de la Entidad Bancaria
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return object Objeto con el registro relacionado al modelo Image
     */
    public function logo()
    {
        return $this->belongsTo(Image::class, 'logo_id');
    }

    /**
     * Get the payrollfinancial associated with payroll financial.
     */
    public function payrollFinancial()
    {
        return $this->hasMany(\Modules\Payroll\Models\PayrollFinancial::class);
    }

    /**
     * Método que obtiene todas las cuentas bancarias asociadas a un banco
     *
     * @author José Briceño <josejorgebriceno9@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function financeBankAccounts()
    {
        return $this->hasMany(FinanceBankAccount::class);
    }
}
