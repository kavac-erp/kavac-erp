<?php

namespace Modules\CitizenService\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Payroll\Models\PayrollStaff;

/**
 * @class CitizenServiceDepartment
 * @brief Gestiona la información de los departamentos de la oficina de atención al ciudadano
 *
 * @author Ing. Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceDepartment extends Model implements Auditable
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
        'name', 'description', 'director_id', 'coordinator_id'
    ];

    /**
     * Método que obtiene el departamento asociado a muchas solicitudes
     *
     * @author Yennifer Ramirez <yramirezs@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceRequests()
    {
        return $this->hasMany(CitizenServiceRequest::class);
    }

    /**
     * Método que obtiene el departamento asociado a muchos trabajadores
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function payrollStaffs()
    {
        return $this->belongsToMany(PayrollStaff::class);
    }

    /**
     * Establece la relación entre el cargo y el director de un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function departmentDirector()
    {
        return $this->hasOne(PayrollStaff::class, 'id', 'director_id');
    }

    /**
     * Establece la relación entre el cargo y el coordinador de un departamento
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function departmentCoordinator()
    {
        return $this->hasOne(PayrollStaff::class, 'id', 'coordinator_id');
    }

    /**
     * Establece la relación con el cargo del director
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scopeGetDirector()
    {
        return $this->belongsTo(PayrollStaff::class, 'director_id');
    }

    /**
     * Establece la relacion con el cargo del coordinador
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function scopeGetCoordinator()
    {
        return $this->belongsTo(PayrollStaff::class, 'coordinator_id');
    }
}
