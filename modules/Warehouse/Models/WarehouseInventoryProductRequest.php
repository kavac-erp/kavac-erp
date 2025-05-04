<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class WarehouseInventoryProductRequest
 * @brief Datos de los productos solicitados
 *
 * Gestiona el modelo de datos de los productos almacenables solicitados
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseInventoryProductRequest extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['quantity', 'warehouse_request_id', 'warehouse_inventory_product_id', 'new_exist'];

    /**
     * Método que obtiene el producto asociado al inventario
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseInventoryProduct()
    {
        return $this->belongsTo(WarehouseInventoryProduct::class);
    }

    /**
     * Método que obtiene la solicitud registrada
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseRequest()
    {
        return $this->belongsTo(WarehouseRequest::class);
    }
}
