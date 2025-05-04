<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollTimeSheetParameter
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTimeSheetParameter extends Model implements Auditable
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
    protected $fillable = ['code', 'name', 'description'];

    /**
     * Método que obtiene la información de los parámetros de hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollParameterTimeSheetParameters()
    {
        return $this->hasMany(PayrollParameterTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de los tipos de pago a los que afectan los parámetros de hoja de tiempo
     *
     * @author    Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return    \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function payrollPaymentTypeTimeSheetParameters()
    {
        return $this->hasMany(PayrollPaymentTypeTimeSheetParameter::class);
    }

    /**
     * Método que obtiene la información de la hoja de tiempo asociada a los parámetros de hoja de tiempo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function payrollTimeSheet()
    {
        return $this->hasOne(PayrollTimeSheet::class);
    }

    /**
     * Obtiene la relación con las hojas de tiempo
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollTimeSheets()
    {
        return $this->hasMany(PayrollTimeSheet::class);
    }

    /**
     * Obtiene la relación con las hojas de tiempo pendientes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollTimeSheetsPending()
    {
        return $this->hasMany(PayrollTimeSheetPending::class);
    }
}
