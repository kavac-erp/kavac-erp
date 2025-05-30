<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Models\User;
use App\Traits\ModelsTrait;

/**
 * @class WarehouseClose
 * @brief Datos de los cierres de almacén
 *
 * Gestiona el modelo de datos para los cierres de almacén registrados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseClose extends Model implements Auditable
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
        'initial_date', 'end_date', 'initial_user_id', 'end_user_id', 'warehouse_id', 'observations'
    ];

    /**
    * Método que obtiene el usuario que inicio el cierre del almacén
    *
    * @author Henry Paredes <hparedes@cenditel.gob.ve>
    *
    * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
    */
    public function initialUser()
    {
        return $this->belongsTo(User::class, 'initial_user_id');
    }

    /**
     * Método que obtiene el usuario que finalizó el cierre del almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function endUser()
    {
        return $this->belongsTo(User::class, 'end_user_id');
    }

    /**
     * Método que obtiene el almacén involucrado el en cierre de funciones
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
}
