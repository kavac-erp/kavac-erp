<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class DocumentStatus
 * @brief Datos de estatus de documentos
 *
 * Gestiona el modelo de datos para los estados de documentos
 *
 * @property  int    $id
 * @property  string $name
 * @property  string $description
 * @property  string $color
 * @property  string $action
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class DocumentStatus extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla a usar en la base de datos
     *
     * @var string $table
     */
    protected $table = 'document_status';

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
    protected $fillable = ['name', 'description', 'color', 'action'];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Ejecuta acciones generales del modelo
     *
     * @author Ing. Roldan Vargas <roldandvg at gmail.com> | <rvargas at cenditel.gob.ve>
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::setCacheEvents('document_status');
    }

    /**
     * Obtiene el estatus del documento según los filtros indicados
     *
     * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @param  object       $query    Objeto que contiene la consulta del modelo
     * @param  string       $status   Estatus por el cual filtrar la información
     * @param  string|null  $operator Operador por el cual se va a filtrar los datos, el valor por defecto es '='
     *
     * @return DocumentStatus         Consulta filtrada
     */
    public function scopeGetStatus($query, $status, $operator = null)
    {
        // Define el operador por el cual filtrar la consulta, si no se indica el valor por defecto es '='
        $operator = (!is_null($operator)) ? $operator : "=";

        return $query->where('action', $operator, $status)->first();
    }
}
