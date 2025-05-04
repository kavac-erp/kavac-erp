<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class BudgetFinancementSources
 *
 * @brief Gestión de las fuentes de financiamiento.
 *
 * Gestiona el modelo de datos para las fuentes de financiamiento.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class BudgetFinancementSources extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'budget_financement_type_id',
    ];

    protected $with = ['budgetFinancementType'];

    /**
     * Las fuentes de financiamiento tiene un tipo de financiamiento.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetFinancementType()
    {
        return $this->belongsTo(BudgetFinancementTypes::class);
    }
}
