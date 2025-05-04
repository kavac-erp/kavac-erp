<?php

namespace Modules\Finance\Models;

use App\Models\Deduction;
use App\Traits\ModelsTrait;
use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class FinancePaymentDeduction
 * @brief Modelo para el pago de deducciones
 *
 * Gestiona el modelo de datos para el pago de deducciones
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class FinancePaymentDeduction extends Model implements Auditable
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
        'amount',
        'mor',
        'deduction_id',
        'finance_payment_execute_id',
        'deductionable_type',
        'deductionable_id',
        'document_status_id',
        'deductions_ids',
        'deducted_at',
    ];

    /**
     * Obtiene la relación con la ejecución de pago
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financePaymentExecute()
    {
        return $this->belongsTo(FinancePaymentExecute::class);
    }

    /**
     * Obtiene la relación con la deducción
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deduction()
    {
        return $this->belongsTo(Deduction::class);
    }

    /**
     * Obtiene la relación morfológica con la deducción
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function deductionable()
    {
        return $this->morphTo();
    }

    /**
     * Obtiene la relación con el estatus del documento
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class, 'document_status_id');
    }
}
