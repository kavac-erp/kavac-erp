<?php

/** [descripción del namespace] */

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Modules\Purchase\Models\PurchaseType;

/**
 * @class PurchaseDirectHire
 * @brief Modelo para la contratación directa
 *
 * Modelo para la contratación directa
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve | juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseDirectHire extends Model implements Auditable
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
        'code',
        'date',
        'institution_id',
        'contracting_department_id',
        'user_department_id',
        'fiscal_year_id',
        'purchase_supplier_id',
        'purchase_supplier_object_id',
        'currency_id',
        'funding_source',
        'description',
        'payment_methods',

        // variables para firmas
        'prepared_by_id',
        'reviewed_by_id',
        'verified_by_id',
        'first_signature_id',
        'second_signature_id',

        // Factura
        'receiver',

        //modlidad de compra
        'purchase_type_id',

        'due_date',
        'hiring_number',

        //estado de las orden de compra
        'status'
    ];

    protected $appends = ['receiver_json', 'status_pay_order'];

    public function getReceiverJsonAttribute()
    {
        return json_decode($this->receiver);
    }

    /**
     * Método para obtener el estatus de una orden de pago asciada a una orde de compra
     */
    public function getStatusPayOrderAttribute()
    {
        $status = "";

        $budgetEnabled = Module::has('Budget') && Module::isEnabled('Budget');
        $financeEnabled = Module::has('Finance') && Module::isEnabled('Finance');
        if ($budgetEnabled && $financeEnabled) {
            $find_compromise = \Modules\Budget\Models\BudgetCompromise::query()
            ->where([
                'sourceable_id' => $this->id,
                'sourceable_type' => PurchaseDirectHire::class,
                'document_number' => $this->code
            ])->latest()->first();

            if ($find_compromise) {
                $find_pay_order = \Modules\Finance\Models\FinancePayOrder::query()->where(
                    'document_sourceable_id',
                    $find_compromise->id
                )->latest()->first();
                if ($find_pay_order) {
                    $status = $find_pay_order->status_aux;
                }
            }
        }
        return $status;
    }

    /**
     * PurchaseDirectHire belongs to FiscalYear.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * PurchaseDirectHire belongs to Currency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    /**
     * PurchaseDirectHire belongs to Institution.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
    /**
     * PurchaseDirectHire belongs to Purchase_quatations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function quatations()
    {
        return $this->hasMany(PurchaseQuotation::class, "orderable_id");
    }

    /**
     * PurchaseBaseBudget morphs many PurchasePivotModelsToRequirementItem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatable()
    {
        return $this->morphMany(PurchasePivotModelsToRequirementItem::class, 'relatable');
    }

    /**
     * Obtiene todos los documentos asociados al proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * PurchaseDirectHire belongs to PurchaseSupplier.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplier()
    {
        return $this->belongsTo(PurchaseSupplier::class);
    }

    /**
     * PurchaseDirectHire belongs to PurchaseSupplierObject.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplierObject()
    {
        return $this->belongsTo(PurchaseSupplierObject::class);
    }

    /**
     * PurchaseDirectHire belongs to ContratingDepartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contratingDepartment()
    {
        return $this->belongsTo(Department::class, 'contracting_department_id');
    }

    /**
     * PurchaseDirectHire belongs to UserDepartment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userDepartment()
    {
        return $this->belongsTo(Department::class, 'user_department_id');
    }

    /**
     * PurchaseDirectHire morphs many PurchaseBaseBudget.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function purchaseBaseBudgets()
    {
        return $this->morphMany(PurchaseBaseBudget::class, 'orderable');
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'prepared_by_id'
                ) : null;
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'reviewed_by_id'
                ) : null;
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'verified_by_id'
                ) : null;
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firstSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'first_signature_id'
                ) : null;
    }

    /**
     * PurchaseDirectHire belongs to payroll_employment.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function secondSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'second_signature_id'
                ) : null;
    }

    /**
     * PurchaseDirectHire belongs to PurchaseType.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseType()
    {
        return $this->belongsTo(PurchaseType::class);
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->date;
    }
}
