<?php

declare(strict_types=1);

namespace Modules\Payroll\Models;

use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @class PayrollGuardScheme
 *
 * @brief Datos de esquema de guardias
 *
 * Gestiona el modelo de esquema de guardias
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
final class PayrollGuardSchemePeriod extends Model implements Auditable
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
     * @var array
     */
    protected $fillable = [
        'from_date', 'to_date', 'observations', 'payroll_guard_scheme_id', 'document_status_id',
    ];

    /**
     * Get the DocumentStatus that owns the PayrollGuardSchemePeriod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus(): BelongsTo
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Get the payrollGuardScheme that owns the PayrollGuardSchemePeriod
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollGuardScheme(): BelongsTo
    {
        return $this->belongsTo(PayrollGuardScheme::class);
    }

    public function getData(): array
    {
        $months = [
            'Enero' => '01',
            'Febrero' => '02',
            'Marzo' => '03',
            'Abril' => '04',
            'Mayo' => '05',
            'Junio' => '06',
            'Julio' => '07',
            'Agosto' => '08',
            'Septiembre' => '09',
            'Octubre' => '10',
            'Noviembre' => '11',
            'Diciembre' => '12',
        ];
        list($fromYear, $fromMonth, $fromDay) = explode('-', $this->from_date);
        list($toYear, $toMonth, $toDay) = explode('-', $this->to_date);
        return collect($this->payrollGuardScheme->data_source)->reduce(function ($carry, $field, $index) use ($months, $fromYear, $toYear) {
            list($staffId, $month, $day) = explode('-', $index);
            foreach ($field as $value) {
                $fromDate = $fromYear.'-'.$months[$month].'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
                $toDate = $toYear.'-'.$months[$month].'-'.str_pad($day, 2, '0', STR_PAD_LEFT);

                if ($fromYear != $toYear) {
                    if ($fromDate >= $this->from_date && $toDate <= $this->to_date) {
                        $carry[$fromDate][$value['text'].'-'.$staffId] = [
                            'count' => $value['count'],
                            'confirmed' => "Confirmación total" == $this->observations ? true : false,
                        ];
                    } else if ($toDate >= $this->from_date && $toDate <= $this->to_date) {
                        $carry[$toDate][$value['text'].'-'.$staffId] = [
                            'count' => $value['count'],
                            'confirmed' => "Confirmación total" == $this->observations ? true : false,
                        ];
                    }
                } else {
                    if ($fromDate >= $this->from_date && $toDate <= $this->to_date) {
                        $carry[$fromDate][$value['text'].'-'.$staffId] = [
                            'count' => $value['count'],
                            'confirmed' => "Confirmación total" == $this->observations ? true : false,
                        ];
                    }
                }
            }

            return $carry;
        }, []);
    }
}
