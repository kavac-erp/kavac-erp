<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Purchase\Models\PurchasePivotModelsToRequirementItem;

/**
 * @class PurchaseRequirementItem
 * @brief Datos de los productos o servicios en los requerimientos de compras
 *
 * Gestiona el modelo de datos para los productos o servicios en los requerimientos de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseRequirementItem extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $appends
     */
    protected $appends = ['unit_price','Quoted'];

    /**
     * Lista de relaciones a cargar por defecto con el modelo
     *
     * @var array $with
     */
    protected $with = ['purchaseProduct'];

    /**
     * Lista de atributos del modelo
     *
     * @var array $fillable
     */
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
     * Establece la relación con los requerimientos de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseRequirement()
    {
        return $this->belongsTo(PurchaseRequirement::class);
    }

    /**
     * Establece la relación con los productos de almacén si el módulo de almacén está presente
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseProduct()
    {
        return (
            Module::has('Warehouse') && Module::isEnabled('Warehouse')
        ) ? $this->belongsTo('Modules\Warehouse\Models\WarehouseProduct', 'warehouse_product_id') : null;
    }

    /**
     * Establece la relación con los productos de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseProduct()
    {
        return $this->belongsTo(PurchaseProduct::class);
    }

    /**
     * Establece la relación con el historial de impuestos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historyTax()
    {
        return $this->belongsTo(HistoryTax::class);
    }

    /**
     * Establece la relación con la Unidad de Medida
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    /**
     * Establece la relación con la tabla pivote de compras
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pivotPurchase()
    {
        return $this->hasMany(PurchasePivotModelsToRequirementItem::class);
    }

     /**
     * Obtiene la informacion de cotizacion relacionada a un item
     *
     * @author Francisco Escala <Fjescala@gmail.com> >
     *
     * @return     string|null  Devuelve la información de cotizacion relacionada a item
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
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return     string|null  Devuelve el nombre de un pais si esta definido, de lo contrario devuelve nulo
     */
    public function getUnitPriceAttribute(): ?string
    {
        $r = PurchasePivotModelsToRequirementItem::where('purchase_requirement_item_id', $this->id)->first();
        return $r ? $r->unit_price : null;
    }
}
