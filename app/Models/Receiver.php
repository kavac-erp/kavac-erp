<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Receiver
 * @brief Datos de receptores de procesos
 *
 * Gestiona el modelo de datos para los receptores de procesos dentro del sistema
 *
 * @property  string|integer $id
 * @property  string $group
 * @property  string $description
 * @property  string $receiverable_type
 * @property  string $receiverable_id
 * @property  string $associateable_type
 * @property  string $associateable_id
 * @property  string $text
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Receiver extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = [
        'group', 'description', 'receiverable_type', 'receiverable_id', 'associateable_type', 'associateable_id'
    ];

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Los modelos registrados en el campo receiverable_type hacen referencia a
     * un receptor de un proceso o beneficiario, por ejemplo un proveedor
     * registrado en el módulo de compras.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function receiverable()
    {
        return $this->morphTo();
    }

    /**
     * Los modelos registrados en el campo associateable_type.
     * hacen referencia a información requerida que si necesite
     * asociar al receptor de un proceso o beneficiario, por ejemplo,
     * una cuenta contable del módulo de contabilidad.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function associateable()
    {
        return $this->morphTo();
    }

    /**
     * Relación morphMany con el modelo Source, donde se
     * almacenan las fuentes de donde se guarda al receptor de un proceso
     * o beneficiario por ejemplo, un beneficiario registrado
     * desde un compromiso en el módulo de presupuesto.
     *
     * @return \Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function sources()
    {
        return $this->hasMany(Source::class);
    }
}
