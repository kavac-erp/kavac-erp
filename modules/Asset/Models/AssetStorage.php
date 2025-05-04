<?php

namespace Modules\Asset\Models;

use App\Models\Parish;
use App\Traits\ModelsTrait;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable as AuditableTrait;

/**
 * @class AssetStorage
 * @brief Modelo que gestiona los almacenes de los bienes
 *
 * Gestión de almacenes de bienes
 *
 * @author Oscar González <ojgonzalez@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetStorage extends Model implements Auditable
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
    protected $fillable = ['name', 'active', 'address','parish_id'];

    /**
     * Método que obtiene la parroquia donde esta ubicado el depósito
     *
     * @author Oscar González <ojgonzalez@cenditel.gob.ve> | <xxmaestroyixx@gmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parish()
    {
        return $this->belongsTo(Parish::class);
    }

    /**
     * Método que obtiene las instituciones que gestionan el almacén
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetInstitutionStorages()
    {
        return $this->hasMany(AssetInstitutionStorage::class);
    }
}
