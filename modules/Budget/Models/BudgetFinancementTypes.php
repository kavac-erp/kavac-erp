<?php

namespace Modules\Budget\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class BudgetFinancementTypes
 *
 * @brief Gestión de los tipos de financiamiento.
 *
 * Gestiona el modelo de datos para los tipos de financiamiento.
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class BudgetFinancementTypes extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla en base de datos
     *
     * @var string $table
     */
    protected $table = 'budget_financement_types';

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
    ];

    /**
     * Tipos de financiamiento puede tener muchas fuentes de financiamiento.
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetFinancementSources()
    {
        return $this->hasMany(BudgetFinancementSources::class);
    }
}
