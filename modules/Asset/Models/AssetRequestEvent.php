<?php

namespace Modules\Asset\Models;

use App\Traits\ModelsTrait;
use Modules\Asset\Models\Asset;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetRequestEvent
 * @brief Datos de los eventos asociados a una solicitud
 *
 * Gestiona el modelo de datos de los eventos asociados a una solicitud
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRequestEvent extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use ModelsTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['type', 'description', 'asset_request_id', 'ids_assets'];

    /**
     * Lista de Bienes institucionales pertenecientes a un evento
     *
     * @var array $appends
     */
    protected $appends = ['assets_event'];

    /**
     * Obtiene el atributo para los registros de eventos de bienes
     *
     * @return \Illuminate\Database\Eloquent\Collection|Asset[]|void
     */
    public function getAssetsEventAttribute()
    {
        if ($this->ids_assets) {
            $ids_assets = json_decode($this->ids_assets, true);
            return Asset::whereIn('id', $ids_assets)->get();
        }
    }
    /**
     * Método que obtiene la solicitud asociada al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }
}
