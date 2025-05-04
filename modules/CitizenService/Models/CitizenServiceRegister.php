<?php

namespace Modules\CitizenService\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class CitizenServiceIndicator
 * @brief Gestiona la información de los registros en la oficina de atención al ciudadano
 *
 * @author Ing. Yenifer Ramírez <yramirez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class CitizenServiceRegister extends Model implements Auditable
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
        'date_register', 'payroll_staff_id', 'team_name',
        'activities', 'start_date', 'end_date', 'email', 'percent', 'code'
    ];

    /**
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['payrollStaff'];

    /**
     * Lista de atributos con el tipo de dato a retornar
     *
     * @var array
     */
    protected $casts = [
        'team_name' => 'array',
    ];

    /**
     * Método que obtiene la información del trabajador asociado a un registro en ingresar un cronograma.
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : [];
    }

    /**
     * Establece la relación con la solicitud de servicio
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function codeCitizenServiceRequest()
    {
        return $this->belongsTo(CitizenServiceRequest::class);
    }
}
