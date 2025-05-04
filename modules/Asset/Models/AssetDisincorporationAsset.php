<?php

namespace Modules\Asset\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetDisincorporationAsset
 * @brief Datos del listado de bienes registrados en una desincorporación
 *
 * Gestiona el modelo de datos de los bienes registrados en una desincorporación
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDisincorporationAsset extends Model implements Auditable
{
    use AuditableTrait;
    use ModelsTrait;
    use SoftDeletes;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['asset_id','asset_disincorporation_id'];

    /**
     * Método que obtiene la desincorporación asociada al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetDisincorporation()
    {
        return $this->belongsTo(AssetDisincorporation::class);
    }

    /**
     * Método que obtiene el bien asociado a la desincorporación
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
