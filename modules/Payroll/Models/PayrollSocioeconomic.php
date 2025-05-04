<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollSocioeconomic
 * @brief Datos de información socioeconómica del trabajador
 *
 * Gestiona el modelo de información socioeconómica
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSocioeconomic extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    protected $with = ['maritalStatus', 'payrollChildrens'];
    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'full_name_twosome', 'id_number_twosome', 'birthdate_twosome',
        'payroll_staff_id', 'marital_status_id',
    ];

    /**
     * Método que obtiene la información socioeconómica del trabajador asociado a una información personal del mismo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Método que obtiene la información socioeconómica del trabajador asociado a un estado civil
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function maritalStatus()
    {
        return $this->belongsTo(MaritalStatus::class);
    }

    /**
     * Método que obtiene lo socioeconómico del trabajador asociado a muchos hijos
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollChildrens()
    {
        return $this->hasMany(PayrollFamilyBurden::class);
    }
}
