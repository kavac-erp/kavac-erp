<?php

namespace Modules\Asset\Models;

use App\Models\User;
use App\Models\Image;
use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetRequest
 * @brief Datos de las solicitudes de bienes institucionales
 *
 * Gestiona el modelo de datos de las solicitudes de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetRequest extends Model implements Auditable
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
        'code', 'type', 'motive', 'state', 'delivery_date', 'address',
        'country_id', 'estate_id', 'municipality_id', 'parish_id',
        'agent_name', 'agent_telf', 'agent_email', 'user_id', 'institution_id'
    ];

    /**
     * Método que obtiene los bienes asociados a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetRequestAssets()
    {
        return $this->hasMany(AssetRequestAsset::class);
    }

    /**
     * Método que obtiene los eventos asociados a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetRequestEvents()
    {
        return $this->hasMany(AssetRequestEvent::class);
    }

    /**
     * Método que obtiene las prorrogas asociados a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetRequestExtension()
    {
        return $this->hasMany(AssetRequestExtension::class);
    }

    /**
     * Método que obtiene las prorrogas asociados a la solicitud
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetRequestDelivery()
    {
        return $this->hasMany(AssetRequestDelivery::class);
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

     /**
     * Obtiene todos documentos asociados
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Obtiene todos las imagenes
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->delivery_date;
    }
}
