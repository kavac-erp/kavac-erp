<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollAcknowledgment
 * @brief Datos de los reconocimientos
 *
 * Gestiona el modelo de reconocimientos
 *
 * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollAcknowledgment extends Model implements Auditable
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
    protected $fillable = ['payroll_professional_id'];

    /**
     * Método que obtiene el dato profesional asociado a un reconocimiento
     *
     * @author  William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollProfessional()
    {
        return $this->belongsTo(PayrollProfessional::class);
    }

    /**
     * Método que obtiene el reconocimiento asociado a muchos archivos de reconocimiento
     *
     * @author William Páez <wpaez@cenditel.gob.ve> | <paez.william8@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollAcknowledgmentFiles()
    {
        return $this->hasMany(PayrollAcknowledgmentFile::class);
    }
}
