<?php

namespace Modules\CitizenService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class CitizenServiceIndicator
 * @brief Gestiona la información de los indicadores de la oficina de atención al ciudadano
 *
 * @author Ing. Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceIndicator extends Model implements Auditable
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
        'name', 'description', 'effect_types_id'
    ];

    /**
     * Establece la relación con los tipos de efectos
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function effectType()
    {
        return $this->belongsTo(CitizenServiceEffectType::class);
    }

    /**
     * Establece la relación con los indicadores
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function addIndicator()
    {
        return $this->hasMany(CitizenServiceAddIndicator::class);
    }
}
