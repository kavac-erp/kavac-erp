<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Modules\Sale\Models\SaleBillInventoryProduct;

/**
 * @class SaleBill
 * @brief Gestiona los datos de las facturas
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleBill extends Model implements Auditable
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
    protected $fillable = ['code', 'state', 'type', 'type_person', 'name', 'id_number', 'rif', 'phone', 'email', 'sale_form_payment_id', 'rejected_reason'];

    /**
     * Lista de atributos personalizados obtenidos por defecto
     *
     * @var array $appends
     */
    protected $appends = [
        'bill_total_without_taxs', 'bill_taxs', 'bill_totals', 'sale_bill_products'
    ];

    /**
     * Atributo que devuelve informacion de los bienes a comercializar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    integer|float
     */
    public function getBillTotalWithoutTaxsAttribute()
    {
        $billProducts = SaleBillInventoryProduct::get();
        $data = 0;

        foreach ($billProducts as $product) {
            if ($product->sale_bill_id === $this->id) {
                $value = $product->value * $product->quantity;
                $data += $value;
            }
        }

        return $data;
    }

    /**
     * Atributo que devuelve informacion de los bienes a comercializar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    integer|float
     */
    public function getBillTaxsAttribute()
    {
        $billProducts = SaleBillInventoryProduct::with('historyTax')->get();
        $total_iva = 0;

        foreach ($billProducts as $product) {
            if ($product->sale_bill_id === $this->id) {
                if ($product->historyTax) {
                    $value = $product->value * $product->quantity;
                    $iva = $product->historyTax->percentage * $value / 100;
                    $total_iva += $iva;
                }
            }
        }

        return $total_iva;
    }

    /**
     * Atributo que devuelve informacion de los bienes a comercializar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    integer|float
     */
    public function getBillTotalsAttribute()
    {
        $billProducts = SaleBillInventoryProduct::with('historyTax')->get();
        $total_iva = 0;
        $total_value = 0;
        $total = 0;

        foreach ($billProducts as $product) {
            if ($product->sale_bill_id === $this->id) {
                $value = $product->value * $product->quantity;

                if ($product->historyTax) {
                    $iva = $product->historyTax->percentage * $value / 100;
                    $total_iva += $iva;
                }

                $total_value += $value;
                $total = $total_iva + $total_value;
            }
        }

        return $total;
    }

    /**
     * Atributo que devuelve informacion de los productos registrados
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array
     */
    public function getSaleBillProductsAttribute()
    {
        $billProducts = SaleBillInventoryProduct::with('historyTax', 'measurementUnit', 'saleGoodsToBeTraded', 'saleWarehouseInventoryProduct')->get();
        $products = [];

        foreach ($billProducts as $product) {
            if ($product->sale_bill_id === $this->id) {
                $total_without_tax = $product->value * $product->quantity;
                $total = $total_without_tax;
                $history_tax_value = 0;

                if ($product->history_tax_id) {
                    $history_tax_value = $product->historyTax->percentage * $total_without_tax / 100;
                    $total = $total_without_tax + $history_tax_value;
                }

                $products[$product->id] = [
                    'product_type' => $product->product_type,
                    'sale_warehouse_inventory_product_id' => $product->sale_warehouse_inventory_product_id,
                    'sale_goods_to_be_traded_id' => $product->sale_goods_to_be_traded_id,
                    'sale_list_subservices_id' => $product->sale_list_subservices_id,
                    'measurement_unit_id' => $product->measurement_unit_id,
                    'currency_id' => $product->currency_id,
                    'history_tax_id' => $product->history_tax_id,
                    'value' => $product->value,
                    'quantity' => $product->quantity,
                    'total_without_tax' => $total_without_tax,
                    'sale_goods_to_be_traded_name' => $product->saleGoodsToBeTraded ? $product->saleGoodsToBeTraded->name : '',
                    'inventory_product_name' => $product->sale_warehouse_inventory_product_id ? $product->saleWarehouseInventoryProduct->saleSettingProduct->name : '',
                    'measurement_unit_name' => $product->measurementUnit->name,
                    'sale_list_subservices_name' => $product->saleListSubservices ? $product->saleListSubservices->name : '',
                    'currency_name' => $product->currency->symbol . ' - ' . $product->currency->name,
                    'history_tax_value' => $history_tax_value,
                    'total' => $total,
                ];
            }
        }

        return $products;
    }

    /**
     * Método que obtiene la lista de clientes del módulo de comercialización
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleClient()
    {
        return $this->belongsTo(SaleClient::class);
    }

    /**
     * Método que obtiene la lista de almacenes del módulo de comercialización
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
     * Método que obtiene la lista de métodos de pago del módulo de comercialización
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleFormPayment()
    {
        return $this->belongsTo(SaleFormPayment::class);
    }

    /**
     * Método que obtiene la lista de descuentos del módulo de comercialización
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleDiscount()
    {
        return $this->belongsTo(SaleDiscount::class);
    }

    /**
     * Método que obtiene la lista de productos guardados en el almacén del módulo de comercialización
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleBillInventoryProduct()
    {
        return $this->hasMany(SaleBillInventoryProduct::class);
    }

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
}
