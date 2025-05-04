<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class      PayrollConceptAssignOption
 * @brief      Datos de las opciones a asignar de un concepto
 *
 * Gestiona el modelo de opciones a asignar en concepto conceptos
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollConceptAssignOption extends Model implements Auditable
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
    protected $fillable = ['key', 'value', 'applicable_type', 'applicable_id'];

    /**
     * Obtiene la relación con la política de vacaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollVacationPolicy()
    {
        return $this->belongsTo(PayrollVacationPolicy::class);
    }

    /**
     * Obtiene la relación morfológica con otros modelos que se relacionen con la asignación de conceptos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function applicable()
    {
        return $this->morphTo();
    }
}
