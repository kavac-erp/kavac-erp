<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PurchaseOrder
 * @brief Gestiona la información de las órdenes de compra o servicios
 *
 * @property integer $id Identificador de la orden de compra o servicio
 * @property integer $purchase_supplier_id Identificador del proveedor
 * @property integer $currency_id Identificador de la moneda
 * @property float $subtotal Subtotal de la orden de compra o servicio
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseOrder extends Model implements Auditable
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
    protected $fillable = ['purchase_supplier_id', 'currency_id', 'subtotal'];

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
     * Establece la relación con la moneda
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Establece la relación con los requerimientos de la orden
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRequirement()
    {
        return $this->hasMany(PurchaseRequirement::class);
    }

    /**
     * Establece la relación con los detalles de los requerimientos de la orden
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function relatable()
    {
        return $this->morphMany(PurchasePivotModelsToRequirementItem::class, 'relatable');
    }
}
