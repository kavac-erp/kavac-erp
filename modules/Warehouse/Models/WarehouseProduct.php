<?php

namespace Modules\Warehouse\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class WarehouseProduct
 * @brief Datos de los productos
 *
 * Gestiona el modelo de datos de los productos almacenables
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class WarehouseProduct extends Model implements Auditable
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
        'name',
        'description',
        'define_attributes',
        'measurement_unit_id',
        'budget_account_id',
        'accounting_account_id',
        'history_tax_id'
    ];

    /**
     * Método que obtiene los atributos personalizados de un producto
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function warehouseProductAttributes()
    {
        return $this->hasMany(WarehouseProductAttribute::class);
    }

    /**
     * Método que obtiene la unidad de medida del producto registrado
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(\App\Models\MeasurementUnit::class);
    }

    /**
     * Método que obtiene la información de la cuenta presupuestaria asociada al insumo
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return (
            Module::has('Budget') && Module::isEnabled('Budget')
        ) ? $this->belongsTo(\Modules\Budget\Models\BudgetAccount::class) : null;
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada al insumo
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }

    /**
     * Método que obtiene el impuesto del producto registrado
     *
     * @author Daniel Contreras <dcointreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historyTax()
    {
        return $this->belongsTo(\App\Models\HistoryTax::class);
    }
}
