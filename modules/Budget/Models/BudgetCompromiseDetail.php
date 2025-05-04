<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class BudgetCompromiseDetail
 * @brief Datos de los detalles de los compromisos presupuestarios
 *
 * Gestiona el modelo de datos para los detalles de los compromisos Compromisos de Presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetCompromiseDetail extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'description', 'amount', 'tax_amount', 'tax_id', 'budget_compromise_id', 'budget_account_id',
        'budget_sub_specific_formulation_id', 'document_status_id', 'budget_tax_key'
    ];

    /**
     * Agrega campos personalizados
     *
     * @var array
     */
    protected $appends = ['total'];

    /**
     * Obtiene el total del compromiso
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    public function getTotalAttribute()
    {
        return $this->amount + $this->tax_amount;
    }

    /**
     * BudgetCompromiseDetail belongs to BudgetCompromise.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetCompromise()
    {
        return $this->belongsTo(BudgetCompromise::class);
    }

    /**
     * Get the budgetAccount that owns the BudgetCompromiseDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return (Module::has('Accounting') && Module::isEnabled('Accounting')) ?
               $this->belongsTo(\Modules\Accounting\Models\BudgetAccount::class) :
               $this->belongsTo(BudgetAccount::class);
    }

    /**
     * Get the budgetSubSpecificFormulation that owns the BudgetCompromiseDetail
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
     * @method  tax
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return object Objeto con el registro relacionado al modelo Tax
     */
    public function tax()
    {
        return $this->belongsTo('App\\Models\\Tax');
    }
}
