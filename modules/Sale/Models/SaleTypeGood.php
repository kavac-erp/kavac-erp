<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class SaleTypeGood
 * @brief Datos de tipos de bienes
 *
 * Gestiona el modelo de los tipos de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleTypeGood extends Model implements Auditable
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
    protected $fillable = ['name', 'description', 'define_attributes'];

    /**
     * Método que obtiene los tipos de bienes
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleTypeGoodAttribute()
    {
        return $this->hasMany(SaleTypeGoodAttribute::class);
    }

    /**
     * Establece la relación con la lista de subservicios
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleListSubservices()
    {
        return $this->hasMany(SaleListSubservices::class);
    }
}
