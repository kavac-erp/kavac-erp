<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\DB;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Modules\Purchase\Models\PurchaseType;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchaseDirectHire
 * @brief Modelo para la contratación directa
 *
 * Modelo para la contratación directa
 *
 * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
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

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['receiver_json', 'status_pay_order'];

    /**
     * Obtiene los datos del receptor de la orden de compra
     *
     * @return mixed
     */
    public function getReceiverJsonAttribute()
    {
        return json_decode($this->receiver);
    }

    /**
     * Método para obtener el estatus de una orden de pago asciada a una orde de compra
     *
     * @return string
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
     * Establece la relación con el año fiscal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Establece la relación con la moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con la institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Establece la relación con las cotizaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function quatations()
    {
        return $this->hasMany(PurchaseQuotation::class, "orderable_id");
    }

    /**
     * Establece la relación con los elementos del requerimiento de compra
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
     * Establece la relación con el proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplier()
    {
        return $this->belongsTo(PurchaseSupplier::class);
    }

    /**
     * Establece la relación con el objeto del proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplierObject()
    {
        return $this->belongsTo(PurchaseSupplierObject::class);
    }

    /**
     * Establece la relación con el departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contratingDepartment()
    {
        return $this->belongsTo(Department::class, 'contracting_department_id');
    }

    /**
     * Establece la relacion con el usuario de un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userDepartment()
    {
        return $this->belongsTo(Department::class, 'user_department_id');
    }

    /**
     * Establece la relación morfológica con los presupuestos base
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function purchaseBaseBudgets()
    {
        return $this->morphMany(PurchaseBaseBudget::class, 'orderable');
    }

    /**
     * Establece la relación con el usuario que preparó la orden de compra
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
     * Establece la relación con el usuario que revisó la orden de compra
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
     * Establece la relación con el usuario que verificó la orden de compra
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
     * Establece la relación con el usuario que firmó, en primer lugar, la orden de compra
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
     * Establece la relación con el usuario que firmó, en segundo lugar, la orden de compra
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
     * Establece la relación con el tipo de compra
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

    /**
     * Scope para buscar y filtrar datos de ordenes de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where(DB::raw('upper(code)'), 'LIKE', '%' . strtoupper($search) . '%')
            ->orWhereRaw("TO_CHAR(date, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'");
    }
}
