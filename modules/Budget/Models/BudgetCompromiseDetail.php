<?php

namespace Modules\Budget\Models;

use App\Models\Tax;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class BudgetCompromiseDetail
 * @brief Datos de los detalles de los compromisos presupuestarios
 *
 * Gestiona el modelo de datos para los detalles de los compromisos Compromisos de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetCompromiseDetail extends Model implements Auditable
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
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'description', 'amount', 'tax_amount', 'tax_id', 'budget_compromise_id', 'budget_account_id',
        'budget_sub_specific_formulation_id', 'document_status_id', 'budget_tax_key'
    ];

    /**
     * Agrega campos personalizados
     *
     * @var array $appends
     */
    protected $appends = ['total'];

    /**
     * Obtiene el total del compromiso
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return float
     */
    public function getTotalAttribute()
    {
        return $this->amount + $this->tax_amount;
    }

    /**
     * Obtiene la relación con el compromiso presupuestario
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetCompromise()
    {
        return $this->belongsTo(BudgetCompromise::class);
    }

    /**
     * Obtiene la relación con la cuenta presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\BudgetAccount::class)
        : $this->belongsTo(BudgetAccount::class);
    }

    /**
     * Obtiene la relación con la formulación presupuestaria
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetSubSpecificFormulation()
    {
        return $this->belongsTo(BudgetSubSpecificFormulation::class);
    }

    /**
     * Método que obtiene el Impuesto asociado
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
