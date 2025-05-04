<?php

namespace Modules\CitizenService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class CitizenServiceAddIndicator
 * @brief Gestiona la información de los indicadores de solicitudes de servicio
 *
 * @author Ing. Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceAddIndicator extends Model implements Auditable
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
        'name', 'indicator_id', 'request_id'
    ];

    /**
     * Establece la relación con el indicador
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function indicator()
    {
        return $this->belongsTo(CitizenServiceIndicator::class);
    }

    /**
     * Establece la relación con la solicitud de servicio
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function request()
    {
        return $this->belongsTo(CitizenServiceRequest::class);
    }
}
