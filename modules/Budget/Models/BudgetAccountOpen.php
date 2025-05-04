<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class BudgetAccountOpen
 * @brief Datos de cuentas formuladas en presupuesto
 *
 * Gestiona el modelo de datos para las cuentas formuladas en presupuesto
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetAccountOpen extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de campos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'jan_amount', 'feb_amount', 'mar_amount', 'apr_amount', 'may_amount', 'jun_amount',
        'jul_amount', 'aug_amount', 'sep_amount', 'oct_amount', 'nov_amount', 'dec_amount',
        'total_year_amount', 'total_year_amount_m', 'total_real_amount', 'total_estimated_amount',
        'budget_account_id', 'budget_sub_specific_formulation_id'
    ];

    /**
     * Lista de campos ocultos
     *
     * @var array $hidden
     */
    protected $hidden = ['created_at', 'updated_at'];

    /**
     * Establece la relación con la cuenta presupuestaria
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetAccount()
    {
        return $this->belongsTo(BudgetAccount::class);
    }

    /**
     * Establece la relacion con la formulación por sub especifica
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function subSpecificFormulation()
    {
        return $this->belongsTo(BudgetSubSpecificFormulation::class, 'budget_sub_specific_formulation_id');
    }
}
