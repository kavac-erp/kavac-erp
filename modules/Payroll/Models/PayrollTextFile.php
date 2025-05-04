<?php

/** [descripción del namespace] */

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollTextFile
 * @brief [descripción detallada]
 *
 * [descripción corta]
 *
 * @author [autor de la clase] [correo del autor]
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollTextFile extends Model implements Auditable
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
    protected $fillable = ['file_name', 'file_number', 'payment_date', 'payroll_id', 'bank_account_id', 'payment_type_id'];

    /**
     * Tabla de base de datos
     */
    protected $table = 'payroll_text_files';

    /**
     *
     */
    protected $appends = ['payroll_name'];

    /**
     *
     */
    public function getPayrollNameAttribute()
    {

        $payroll_name = Payroll::find($this->payroll_id)->name;

        return $payroll_name;
    }
}
