<?php

/** [descripción del namespace] */

namespace Modules\Asset\Models;

use App\Models\Institution;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Asset\Enums\DepreciationType;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetDepreciationMethod
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [Yennifer Ramirez] [yramirez@cenditel.gob.ve]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDepreciationMethod extends Model implements Auditable
{
    use AuditableTrait;
    use ModelsTrait;
    use SoftDeletes;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'activation_date',
        'active',
        'depreciation_type_id',
        'formula',
        'institution_id',
        'name',
    ];

    protected $appends = [
        'depreciation_type', 'formula'
    ];

    protected $casts = [
        'depreciation_type_id' => DepreciationType::class,
    ];

    public function getDepreciationTypeAttribute()
    {
        return $this->depreciation_type_id->getName();
    }

    public function getFormulaAttribute()
    {
        return $this->depreciation_type_id->getFormula();
    }

    /**
     * Get the user that owns the AssetDepreciationMethod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}
