<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollCoordination
 * @brief Datos de las coordinaciones
 *
 * Gestiona el modelo de datos dee la tabla Coordinaciones
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollResponsibility extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de relaciones a cargar con el modelo
     *
     * @var array $with
     */
    protected $with = [
        'payrollPosition',
    ];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'department_id',
        'payroll_staff_id',
        'payroll_position_id',
        'payroll_coordination_id',
        'type_responsibility',
    ];

    /**
     * Método que obtiene el cargo asociado a una coordinación
     *
     * @author  Pedro Contreras <pmcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPosition()
    {
        return $this->belongsTo(PayrollPosition::class);
    }

    /**
     * Método que obtiene el personal asociado a una coordinación
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }
}
