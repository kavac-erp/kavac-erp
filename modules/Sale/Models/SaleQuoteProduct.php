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
 * @class SaleQuoteProduct
 * @brief Modelo para la gestion de los productos de cotizaciones en comercializacion
 *
 * Modelo para la gestion de los productos de cotizaciones en comercializacion
 *
 * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleQuoteProduct extends Model implements Auditable
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
        'value',
        'product_type',
        'sale_quote_id',
        'currency_id',
        'measurement_unit_id',
        'quantity',
        'total',
        'total_without_tax',
        'sale_warehouse_inventory_product_id',
        'sale_type_good_id',
        'history_tax_id',
        'sale_list_subservices_id',
    ];

    /**
     * Método que establece las unidades monetarias almacenadas en el sistema
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Método que obtiene la unidad de medida
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    /**
     * Método que obtiene los tipos de bien
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleTypeGood()
    {
        return $this->belongsTo(SaleTypeGood::class);
    }

    /**
     * Método que obtiene el producto de almacen (warehouse) en la cotizacion
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleWarehouseInventoryProduct()
    {
        return $this->belongsTo(SaleWarehouseInventoryProduct::class);
    }

    /**
     * Método que obtiene los impuestos del producto
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
     * Método que obtiene lista de Subservicios del producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleListSubservices()
    {
        return $this->belongsTo(SaleListSubservices::class);
    }
}
