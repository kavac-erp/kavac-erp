<?php

namespace Modules\Warehouse\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class WarehouseInventoryProduct
 * @brief Datos del inventario de los productos
 *
 * Gestiona el modelo de datos del inventario de los productos almacenables
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseInventoryProduct extends Model implements Auditable
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
        'code', 'exist', 'reserved', 'unit_value', 'currency_id', 'warehouse_product_id',
        'warehouse_institution_warehouse_id'
    ];

     /**
      * Lista de atributos personalizados a cargar con el modelo
      *
      * @var array $appends
      */
     protected $appends = ['real'];

    /**
     * Obtiene el valor real del producto
     *
     * @return float|int
     */
    public function getRealAttribute()
    {
        return $this->exist - $this->reserved;
    }

    /**
     * Método que obtiene el producto registrado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseProduct()
    {
        return $this->belongsTo(WarehouseProduct::class);
    }

    /**
     * Método que obtiene los valores de los atributos del producto registrado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseProductValues()
    {
        return $this->hasMany(WarehouseProductValue::class);
    }

    /**
     * Método que obtiene la moneda en que se expresa el valor del producto
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }

    /**
     * Método que obtiene el almacen donde esta inventariado el producto
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function warehouseInstitutionWarehouse()
    {
        return $this->belongsTo(WarehouseInstitutionWarehouse::class);
    }

    /**
     * Método que obtiene las reglas de almacenamiento del producto en el inventario
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function warehouseInventoryRule()
    {
        return $this->hasOne(WarehouseInventoryRule::class);
    }
}
