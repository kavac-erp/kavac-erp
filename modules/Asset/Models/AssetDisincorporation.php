<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use App\Models\User;
use Carbon\Carbon;

/**
 * @class AssetDisincorporation
 * @brief Datos de las desincorporaciones de los bienes institucionales
 *
 * Gestiona el modelo de datos de las desincorporaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class AssetDisincorporation extends Model implements Auditable
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
        'code', 'asset_disincorporation_motive_id', 'date', 'observation', 'user_id', 'institution_id',
        'authorized_by_id', 'formed_by_id', 'produced_by_id', 'document_status_id',
    ];
      /**
     * Obtiene todos documentos asociados
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function documents()
    {
        return $this->morphMany(\App\Models\Document::class, 'documentable');
    }

    /**
     * Obtiene todos las imagenes
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }
    /**
    * Método que obtiene los bienes desincorporados
    *
    * @author Henry Paredes <hparedes@cenditel.gob.ve>
    * @return \Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
    * AssetDisincorporationAsset
    */
    public function assetDisincorporationAssets()
    {
        return $this->hasMany(AssetDisincorporationAsset::class);
    }

    /**
     * Método que obtiene el motivo de la desincorporacion del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     * AssetDisincorporationMotive
     */
    public function assetDisincorporationMotive()
    {
        return $this->belongsTo(AssetDisincorporationMotive::class);
    }

    /**
     * Método que obtiene el usuario asociado al registro
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Método que obtiene la institución a la cual está relaciona la desincorporación
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     *                                                           Institution
     */
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

    /**
     * Método que obtiene el estado del documento que está relaciona la desincorporación
     *
     * @author Henry Paredes <mazambrano@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo Objeto con el registro relacionado al modelo
     *                                                           Document_status
     */
    public function documentStatus()
    {
        return $this->belongsTo(\App\Models\DocumentStatus::class);
    }

    public function scopeSearch($query, $search)
    {
        $isDate = true;
        $formattedDate = '';
        try {
            $formattedDate = Carbon::createFromFormat('d/m/Y', $search)?->format('Y-m-d');
        } catch (\Throwable $th) {
            $isDate = false;
        }

        return $query->when('' != $search, function ($query) use ($search, $formattedDate, $isDate) {
            return $query
                ->where(function ($query) use ($search, $formattedDate, $isDate) {
                    $query->when($isDate, function ($query) use ($formattedDate) {
                        return $query->whereDate('date', $formattedDate);
                    });
                })
                ->orWhereRaw('LOWER(code) LIKE ?', [strtolower("%$search%")])
                ->orWhereHas('assetDisincorporationMotive', function ($query) use ($search) {
                    $query
                        ->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                })
                ->orWhereHas('documentStatus', function ($query) use ($search) {
                    $query
                        ->whereRaw('LOWER(name) LIKE ?', [strtolower("%$search%")]);
                });
        });
    }
}
