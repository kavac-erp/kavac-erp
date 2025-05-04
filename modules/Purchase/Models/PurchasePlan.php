<?php

namespace Modules\Purchase\Models;

use App\Traits\ModelsTrait;
use Illuminate\Support\Facades\Date;
use Illuminate\Database\Eloquent\Model;
use Modules\Payroll\Models\PayrollStaff;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PurchasePlan
 * @brief Gestiona los planes de compra
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchasePlan extends Model implements Auditable
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
    protected $fillable = ['init_date', 'end_date', 'purchase_type_id', 'payroll_staff_id', 'active'];

    /**
     * Establece la relación con el proceso de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseProcess()
    {
        return $this->belongsTo(PurchaseProcess::class, 'purchase_processes_id');
    }

    /**
     * Establece la relación con el tipo de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseType()
    {
        return $this->belongsTo(PurchaseType::class);
    }

    /**
     * Establece la relación con el personal
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }


    /**
     * Establece la relación con el documento asociado a un plan de compras
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne
     */
    public function document()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    /**
     * Obtiene la fecha del registro para usar en el bloqueo del cierre de ejercicio
     *
     * @return Date
     */
    public function getDate()
    {
        return $this->init_date;
    }
}
