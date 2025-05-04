<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Purchase\Models\PurchaseBudgetaryAvailability;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

class PurchaseQuotation extends Model implements Auditable
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
        'status',
        'purchase_supplier_id',
        'purchase_base_budget_id',
        'currency_id',
        'subtotal',
        'date',
        'orderable_type',
        'orderable_id'
    ];

    protected $appends = ['status_purchase_order'];

    public function getStatusPurchaseOrderAttribute()
    {
        $purchase_order = $this->purchaseDirectHire()->first();
        return $purchase_order ? $purchase_order->status : "";
    }

    /**
     * PurchaseOrder belongs to PurchaseSupplier.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplier()
    {
        return $this->belongsTo(PurchaseSupplier::class);
    }

    /**
     * PurchaseOrder belongs to Currency.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * PurchaseDirectHire belongs to Purchase_quatations.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseDirectHire()
    {
        return $this->belongsTo(PurchaseDirectHire::class, "orderable_id");
    }
    /**
     * PurchaseQuotation has many Pivot.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotRecordable()
    {
        return $this->hasMany(Pivot::class, 'recordable_id');
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
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->date;
    }
}
