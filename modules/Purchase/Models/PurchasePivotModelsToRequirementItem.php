<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PurchasePivotModelsToRequirementItem
 * @brief Gestiona los detalles de los requerimientos de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchasePivotModelsToRequirementItem extends Model implements Auditable
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
        'purchase_requirement_item_id',
        'unit_price',
        'relatable_type',
        'relatable_id',
        'quantity',
    ];

    /**
     * Establece la relación morfológica entre modelos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function relatable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con el detalle del requerimiento de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseRequirementItem()
    {
        return $this->belongsTo(PurchaseRequirementItem::class);
    }
}
