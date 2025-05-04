<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;

/**
 * @class PurchaseRequirementItem
 * @brief Datos de los productos o servicios en los requerimientos de compras
 *
 * Gestiona el modelo de datos para los productos o servicios en los requerimientos de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class PurchaseRequirementItem extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    protected $appends = ['unit_price','Quoted'];

    protected $with = ['purchaseProduct'];

    protected $fillable = [
        'name',
        'description',
        'technical_specifications',
        'quantity',
        'warehouse_product_id',
        'purchase_product_id',
        'history_tax_id',
        'measurement_unit_id',
        'purchase_requirement_id'
    ];

    /**
     * PurchaseRequirementItem belongs to PurchaseRequirement.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseRequirement()
    {
        return $this->belongsTo(PurchaseRequirement::class);
    }
    /**
     * PurchaseRequirementItem belongs to WarehouseProduct.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseProduct()
    {
        // belongsTo(RelatedModel, foreignKey = warehouse_product_id, keyOnRelatedModel = id)
        return $this->belongsTo('Modules\Warehouse\Models\WarehouseProduct', 'warehouse_product_id');
    }

    /**
     * PurchaseRequirementItem belongs to PurchaseProduct.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseProduct()
    {
        return $this->belongsTo(PurchaseProduct::class);
    }

    /**
     * PurchaseRequirementItem belongs to HistoryTax.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historyTax()
    {
        return $this->belongsTo(HistoryTax::class);
    }

    /**
     * PurchaseRequirementItem belongs to MeasurementUnit.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    /**
     * PurchaseRequirementItem has one PurchasePivotModelsToRequirementItem.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function pivotPurchase()
    {
        // hasOne(RelatedModel, foreignKeyOnRelatedModel = purchaseRequirementItem_id, localKey = id)
        return $this->hasMany(PurchasePivotModelsToRequirementItem::class);
    }

     /**
     *
     *
     *
     *
     * @author Francisco Escala <Fjescala@gmail.com> >
     *
     * @return     string|null  Devuelve la informacion de cotizacion relacionada a item
     */
    public function getQuotedAttribute()
    {
        $r = PurchasePivotModelsToRequirementItem::where("relatable_type", "Modules\Purchase\Models\PurchaseQuotation")
        ->where('purchase_requirement_item_id', $this->id)->first();
        return $r;
    }
    /**
     * Obtiene el nombre de un Pais
     *
     * @method     getCountryNameAttribute
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     string|null  Devuelve el nombre de un pais si esta definido, de lo contrario devuelve nulo
     */
    public function getUnitPriceAttribute(): ?string
    {
        $r = PurchasePivotModelsToRequirementItem::where('purchase_requirement_item_id', $this->id)->first();
        return $r
                ? $r->unit_price
                : null;
    }
}
