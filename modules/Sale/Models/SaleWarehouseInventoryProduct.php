<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use App\Models\HistoryTax;
use App\Models\MeasurementUnit;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleWarehouseInventoryProduct
 * @brief Datos del inventario de los productos
 *
 * Gestiona el modelo de datos del inventario de los productos almacenables
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleWarehouseInventoryProduct extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

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
        'code', 'exist', 'reserved', 'unit_value', 'currency_id', 'measurement_unit_id', 'sale_setting_product_id', 'sale_warehouse_institution_warehouse_id', 'history_tax_id'
    ];

    /**
     * Método que obtiene el producto registrado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleSettingProduct()
    {
        return $this->belongsTo(SaleSettingProduct::class);
    }

    /**
     * Método que obtiene los valores de los atributos del producto registrado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleWarehouseProductValues()
    {
        return $this->hasMany(SaleWarehouseProductValue::class);
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

    /**
     * Método que obtiene el almacen donde esta inventariado el producto
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseInstitutionWarehouse()
    {
        return $this->belongsTo(SaleWarehouseInstitutionWarehouse::class);
    }

    /**
     * Método que obtiene las reglas de almacenamiento del producto en el inventario
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function saleWarehouseInventoryRule()
    {
        return $this->hasOne(SaleWarehouseInventoryRule::class);
    }

    /**
     * Método que obtiene la unidad de medida
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    /**
     * Método que obtiene los porcentajes de impuestos almacenados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historyTax()
    {
        return $this->belongsTo(HistoryTax::class);
    }

    /**
     * Método que obtiene el registro en el inventario del producto movilizado
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleWarehouseInventoryProductMovement()
    {
        return $this->hasMany(SaleWarehouseInventoryProductMovement::class);
    }
}
