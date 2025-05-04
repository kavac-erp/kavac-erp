<?php

namespace Modules\Payroll\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Payroll\Models\PayrollEmployment;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class PayrollEmploymentNoAppends
 * @brief Modelo de datos para gestión de personal
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PayrollEmploymentNoAppends extends PayrollEmployment
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
    protected $fillable = [];

    /**
     * Lista de relaciones a cargar por defecto en las consultas
     *
     * @var array $with
     */
    protected $with = [];

    /**
     * Lista de atributos personalizados a devolver en las consultas
     *
     * @var array $appends
     */
    protected $appends = [];

    /**
     * Nombre de la tabla en la base de datos
     *
     * @var string $table
     */
    protected $table = "payroll_employments";
}
