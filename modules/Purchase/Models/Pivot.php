<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class Pivot
 * @brief Modelo que gestiona los datos de relaciones entre tablas morfológicas
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Pivot extends Model implements Auditable
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
    protected $fillable = ['recordable_type', 'recordable_id', 'relatable_type', 'relatable_id'];

    /**
     * Relación morfológica entre modelos que establece la relación entre dos tablas
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function relatable()
    {
        return $this->morphTo();
    }
    /**
     * Relación morfológica entre modelos que establece la relación de datos entre dos tablas
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function recordable()
    {
        return $this->morphTo();
    }
}
