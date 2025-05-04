<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Modules\Payroll\Models\PayrollStaff;

class PurchasePlan extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['init_date', 'end_date', 'purchase_type_id', 'payroll_staff_id', 'active'];

    /**
     * PurchasePlan belongs to PurchaseProcess.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseProcess()
    {
        return $this->belongsTo(PurchaseProcess::class, 'purchase_processes_id');
    }

    /**
     * PurchasePlan belongs to PurchaseType.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function purchaseType()
    {
        return $this->belongsTo(PurchaseType::class);
    }

    /**
     * PurchasePlan belongs to payrollStaff.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollStaff()
    {
        return $this->belongsTo(PayrollStaff::class);
    }


    /**
     * PurchasePlan has one document.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
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
