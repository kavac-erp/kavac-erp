<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * @class PayrollStaffAccount
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollStaffAccount extends Model implements Auditable
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
    protected $fillable = ['payroll_staff_id', 'accounting_account_id'];

    /**
     * Método que obtiene la información personal del trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Método que obtiene la información de la cuenta contable asociada al trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccount()
    {
        return (
            Module::has('Accounting') && Module::isEnabled('Accounting')
        ) ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class) : null;
    }

    /**
     * Scope para buscar y filtrar datos
     *
     * @param \Illuminate\Database\Eloquent\Builder $query Objeto con la consulta
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query)
    {
        return $query->when(request('query'), function ($query) {
            $search = request('query');
            $query->where(function ($query) use ($search) {
                $query->where('id_number', 'ilike', "%$search%")
                    ->orWhere('first_name', 'ilike', "%$search%")
                    ->orWhere('last_name', 'ilike', "%$search%");
            });
        });
    }
}
