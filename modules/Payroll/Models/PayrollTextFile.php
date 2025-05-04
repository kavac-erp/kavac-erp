<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PayrollTextFile
 * @brief Gestiona la información, procesos, consultas y relaciones asociadas al modelo
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
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
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['file_name', 'file_number', 'payment_date', 'payroll_id', 'bank_account_id', 'payment_type_id'];

    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = 'payroll_text_files';

    /**
     * Lista de atributos personalizados a cargar con el modelo
     *
     * @var array $with
     */
    protected $appends = ['payroll_name'];

    /**
     * Obtiene el nombre de la nómina
     *
     * @return string
     */
    public function getPayrollNameAttribute()
    {

        $payroll_name = Payroll::find($this->payroll_id)->name;

        return $payroll_name;
    }
}
