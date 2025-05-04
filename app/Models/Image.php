<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class Image
 * @brief Datos de Imágenes
 *
 * Gestiona el modelo de datos para las imágenes
 *
 * @property  string|integer  $id
 * @property  string  $file
 * @property  string  $url
 * @property  integer $max_width
 * @property  integer $max_height
 * @property  integer $min_width
 * @property  integer $min_height
 * @property  string  $imageable_type
 * @property  string  $imageable_id
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class Image extends Model implements Auditable
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
        'file', 'url', 'max_width', 'max_height', 'min_width', 'min_height', 'imageable_type', 'imageable_id'
    ];

    /**
     * Método que obtiene los logos de las organizaciones
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function institutionLogos()
    {
        return $this->hasMany(Institution::class, 'logo_id');
    }

    /**
     * Método que obtiene los banners de las organizaciones
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function institutionBanners()
    {
        return $this->hasMany(Institution::class, 'banner_id');
    }

    /**
     * Método que obtiene el perfil de una imagen
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function profile()
    {
        return $this->hasMany(Institution::class);
    }

    /**
     * Image morphs to models in imageable_type
     *
     * @author  Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function imageable()
    {
        return $this->morphTo();
    }
}
