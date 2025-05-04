<?php

declare(strict_types=1);

namespace Modules\Payroll\Models;

use App\Models\DocumentStatus;
use App\Models\Institution;
use App\Traits\ModelsTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @class PayrollGuardScheme
 * @brief Datos de esquema de guardias
 *
 * Gestiona el modelo de esquema de guardias
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollGuardScheme extends Model implements Auditable
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
        'from_date', 'to_date', 'payroll_supervised_group_id', 'institution_id', 'data_source'
    ];

    /**
     * Lista de atributos personalizados obtenidos por defecto
     *
     * @var array $appends
     */
    protected $appends = [
        'document_status', 'confirmed_periods'
    ];

    /**
     * Lista de atributos que se deben convertir a tipos de pago
     *
     * @var array $casts
     */
    protected $casts = [
        'data_source' => 'json'
    ];

    /**
     * Obtiene información del estatus del documento
     *
     * @return array
     */
    public function getDocumentStatusAttribute()
    {
        $documentDefault = DocumentStatus::query()
            ->whereAction('EL')
            ->first();
        $documentInProcess = DocumentStatus::query()
            ->whereAction('PR')
            ->first();
        $documentClose = DocumentStatus::query()
            ->whereAction('CE')
            ->first();
        $periods = $this->payrollGuardSchemePeriods()
            ->orderBy('to_date')
            ->get();

        if (count($periods) > 0) {
            $lastPeriod = $periods->last();
            return ($lastPeriod->to_date < $this->to_date || 'CE' != $lastPeriod->documentStatus?->action)
                ? [
                    'name' => $documentInProcess->name,
                    'description' => $documentInProcess->description,
                    'color' => $documentInProcess->color,
                    'action' => $documentInProcess->action,
                    'pending_period' => $periods->first(function ($item) {
                        return 'PR' == $item?->documentStatus?->action;
                    }),
                    'last_period' => $lastPeriod
                ]
                : [
                    'name' => $documentClose->name,
                    'description' => $documentClose->description,
                    'color' => $documentClose->color,
                    'action' => $documentClose->action,
                    'pending_period' => $periods->first(function ($item) {
                        return 'PR' == $item?->documentStatus?->action;
                    }),
                    'last_period' => $lastPeriod
                ];
        }
        return [
            'name' => $documentDefault->name,
            'description' => $documentDefault->description,
            'color' => $documentDefault->color,
            'action' => $documentDefault->action,
            'pendingPeriod' => null,
            'lastPeriod' => null
        ];
    }

    /**
     * Obtiene información de los periodos confirmados
     *
     * @return array
     */
    public function getConfirmedPeriodsAttribute()
    {
        $periods = $this->payrollGuardSchemePeriods()
            ->where('document_status_id', DocumentStatus::query()
                ->whereAction('CE')
                ->first()?->id)
            ->get();

        return $periods->filter(function ($periodo) {
                return $periodo->observations === 'Confirmación total';
        })->flatMap(function ($periodo) {
            $fromDate = Carbon::parse($periodo->from_date);
            $toDate = Carbon::parse($periodo->to_date);

            return collect(Carbon::parse($fromDate)->range($toDate))->map(function ($date) {
                return ucfirst($date->locale('es')->monthName) . '-' . $date->day;
            });
        })->toArray();
    }

    /**
     * Método que obtiene la información del grupo de supervisado
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSupervisedGroup(): BelongsTo
    {
        return $this->belongsTo(PayrollSupervisedGroup::class);
    }

    /**
     * Método que obtiene la información de la institutción asociada al esquema de guardia
     *
     * @author    Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Obtiene la relación con los periodos de esquemas de guardia
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollGuardSchemePeriods(): HasMany
    {
        return $this->hasMany(PayrollGuardSchemePeriod::class);
    }
}
