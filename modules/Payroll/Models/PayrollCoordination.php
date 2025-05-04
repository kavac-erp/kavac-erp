<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollCoordination
 *
 * @brief Datos de las coordinaciones
 *
 * Gestiona el modelo de datos dee la tabla Coordinaciones
 *
 * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollCoordination extends Model implements Auditable
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
     * Lista de relaciones a cargar por defecto en las consultas
     *
     * @var array $with
     */
    protected $with = ['department'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'name',
        'description',
        'department_id'
    ];

    /**
     * Las coordinaciones tienen un Departamento de adscripción.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Método que obtiene la coordinación asociado a muchos datos laborales
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollCoordinations()
    {
        return $this->hasMany(PayrollEmployment::class);
    }
}
