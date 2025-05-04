<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class      PayrollPaymentType
 * @brief      Datos de tipos de pago
 *
 * Gestiona el modelo de tipos de pago
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPaymentType extends Model implements Auditable
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
        'code', 'name', 'payment_periodicity', 'correlative', 'start_date',
        'payment_relationship', 'associated_records', 'finance_bank_account_id', 'accounting_account_id',
        'order', 'individual', 'accounting_entry_category_id', 'finance_payment_method_id', 'receipt', 'skip_moments'
    ];

    /**
     * Lista de relaciones a cargar con el modelo
     *
     * @var array $with
     */
    protected $with = ['financeBankAccount'];

    /**
     * Método que obtiene los conceptos asociados a muchos tipos de pago de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function payrollConcepts()
    {
        return $this->belongsToMany(
            PayrollConcept::class,
            'payroll_concept_payment_type',
            'payroll_payment_type_id',
            'payroll_concept_id'
        );
    }

    /**
     * Método que obtiene la información de los períodos asociados al tipo de pago de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollPaymentPeriods()
    {
        return $this->hasMany(PayrollPaymentPeriod::class);
    }

    /**
     * Método que obtiene la información de las políticas de prestaciones asociadas al tipo de pago de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollBenefitsPolicies()
    {
        return $this->hasMany(PayrollBenefitsPolicy::class);
    }

    /**
     * Método que obtiene la información de las políticas vacacionales asociadas al tipo de pago de nómina
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollVacationPolicies()
    {
        return $this->hasMany(PayrollVacationPolicy::class);
    }

    /**
     * Método que obtiene la información de los parámetros de hoja de tiempo que se emplean en el tipo de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function payrollTimeSheetParameters()
    {
        return $this->belongsToMany(PayrollTimeSheetParameter::class, 'payroll_payment_type_time_sheet_parameters');
    }

    /**
     * Método que obtiene la información de las cuentas contables asociadas a un tipo de pago
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }

    /**
     * Método que obtiene la información de las cuentas contables asociadas a un tipo de pago
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function financeBankAccount()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->belongsTo(\Modules\Finance\Models\FinanceBankAccount::class) : null;
    }

    /**
     * Método que obtiene la información de la categoria de cuenta contables asociadas a un tipo de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function accountingEntryCategory()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingEntryCategory::class) : null;
    }

    /**
     * Método que obtiene la información del metodo de pago asociadas a un tipo de pago
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function financePaymentMethod()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->belongsTo(\Modules\Finance\Models\FinancePaymentMethods::class, 'finance_payment_method_id') : null;
    }
}
