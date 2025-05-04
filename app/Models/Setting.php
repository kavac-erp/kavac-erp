<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Setting
 * @brief Datos de Configuraciones
 *
 * Gestiona el modelo de datos para las configuraciones generales del sistema
 *
 * @property boolean $support
 * @property boolean $chat
 * @property boolean $notify
 * @property boolean $report_banner
 * @property boolean $multi_institution
 * @property boolean $digital_sign
 * @property boolean $active
 * @property boolean $multi_warehouse
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Setting extends Model implements Auditable
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
        'support', 'chat', 'notify', 'report_banner', 'multi_institution',
        'digital_sign', 'active', 'multi_warehouse'
    ];
}
