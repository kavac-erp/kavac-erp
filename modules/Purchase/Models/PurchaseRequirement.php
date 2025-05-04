<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Composer\XdebugHandler\Status;
use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchaseRequirement
 * @brief Datos de los requerimientos de compras
 *
 * Gestiona el modelo de datos para los requerimientos de compra
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @property integer $id
 * @property integer $purchase_order_id
 * @property string $requirement_status
 * @property string $requirement_type
 * @property integer $purchase_base_budget_id
 * @property integer $prepared_by_id
 * @property integer $reviewed_by_id
 * @property integer $verified_by_id
 * @property integer $first_signature_id
 * @property integer $second_signature_id
 * @property string  $description
 * @property string $date
 * @property integer $contracting_department_id
 * @property integer $user_department_id
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseRequirement extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de relaciones a cargar por defecto con el modelo
     *
     * @var array $with
     */
    protected $with = ['purchaseSupplierObject', 'fiscalYear'];

    /**
     * Lista de attributos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = [
        'code',
        'description',
        'date',
        'fiscal_year_id',
        'contracting_department_id',
        'user_department_id',
        'purchase_supplier_object_id',
        'requirement_status',
        'purchase_base_budget_id',
        'institution_id',
        'prepared_by_id',
        'reviewed_by_id',
        'verified_by_id',
        'first_signature_id',
        'second_signature_id',
        'requirement_type',
    ];

    /**
     * Establece la relación con el año de ejercicio fiscal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fiscalYear()
    {
        return $this->belongsTo(FiscalYear::class);
    }

    /**
     * Establece la relación con el objeto del proveedor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseSupplierObject()
    {
        return $this->belongsTo(PurchaseSupplierObject::class);
    }

    /**
     * Establece la relación con la institución
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class) ?? null;
    }

    /**
     * Establece la relación con el departamento que solicita el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contratingDepartment()
    {
        return $this->belongsTo(Department::class, 'contracting_department_id') ?? null;
    }

    /**
     * Establece la relación con el usuario del departamento que solicita el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userDepartment()
    {
        return $this->belongsTo(Department::class, 'user_department_id') ?? null;
    }

    /**
     * Establece la relación con los items de los requerimientos
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRequirementItems()
    {
        return $this->hasMany(PurchaseRequirementItem::class);
    }

    /**
     * Establece la relación con el presupuesto base
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseBaseBudget()
    {
        return $this->belongsTo(PurchaseBaseBudget::class);
    }

    /**
     * Establece la relación con la orden de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    /**
     * Establece la relación con el empleado que prepara el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function preparedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'prepared_by_id'
                ) : null;
    }

    /**
     * Establece la relación con el empleado que revisa el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reviewedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'reviewed_by_id'
                ) : null;
    }

    /**
     * Establece la relación con el empleado que verifica el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'verified_by_id'
                ) : null;
    }

    /**
     * Estblece la relación con el empleado que firma, la primera vez, el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function firstSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'first_signature_id'
                ) : null;
    }

    /**
     * Establece la relación con el empleado que firma, la segunda vez, el requerimiento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function secondSignature()
    {
        return (Module::has('Payroll') && Module::isEnabled('Payroll'))
                ? $this->belongsTo(
                    \Modules\Payroll\Models\PayrollEmployment::class,
                    'second_signature_id'
                ) : null;
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date|string
     */
    public function getDate()
    {
        return $this->date;
    }

     /**
     * Scope para buscar y filtrar datos de requerimientos
     *
     * @param  \Illuminate\Database\Eloquent\Builder Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->whereRaw("TO_CHAR(date, 'DD/MM/YYYY') LIKE '%" . strtoupper($search) . "%'")
            ->orWhere('code', 'ilike', '%' . $search . '%')
            ->orWhere('description', 'ilike', '%' . $search . '%')
            ->orWhere('requirement_type', 'ilike', '%' . $search . '%')
            ->orWhere('requirement_status', 'ilike', '%' . $this->translateStatus($search) . '%')
            ->orWhereHas('contratingDepartment', function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%');
            })->orWhereHas('userDepartment', function ($query) use ($search) {
                $query->where('name', 'ilike', '%' . $search . '%');
            });
    }

    /**
     * Traduce el estatus del requerimiento
     *
     * @param string $search Estatus a traducir
     *
     * @return string
     */
    private function translateStatus($search)
    {
        switch ($search) {
            case 'WAIT':
                $status = 'WAIT';
                break;
            case 'PROCESSED':
                $status = 'PROCESADO';
                break;
            case 'BOUGHT':
                $status = 'COMPRADO';
                break;
            case 'EN ESPERA':
                $status = 'WAIT';
                break;
            case 'PROCESADO':
                $status = 'PROCESSED';
                break;
            case 'COMPRADO':
                $status =  'BOUGHT';
                break;
            default:
                $status = 'unknown';
                break;
        }

        return $status;
    }
}
