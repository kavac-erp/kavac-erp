<?php

namespace Modules\Payroll\Models;

use App\Models\DocumentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollTimeSheetPending
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetPending extends Model implements Auditable
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
     * Lista de atributos con el tipo de dato a retornar
     *
     * @var array $casts
     */
    protected $casts = [
        'time_sheet_data' => 'array',
        'time_sheet_columns' => 'array',
    ];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'from_date', 'to_date', 'payroll_supervised_group_id', 'payroll_time_sheet_parameter_id',
        'time_sheet_data', 'time_sheet_columns', 'document_status_id', 'observations', 'institution_id',
    ];

    /**
     * Método que obtiene la información personal del trabajador
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollSupervisedGroup()
    {
        return $this->belongsTo(PayrollSupervisedGroup::class);
    }

    /**
     * Método que obtiene la información de los parámetros de la hoja de tiempo pendiente
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollTimeSheetParameters()
    {
        return $this->belongsTo(PayrollTimeSheetParameter::class);
    }

    /**
     * Método que el estatus de la hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function documentStatus()
    {
        return $this->belongsTo(DocumentStatus::class);
    }

    /**
     * Método que obtiene la institución de la hoja de tiempo pendiente
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
