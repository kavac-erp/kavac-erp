<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class Municipality
 * @brief Datos de Municipios
 *
 * Gestiona el modelo de datos para los Municipios
 *
 * @property string|integer $id
 * @property string $name
 * @property string $code
 * @property string $estate_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Municipality extends Model implements Auditable
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
    protected $fillable = ['name', 'code', 'estate_id'];

    /**
     * Oculta los campos de fechas de creación, actualización y eliminación
     *
     * @var    array $hidden
     */
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * Arreglo con las relaciones a cargar por defecto
     *
     * @var    array $with
     */
    protected $with = ['estate'];

    /**
     * Método que obtiene el Estado de un Municipio
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function estate()
    {
        return $this->belongsTo(Estate::class);
    }

    /**
     * Método que obtiene las Parroquias asociadas a un Municipio
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parish()
    {
        return $this->hasMany(Parish::class);
    }

    /**
     * Método que obtiene las Organizaciones asociadas a un Municipio
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function institutions()
    {
        return $this->hasMany(Institution::class);
    }
}
