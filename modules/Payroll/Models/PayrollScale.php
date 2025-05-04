<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class      PayrollScale
 * @brief      Datos de las escalas o niveles de un escalafón
 *
 * Gestiona el modelo de datos de las escalas o niveles de un escalafón
 *
 * @author     Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollScale extends Model implements Auditable
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
    protected $fillable = ['name', 'value', 'payroll_salary_scale_id', 'relationable_type', 'relationable_id', 'relationship_type'];

    /**
     * Método que obtiene el eslacafón salarial al que pertenece la escala
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSalaryScale()
    {
        return $this->belongsTo(PayrollSalaryScale::class);
    }

    /**
     * File morphs to models in relationable_type.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function relationable()
    {
        // morphTo($name = relationable, $type = relationable_type, $id = relationable_id)
        // requires relationable_type and relationable_id fields on $this->table
        return $this->morphTo();
    }
}
