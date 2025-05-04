<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollPreviousJob
 * @brief Trabajos anteriores
 *
 * Gestiona el modelo de trabajos anteriores
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollPreviousJob extends Model implements Auditable
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
    protected $fillable = [
        'organization_name',
        'organization_phone',
        'payroll_sector_type_id',
        'previous_position',
        'payroll_staff_type_id',
        'start_date',
        'end_date',
        'payroll_employment_id'
    ];

    /**
     * Método que obtiene los cargos registrados en el sistema
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPosition()
    {
        return $this->belongsTo(PayrollPosition::class);
    }

    /**
     * Método que obtiene los tipos de personal registrados en el sistema
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaffType()
    {
        return $this->belongsTo(PayrollStaffType::class);
    }

    /**
     * Método que obtiene los tipos de sector registrados en el sistema
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSectorType()
    {
        return $this->belongsTo(PayrollSectorType::class);
    }

    /**
     * Método que obtiene los datos laborales del trabajador
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollEmployment()
    {
        return $this->belongsTo(PayrollEmployment::class);
    }
}
