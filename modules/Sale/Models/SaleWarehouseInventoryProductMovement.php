<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleWarehouseInventoryProductMovement
 * @brief Datos de los movimientos de los productos entre almacenes
 *
 * Gestiona el modelo de datos de los productos para los movimientos de almacén
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleWarehouseInventoryProductMovement extends Model implements Auditable
{
    use AuditableTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'quantity', 'new_value', 'sale_warehouse_movement_id', 'sale_warehouse_initial_inventory_product_id', 'sale_warehouse_inventory_product_id'
    ];

    /**
     * Método que obtiene el registro en el inventario del producto movilizado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseInventoryProduct()
    {
        return $this->belongsTo(SaleWarehouseInventoryProduct::class);
    }

    /**
     * Método que obtiene el registro en el inventario del producto movilizado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseProductValue()
    {
        return $this->belongsTo(SaleWarehouseProductValue::class);
    }

    /**
     * Método que obtiene el registro en el inventario del producto que se va movilizar
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseInitialInventoryProduct()
    {
        return $this->belongsTo(SaleWarehouseInventoryProduct::class);
    }

    /**
     * Método que obtiene el movimiento de almacén asociado al registro
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseMovement()
    {
        return $this->belongsTo(SaleWarehouseMovement::class);
    }

    /**
     * Método que obtiene la moneda en que se expresa el valor del producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
