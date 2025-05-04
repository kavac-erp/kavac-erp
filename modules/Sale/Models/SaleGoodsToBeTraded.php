<?php

namespace Modules\Sale\Models;

use App\Models\Currency;
use App\Models\Department;
use App\Models\HistoryTax;
use App\Traits\ModelsTrait;
use App\Models\MeasurementUnit;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleGoodsToBeTraded
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleGoodsToBeTraded extends Model implements Auditable
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
        'name',
        'description',
        'unit_price',
        'define_attributes',
        'currency_id',
        'measurement_unit_id',
        'department_id',
        'history_tax_id',
        'sale_type_good_id'
    ];

    /**
     * Lista de relaciones a cargar con el modelo
     *
     * @var array $with
     */
    protected $with = ['department'];

    /**
     * Método que obtiene las formas de pago almacenadas en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Método que obtiene las unidades de medida almacenadas en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function measurementUnit()
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    /**
     * Método que obtiene las unidades / dependencias almacenadas en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Método que obtiene los porcentajes de impuestos almacenados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function historyTax()
    {
        return $this->belongsTo(HistoryTax::class);
    }

    /**
     * Método que obtiene la lista de trabajadores almacenados en el modulo payroll
     *
     * @author Juan Rosas <jrosas@cenditel.gob.ve> | <juan.rosasr01@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsToMany
     */
    public function payrollStaffs()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsToMany(\Modules\Payroll\Models\PayrollStaff::class, 'sale_good_to_be_traded_payroll_staff') : null;
    }

    /**
     * Método que obtiene los atributos de los bienes a comercializar
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function saleTypeGood()
    {
        return $this->belongsTo(SaleTypeGood::class);
    }

    /**
     * Método que obtiene los atributos de los bienes a comercializar
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleGoodsAttribute()
    {
        return $this->hasMany(SaleGoodsAttribute::class);
    }

    /**
     * Método que obtiene los registros del formualrio de solicitud de servicios
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleService()
    {
        return $this->hasMany(SaleService::class);
    }
}
