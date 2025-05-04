<?php

namespace Modules\Asset\Models;

use App\Models\User;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetAsignationDelivery
 * @brief Datos de las entregas de equipos asociados a una asignación
 *
 * Gestiona el modelo de datos de las entregas de equipos asociados a una asignación
 *
 * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve / javierrupe19@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetAsignationDelivery extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use ModelsTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'state', 'observation', 'asset_asignation_id',
        'user_id', 'approved_by_id', 'received_by_id', 'ids_assets'
    ];

    /**
     * Método que obtiene la asignación asociada al registro
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetAsignation()
    {
        return $this->belongsTo(AssetAsignation::class);
    }

    /**
     * Método que obtiene el usuario asociado al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
