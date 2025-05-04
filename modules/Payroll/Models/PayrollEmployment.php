<?php

namespace Modules\Payroll\Models;

use App\Models\Profile;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PayrollEmployment
 * @brief Datos laborales del trabajador
 *
 * Gestiona el modelo de datos laborales
 *
 * @author William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollEmployment extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos de relacion consultados automáticamente
     *
     * @var array $with
     */
    protected $with = [
        'payrollPositionType',
        'payrollPositions',
        'payrollCoordination',
        'department',
        'payrollStaffType',
        'payrollInactivityType',
        'payrollContractType',
        'payrollPreviousJob'
    ];

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
        'active',
        'years_apn',
        'start_date',
        'end_date',
        'institution_email',
        'function_description',
        'payroll_inactivity_type_id',
        'payroll_position_type_id',
        'payroll_coordination_id',
        'department_id',
        'payroll_staff_type_id',
        'payroll_contract_type_id',
        'payroll_staff_id',
        'worksheet_code'
    ];

    /**
     * Atributos personalizados a agregar en las consultas
     *
     * @var array $appends
     */
    protected $appends = [
        'startDateApn',
        'payrollPosition',
        'payroll_position_id'
    ];

    /**
     * Metodo que obtiene la fecha de inicio en la Administración Pública Nacional (APN)
     *
     * @return Date|string
     */
    public function getStartDateApnAttribute()
    {
        if (!is_numeric($this->years_apn) && $this->years_apn != '') {
            $years = explode(' ', $this->years_apn);
            $totalDate = strtotime('-' . ($years[1] ?? 0) . ' years ' . '-' . ($years[3] ?? 0) . ' months ' . '-' . ($years[5] ?? 0) . ' days', strtotime($this->start_date));

            return date('Y-m-d', $totalDate);
        } else {
            return $this->start_date;
        }
    }

    /**
     * Método que obtiene el dato laboral del trabajador que está asociada a muchas organizaciones
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollOrganizations()
    {
        return $this->hasMany(PayrollOrganization::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un dato personal del mismo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un tipo de inactividad
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollInactivityType()
    {
        return $this->belongsTo(PayrollInactivityType::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un tipo de cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPositionType()
    {
        return $this->belongsTo(PayrollPositionType::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getPayrollPositionAttribute()
    {
        $responsability = PayrollResponsibility::query()
            ->where('payroll_staff_id', $this->payroll_staff_id)
            ->with('payrollPosition')
            ->first();

        $position = $this->payrollPositions()->where([
            'payroll_employment_id' => $this->id,
            'active' => true
        ])->first();

        $position = $responsability ? $responsability->payrollPosition : ($position ? $position : null);

        return $position;
    }

    /**
     * Método que obtiene el id del cargo asociado a un trabajador
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getPayrollPositionIdAttribute()
    {
        $responsability = PayrollResponsibility::query()
            ->where('payroll_staff_id', $this->payroll_staff_id)
            ->first();

        $position = $this->payrollPositions()->where([
            'payroll_employment_id' => $this->id,
            'active' => true
        ])->first();

        $positionId = $responsability ? $responsability->payroll_position_id : ($position ? $position->id : null);

        return $positionId;
    }

    /**
     * Método que establece una relación de "muchos a muchos" (belongsToMany)
     * entre el modelo actual y el modelo PayrollPosition.
     *
     * @author Ing. Argenis Osorio <aosorio@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany|\Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollPositions()
    {
        if (Schema::hasTable('payroll_employment_payroll_position')) {
            return $this->belongsToMany(
                PayrollPosition::class,
                'payroll_employment_payroll_position',
                'payroll_employment_id',
                'payroll_position_id'
            );
        }
        return $this->belongsTo(PayrollPosition::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un cargo
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollCoordination()
    {
        return $this->belongsTo(PayrollCoordination::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un departamento
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un tipo de personal
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaffType()
    {
        return $this->belongsTo(PayrollStaffType::class);
    }

    /**
     * Método que obtiene el dato laboral del trabajador asociado a un tipo de contrato
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollContractType()
    {
        return $this->belongsTo(PayrollContractType::class);
    }

    /**
     * Método que obtiene los trabajos anteriores asociados al trabajador
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollPreviousJob()
    {
        return $this->hasMany(PayrollPreviousJob::class);
    }

    /**
     * Método que obtiene los datos del perfil asociado al trabajador
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class, 'id', 'employee_id');
    }

    /**
     * Método que obtiene los trabajos anteriores asociados al trabajador
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function purchaseDirectHires()
    {
        return (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) ? $this->hasMany(\Modules\Purchase\Models\PurchaseDirectHire::class) : null;
    }

    /**
     * Método que obtiene los trabajos anteriores asociados al trabajador
     *
     * @author  Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function purchaseBaseBudget()
    {
        return (
            Module::has('Purchase') && Module::isEnabled('Purchase')
        ) ? $this->hasMany(\Modules\Purchase\Models\PurchaseBaseBudget::class) : null;
    }

    /**
     * Scope para buscar y filtrar datos de personal
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query Objeto con la consulta
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query)
    {
        return $query->when(request('query'), function ($query) {
            $search = request('query');
            $query->whereHas('payrollStaff', function ($query) use ($search) {
                $query->where('first_name', 'ilike', '%' . $search . '%')
                    ->orWhere('last_name', 'ilike', '%' . $search . '%')
                    ->orWhere('id_number', 'ilike', '%' . $search . '%')
                    ->orWhere('email', 'ilike', '%' . $search . '%');
            });
        });
    }
}
