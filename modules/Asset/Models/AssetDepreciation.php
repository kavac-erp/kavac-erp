<?php

namespace Modules\Asset\Models;

use App\Models\Institution;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetDepreciation
 * @brief Modelo que gestiona los datos de depreciación de bienes
 *
 * Gestiona la depreciación de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
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
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['code', 'year', 'amount', 'document_status_id', 'institution_id'];

    /**
     * Método que obtiene el estatus del documento asociado a la depreciación
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
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Método que obtiene los bienes asociados a la depreciación
     *
     * @return  \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetDepreciationAssets()
    {
        return $this->hasMany(AssetDepreciationAsset::class);
    }
}
