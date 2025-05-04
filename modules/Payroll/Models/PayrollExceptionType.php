<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class PayrollExceptionType
 * @brief Datos de tipos de excepciones de jornada laboral
 *
 * Gestiona el modelo de tipos de excepciones de jornada laboral
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollExceptionType extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /** @var array $dates Lista de atributos para la gestión de fechas */
    protected $dates = ['deleted_at'];

    /** @var array $fillable Lista de atributos que pueden ser asignados masivamente */
    protected $fillable = ['name', 'description', 'sign', 'affect_id', 'value_max'];

    /**
     * Get the affect that owns the PayrollExceptionType
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affect(): BelongsTo
    {
        return $this->belongsTo(PayrollExceptionType::class, 'affect_id');
    }
}
