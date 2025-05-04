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
    protected $fillable = ['name', 'description', 'sign', 'affect_id', 'value_max'];

    /**
     * Obtiene la relación con los tipos de excepciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function affect(): BelongsTo
    {
        return $this->belongsTo(PayrollExceptionType::class, 'affect_id');
    }
}
