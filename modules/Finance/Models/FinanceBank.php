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
 * @brief Datos de las entidades bancarias
 *
 * Gestiona el modelo de datos para las entidades bancarias
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinanceBank extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de relaciones cargadas por defecto
     *
     * @var array $with
     */
    protected $with = ['logo'];

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
    protected $fillable = ['code', 'name', 'short_name', 'website', 'logo_id'];

    /**
     * Obtiene la relación con las agencias bancarias
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
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
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function logo()
    {
        return $this->belongsTo(Image::class, 'logo_id');
    }

    /**
     * Obtiene la opción con los datos financieros del trabajador en el módulo de Talento Humano si esta presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollFinancial()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->hasMany(\Modules\Payroll\Models\PayrollFinancial::class) : [];
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
