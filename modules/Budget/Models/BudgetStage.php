<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class BudgetStage
 * @brief Datos de las etapas presupuestarias
 *
 * Gestiona el modelo de datos para las etapas presupuestarias:
 * Precompromiso = PRE
 * Programado = PRO
 * Comprometido = COM
 * Causado = CAU
 * Pagado = PAG
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetStage extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at', 'registered_at'];

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'registered_at',
        'type',
        'amount',
        'budget_compromise_id',
        'stageable_type',
        'stageable_id'
    ];

    /**
     * Establece la relación morfológica con los estatus presupuestarios
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function stageable()
    {
        return $this->morphTo();
    }

    /**
     * Establece la relación con el compromiso presupuestario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetCompromise()
    {
        return $this->belongsTo(BudgetCompromise::class);
    }
}
