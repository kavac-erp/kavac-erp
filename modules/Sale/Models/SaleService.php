<?php

namespace Modules\Sale\Models;

use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Modules\Sale\Models\SaleGoodsToBeTraded;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class SaleService
 * @brief Datos de solicitudes de servicios
 *
 * Gestiona el modelo de las solicitudes de servicios
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class SaleService extends Model implements Auditable
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
    protected $fillable = ['code', 'organization', 'description',
        'resume', 'status', 'sale_goods_to_be_traded', 'sale_client_id', 'payroll_staff_id'];

    /**
     * Lista de atributos que deben ser asignados a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'sale_goods_to_be_traded' => 'json'
    ];

    /**
     * Lista de atributos personalizados obtenidos por defecto
     *
     * @var array $appends
     */
    protected $appends = [
        'sale_goods'
    ];

    /**
     * Atributo que devuelve informacion de los bienes a comercializar
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    array
     */
    public function getSaleGoodsAttribute()
    {
        $data = [];
        if (!empty($this->sale_goods_to_be_traded)) {
            foreach ($this->sale_goods_to_be_traded as $key => $value) {
                $data[$key] = ($value != null)
                        ? SaleGoodsToBeTraded::where('id', $value)
                                                ->with(['department', 'payrollStaffs' => function ($query) {
                                                        $query->with('phones');
                                                }])->first()
                        : null;
            }
        }
        return $data;
    }

    /**
     * Método que obtiene los requerimientos de la solicitud de servicios
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleServiceRequirement()
    {
        return $this->hasMany(SaleServiceRequirement::class);
    }

    /**
     * Método que obtiene los registros del modelo de clientes
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleClient()
    {
        return $this->belongsTo(SaleClient::class);
    }

    /**
     * Método que obtiene los registros del modelo de bienes a comercializar
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function saleGoodsToBeTraded()
    {
        return $this->belongsTo(SaleGoodsToBeTraded::class);
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
     * Método que obtiene los requerimientos de la solicitud de servicios
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function saleTechnicalProposal()
    {
        return $this->hasMany(SaleTechnicalProposal::class);
    }
}
