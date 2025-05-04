<?php

namespace Modules\Sale\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Modules\Sale\Models\SaleListSubservices;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleTechnicalProposal
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleTechnicalProposal extends Model implements Auditable
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
    protected $fillable = ['duration', 'frecuency_id', 'sale_service_id',
                            'sale_list_subservices', 'payroll_staffs', 'status'];

    /**
     * Lista de atributos que deben ser asignados a tipos nativos.
     *
     * @var array $casts
     */
    protected $casts = [
        'sale_list_subservices' => 'json',
        'payroll_staffs' => 'json',
    ];

    /**
     * Lista de atributos personalizados obtenidos por defecto
     *
     * @var array $appends
     */
    protected $appends = [
        'staffs', 'list_subservices'
    ];

    /**
     * Atributo que devuelve informacion de los trabajadores con sus bienes asignados
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return   array
     */
    public function getStaffsAttribute()
    {
        $data = [];
        if (!empty($this->payroll_staffs) && Module::has('Payroll') && Module::isEnabled('Payroll')) {
            foreach ($this->payroll_staffs as $key => $value) {
                $data[$key] = ($value != null)
                        ? \Modules\Payroll\Models\PayrollStaff::where('id', $value)
                                                ->with(['assetAsignation' => function ($query) {
                                                            $query->with(['assetAsignationAssets' => function ($q) {
                                                                            $q->with('asset');
                                                            }]);
                                                }])->get()
                        : null;
            }
        }
        return $data;
    }

    /**
     * Atributo que devuelve informacion de los bienes a comercializar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array
     */
    public function getListSubservicesAttribute()
    {
        $data = [];
        if (!empty($this->sale_list_subservices)) {
            foreach ($this->sale_list_subservices as $key => $value) {
                $data[$key] = ($value != null)
                        ? SaleListSubservices::where('id', $value)->get()
                        : null;
            }
        }
        return $data;
    }

    /**
     * Método que obtiene las solicitudes de servicios almacenados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleService()
    {
        return $this->belongsTo(SaleService::class);
    }

    /**
     * Método que obtiene las solicitudes de servicios almacenados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function frecuency()
    {
        return $this->belongsTo(SaleSettingFrecuency::class);
    }

    /**
     * Método que obtiene la lista de trabajadores almacenados en el modulo payroll
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return (
            Module::has('Payroll') && Module::isEnabled('Payroll')
        ) ? $this->belongsTo(\Modules\Payroll\Models\PayrollStaff::class) : null;
    }

    /**
     * Método que obtiene la lista de subservicios almacenados en el sistema
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleListSubservices()
    {
        return $this->belongsTo(SaleListSubservices::class);
    }

    /**
     * Método que obtiene la lista de especificaciones para la propuesta técnica
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleProposalSpecification()
    {
        return $this->hasMany(SaleProposalSpecification::class);
    }

    /**
     * Método que obtiene la lista de requerimientos para la propuesta técnica
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleProposalRequirement()
    {
        return $this->hasMany(SaleProposalRequirement::class);
    }

    /**
     * Método que obtiene los registros almacenados en el modelo SaleGanttDiagram
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleGanttDiagram()
    {
        return $this->hasMany(SaleGanttDiagram::class);
    }
}
