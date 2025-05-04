<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Payroll\Models\PayrollStaff;
use Nwidart\Modules\Facades\Module;

/**
 * @class PayrollFinancial
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollFinancial extends Model implements Auditable
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
    protected $fillable = ['payroll_staff_id', 'finance_bank_id', 'finance_account_type_id', 'payroll_account_number'];

    /**
     * Lista de relaciones a cargar por defecto
     *
     * @var array $with
     */
    protected $with = ['financeAccountType'];

    /**
     * Método que obtiene el trabajador al que se le asigna información financiera
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }

    /**
     * Método que obtiene el banco asignado a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeBank()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->belongsTo(\Modules\Finance\Models\FinanceBank::class) : null;
    }

    /**
     * Método que obtiene el tipo de cuenta asignado a un trabajador
     *
     * @author Pedro Buitrago <pbuitrago@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financeAccountType()
    {
        return (
            Module::has('Finance') && Module::isEnabled('Finance')
        ) ? $this->belongsTo(\Modules\Finance\Models\FinanceAccountType::class) : null;
    }

    /**
     * Scope para buscar y filtrar datos de personal
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query Objeto con la consulta
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query)
    {
        return $query->when(request()->has('query'), function ($query) {
            $search = request('query');
            $query->whereHas('payrollStaff', function ($query) use ($search) {
                $query->where('first_name', 'ilike', '%' . $search . '%')
                    ->orWhere('last_name', 'ilike', '%' . $search . '%')
                    ->orWhere('id_number', 'ilike', '%' . $search . '%');
            });
        });
    }
}
