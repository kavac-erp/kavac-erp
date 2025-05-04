<?php

namespace Modules\Asset\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetInventoryAsset
 * @brief Datos del listado de bienes inventariados
 *
 * Gestiona el modelo de datos de los bienes registrados en inventario
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetInventoryAsset extends Model implements Auditable
{
    use AuditableTrait;
    use ModelsTrait;
    use SoftDeletes;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['asset_condition', 'asset_status', 'asset_use_function', 'asset_id', 'asset_inventory_id'];

    /**
     * Método que obtiene registro de inventario asociada al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetInventory()
    {
        return $this->belongsTo(AssetInventory::class);
    }

    /**
     * Método que obtiene el bien asociado al registro de inventario
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class)->withTrashed();
    }
}
