<?php

namespace Modules\Payroll\Models;

use App\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @class PayrollSuspensionVacationRequest
 * @brief Modelo que representa una suspension de vacaciones
 *
 * Modelo que representa una suspension de vacaciones
 *
 * @author Natanael Rojo <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollSuspensionVacationRequest extends Model implements Auditable
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
        'status',
        'enjoyed_days',
        'pending_days',
        'suspension_reason',
        'date_request',
        'payroll_vacation_request_id',
    ];

    /**
     * Obtiene la relación con documentos
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function document(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    /**
     * Obtiene la relación con solicitudes de vacaciones
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollVacationRequest(): BelongsTo
    {
        return $this->belongsTo(PayrollVacationRequest::class);
    }
}
