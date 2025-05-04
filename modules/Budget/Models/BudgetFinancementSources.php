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
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetFinancementSources extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Indica si se guarda información de fecha y tiempo
     *
     * @var boolean $timestamps
     */
    public $timestamps = true;

    /**
     * Lista con campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'budget_financement_type_id',
    ];

    /**
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['budgetFinancementType'];

    /**
     * Las fuentes de financiamiento tiene un tipo de financiamiento.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budgetFinancementType()
    {
        return $this->belongsTo(BudgetFinancementTypes::class);
    }
}
