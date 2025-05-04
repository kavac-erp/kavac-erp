<?php

/** [descripción del namespace] */

namespace Modules\Asset\Models;

use App\Models\Currency;
use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AssetDepreciation
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDepreciation extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['code', 'year', 'amount', 'document_status_id', 'institution_id'];

    /**
     * Método que obtiene el estatus del documento asociado a la depreciación
     *
     * @method  documentStatus
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Método que obtiene la institución asociada a la depreciación
     *
     * @method  institution
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene los bienes asociados a la depreciación
     *
     * @method  assetDepreciationAssets
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetDepreciationAssets()
    {
        return $this->hasMany(AssetDepreciationAsset::class);
    }
}
