<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\ModelsTrait;

/**
 * @class AssetStatus
 * @brief Datos de los Estados de uso de un bien
 *
 * Gestiona el modelo de datos de los estados de uso de los bienes
 *
 * @author Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetStatus extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use ModelsTrait;

    /**
     * Nombre de la tabla a usar en la base de datos
     *
     * @var string $table
     */
    protected $table = 'asset_status';

    /**
     * Lista de atributos que pueden ser asignados masivamente
     *
     * @var array $fillable
     */
    protected $fillable = ['name'];

    /**
     * Método que obtiene los bienes asociados a un estatus de uso
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    /**
     * Método que se ejecuta al instanciar el modelo
     *
     * @return void
     */
    public static function boot()
    {
        parent::boot();

        self::deleting(function ($model) {
            if (has_data_in_foreign_key($model->id, 'asset_status_id')) {
                throw new \Exception('No se puede eliminar este registro debido a que tiene bienes asociados');
            };
        });
    }
}
