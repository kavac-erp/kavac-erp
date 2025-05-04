<?php

namespace App\Models;

use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Document
 * @brief Datos de los documentos
 *
 * Gestiona el modelo de datos para los documentos
 *
 * @property  string  $code
 * @property  string  $file
 * @property  string  $url
 * @property  string  $signs
 * @property  string  $archive_number
 * @property  string  $extension
 * @property  string  $physical_support
 * @property  string  $digital_file
 * @property  string  $digital_support_original
 * @property  string  $digital_support_signed
 * @property  string  $documentable_type
 * @property  string  $documentable_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Document extends Model implements Auditable
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
        'code', 'file', 'url', 'signs', 'archive_number', 'extension', 'physical_support', 'digital_file',
        'digital_support_original', 'digital_support_signed', 'documentable_type', 'documentable_id'
    ];

    /**
     * Obtiene el modelo con el que se relaciona el documento
     *
     * @author  William Páez <wpaez@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function documentable()
    {
        return $this->morphTo();
    }
}
