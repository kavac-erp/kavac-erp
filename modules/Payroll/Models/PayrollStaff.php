<?php

namespace Modules\Payroll\Models;

use App\Models\Phone;
use App\Models\Gender;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PayrollAriRegister;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class      PayrollStaff
 * @brief      Datos de la información personal del trabajador
 *
 * Gestiona el modelo de datos del personal
 *
 * @author     William Páez <wpaez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaff extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    protected $table = "payroll_staffs";

    protected $with = ['payrollNationality', 'payrollFinancial', 'payrollGender', 'payrollBloodType', 'payrollDisability', 'payrollLicenseDegree', 'payrollEmployment', 'payrollStaffUniformSize', 'payrollSocioeconomic', 'payrollProfessional', 'payrollResponsibility'];

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = [
        'code', 'first_name', 'last_name', 'id_number', 'passport', 'email', 'birthdate',
        'emergency_contact', 'emergency_phone', 'address', 'has_disability', 'social_security',
        'has_driver_license', 'uniform_size', 'medical_history', 'payroll_license_degree_id',
        'payroll_blood_type_id', 'parish_id', 'payroll_nationality_id', 'payroll_gender_id',
        'payroll_disability_id'
    ];

    /**
     * PayrollPosition has many BudgetProjects.
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetProjects()
    {
        return (Module::has('Budget')) ? $this->hasMany(\Modules\Budget\Models\BudgetProject::class) : [];
    }

    /**
     * PayrollPosition has many BudgetCentralizedAction.
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function budgetCentralizedActions()
    {
        return (Module::has('Budget')) ? $this->hasMany(\Modules\Budget\Models\BudgetCentralizedAction::class) : [];
    }

    /**
     * Obtiene el nombre completo de la persona
     *
     * @author    Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     * @return    string    Nombre completo de la persona
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una parroquia
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un género
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollGender()
    {
        return $this->belongsTo(Gender::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una nacionalidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollNationality()
    {
        return $this->belongsTo(PayrollNationality::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información socioeconómica del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollSocioeconomic()
    {
        return $this->hasOne(PayrollSocioeconomic::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información profesional del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollProfessional()
    {
        return $this->hasOne(PayrollProfessional::class);
    }

    /**
     * Obtiene todos los número telefónicos asociados al trabajador
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function phones()
    {
        return $this->morphMany(Phone::class, 'phoneable');
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a una información laboral del mismo
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollEmployment()
    {
        return $this->hasOne(PayrollEmployment::class);
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada a un trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollStaffAccount()
    {
        return $this->hasOne(PayrollStaffAccount::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un grado de licencia de conducir
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollLicenseDegree()
    {
        return $this->belongsTo(PayrollLicenseDegree::class);
    }

    /**
     * Método que obtiene la información personal del trabajador asociada a un tipo de sangre
     *
     * @author    William Páez <wpaez@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollBloodType()
    {
        return $this->belongsTo(PayrollBloodType::class);
    }

    /**
     * Obtiene información de las opciones asignadas asociadas a un trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function payrollConceptAssignOptions()
    {
        return $this->morphMany(PayrollConceptAssignOption::class, 'assignable');
    }

    /**
     * Método que obtiene la información de las solicitudes de vacaciones asociadas al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollVacationRequests()
    {
        return $this->hasMany(PayrollVacationRequest::class);
    }

    /**
     * Método que obtiene la información de las solicitudes de adelanto de prestaciones asociadas al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollBenefitsRequests()
    {
        return $this->hasMany(PayrollBenefitsRequest::class);
    }

    /**
     * Método que obtiene la información de los registros de nómina asociados al trabajador
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffPayrolls()
    {
        return $this->hasMany(PayrollStaffPayroll::class);
    }

    /**
     * Método que obtiene la información de las solicitudes de permisos asociadas al trabajador
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollPermissionRequests()
    {
        return $this->hasMany(PayrollPermissionRequest::class);
    }

    /**
     * Método que obtiene la información de registros de cronogramas de trabajadores.
     *
     * @author    Yennifer Ramirez <yramirez@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function citizenServiceRegister()
    {
        return $this->hasMany(CitizenServiceRegister::class);
    }

    /**
     * Método que obtiene el dato personal del trabajador asociada a una discapacidad
     *
     * @author    William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollDisability()
    {
        return $this->belongsTo(PayrollDisability::class);
    }

    /**
     * Método que obtiene los bienes asignados a un trabajador
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * AssetAsignation
     */
    public function assetAsignation()
    {
        return (Module::has('Asset'))
            ? $this->hasMany(\Modules\Asset\Models\AssetAsignation::class) : [];
    }

    /**
     * Método que obtiene las tallas de uniforme asociados al trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>

     * @return    \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollStaffUniformSize()
    {
        return $this->hasMany(PayrollStaffUniformSize::class);
    }

    /**
     * Método que obtiene los datos financieros asignados a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     * @return Array|\Illuminate\Database\Eloquent\Relations\HasMany Objeto con el registro relacionado al modelo
     * PayrollFinancial
     */
    public function payrollFinancial()
    {
        return $this->hasMany(PayrollFinancial::class);
    }

    /**
     * saleGoodsToBeTraded
     */
    public function saleGoodsToBeTraded()
    {
        return $this->belongsToMany(\Modules\Sale\Models\SaleGoodsToBeTraded::class, 'sale_good_to_be_traded_payroll_staff');
    }

    /**
     * Registro ARI pertenciente al trabajador
     */
    public function payrollAriRegisters()
    {
        return $this->hasMany(PayrollAriRegister::class);
    }

    public function payrollResponsibility()
    {
        return $this->hasOne(PayrollResponsibility::class);
    }

}
