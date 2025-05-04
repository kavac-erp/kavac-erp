<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use App\Models\HistoryTax;
use App\Traits\ModelsTrait;
use App\Models\MeasurementUnit;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleBillInventoryProduct
 * @brief Gestiona los datos del inventario sobre las ventas
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleBillInventoryProduct extends Model implements Auditable
{
    use AuditableTrait;
    use ModelsTrait;
    use SoftDeletes;

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
    protected $fillable = ['currency_id', 'history_tax_id', 'measurement_unit_id', 'sale_goods_to_be_traded_id', 'sale_list_subservices_id', 'value', 'product_type', 'quantity', 'sale_warehouse_inventory_product_id', 'sale_bill_id'];

    /**
     * Método que obtiene las formas de pago almacenadas en el sistema
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
     * Método que obtiene los registros del modelo de bienes a comercializar
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function saleGoodsToBeTraded()
    {
        return $this->belongsTo(SaleGoodsToBeTraded::class);
    }

    /**
     * Método que obtiene la lista de clientes del módulo de comercialización
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleBill()
    {
        return $this->belongsTo(SaleBill::class);
    }

    /**
     * Método que obtiene el producto asociado al inventario
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
     * Método que obtiene la lista de subservicios registrados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleListSubservices()
    {
        return $this->belongsTo(SaleListSubservices::class);
    }
}
