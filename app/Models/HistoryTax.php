<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class HistoryTax
 * @brief Datos de histórico de impuestos
 *
 * Gestiona el modelo de datos para los históricos de impuestos
 *
 * @property  string  $operation_date
 * @property  string|float  $percentage
 * @property  string  $tax_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class HistoryTax extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

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
        'operation_date', 'percentage', 'tax_id'
    ];

    /**
     * Método que obtiene el Impuesto asociado
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return object Objeto con el registro relacionado al modelo Tax
     */
    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }
}
