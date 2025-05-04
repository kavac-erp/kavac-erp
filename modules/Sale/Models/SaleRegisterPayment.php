<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Modules\Sale\Models\SaleGoodsToBeTraded;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleRegisterPayment
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleRegisterPayment extends Model implements Auditable
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
    protected $fillable = ['id','order_or_service_define_attributes','order_service_id','total_amount','way_to_pay','banking_entity','reference_number','payment_date','advance_define_attributes','payment_approve','payment_refuse'];

    /**
     * Establece la relación con el pedido o servicio
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleService()
    {
        return $this->belongsTo(SaleService::class, 'order_service_id', 'id');
    }

    /**
     * Método que obtiene las formas de pago almacenadas en el sistema
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }


    /**
     * Método que obtiene los bancos registrados
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBank()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->belongsTo(\Modules\Finance\Models\FinanceBank::class) : null;
    }

    /**
     * Método que obtiene las formas de cobro
     *
     * @author Miguel Narvaez <mnarvaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleFormPayment()
    {
        return $this->belongsTo(SaleFormPayment::class);
    }
}
