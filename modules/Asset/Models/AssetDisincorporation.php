<?php

namespace Modules\Asset\Models;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Image;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetDisincorporation
 * @brief Datos de las desincorporaciones de los bienes institucionales
 *
 * Gestiona el modelo de datos de las desincorporaciones de bienes institucionales
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
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
     * Método que obtiene los bienes desincorporados
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetDisincorporationAssets()
    {
        return $this->hasMany(AssetDisincorporationAsset::class);
    }

    /**
     * Método que obtiene el motivo de la desincorporacion del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetDisincorporationMotive()
    {
        return $this->belongsTo(AssetDisincorporationMotive::class);
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
     * Método que obtiene la institución a la cual está relaciona la desincorporación
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

    /**
     * Método que obtiene el estado del documento que está relaciona la desincorporación
     *
     * @author Henry Paredes <mazambrano@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(\App\Models\DocumentStatus::class);
    }

    /**
     * Scope para buscar datos
     *
     * @param Builder $query Objeto con la consulta
     * @param string $search Datos a buscar
     *
     * @return Builder
     */
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
