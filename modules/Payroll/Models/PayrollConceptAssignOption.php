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
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['payroll_concept_id', 'key', 'value', 'applicable_type', 'applicable_id'];

    /**
     * Método que obtine la información del concepto asociado al registro
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollConcept()
    {
        return $this->belongsTo(PayrollConcept::class);
    }

    /**
     * PayrollConceptAssignOption belongs to PayrollVacationPolicy.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollVacationPolicy()
    {
        // belongsTo(RelatedModel, foreignKey = payrollVacationPolicy_id, keyOnRelatedModel = id)
        return $this->belongsTo(PayrollVacationPolicy::class);
    }

    /**
     * File morphs to models in applicable_type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function applicable()
    {
        // morphTo($name = applicable, $type = applicable_type, $id = applicable_id)
        // requires applicable_type and applicable_id fields on $this->table
        return $this->morphTo();
    }
}
