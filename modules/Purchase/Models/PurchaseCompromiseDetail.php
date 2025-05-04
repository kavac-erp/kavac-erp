<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PurchaseCompromiseDetail
 * @brief Datos de los detalles de los compromisos de compras
 *
 * Gestiona el modelo de datos para los detalles de los compromisos Compromisos de Compras
 * Este modelo es usado en caso de que no se encuentre instalado el modulo de Presupuesto
 *
 * @author Ing. Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseCompromiseDetail extends Model implements Auditable
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
        'description', 'amount', 'tax_amount', 'tax_id', 'purchase_compromise_id', 'budget_account_id',
        'budget_sub_specific_formulation_id'
    ];

    /**
     * Establece la relación con el compromiso de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseCompromise()
    {
        return $this->belongsTo(PurchaseCompromise::class);
    }
}
