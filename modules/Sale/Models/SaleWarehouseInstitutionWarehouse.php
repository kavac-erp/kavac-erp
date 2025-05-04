<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class WarehouseInstitutionWarehouse
 * @brief Datos de los almacenes gestionados por cada institución
 *
 * Gestiona el modelo de datos de los almacenes gestionados por cada institución
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleWarehouseInstitutionWarehouse extends Model implements Auditable
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
    protected $fillable = ['institution_id', 'sale_warehouse_id', 'manage', 'main'];

    /**
     * Método que obtiene la institution que gestionan el almacén
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class);
    }

    /**
     * Método que obtiene el almacén gestionado por la institucion
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouse()
    {
        return $this->belongsTo(SaleWarehouse::class);
    }

    /**
     * Método que obtiene el inventario de los productos que gestiona la institución en un almacén
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleWarehouseInventoryProducts()
    {
        return $this->hasMany(SaleWarehouseInventoryProduct::class);
    }
}
