<?php

namespace Modules\Asset\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetRequestAsset
 * @brief Datos de los bienes institucionales solicitados
 *
 * Gestiona el modelo de datos de los bienes solicitados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRequestAsset extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use ModelsTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['asset_id', 'asset_request_id'];

    /**
     * Método que obtiene la solicitud asociada al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>

     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetRequest()
    {
        return $this->belongsTo(AssetRequest::class);
    }

    /**
     * Método que obtiene el bien asociado al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>

     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }
}
