<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AssetSupplierSpecialty
 * @brief Datos de las especialidades de proveedores
 *
 * Gestiona el modelo de datos para las especialidades de los proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierSpecialty extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla
     *
     * @var array $table
     */
    protected $table = 'purchase_supplier_specialties';

    /**
     * Lista de campos de tipo fecha
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de campos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = ['name', 'description'];

    /**
     * Obtiene la relación con los proveedores de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function assetSuppliers()
    {
        return $this->belongsToMany(AssetSupplier::class, 'purchase_specialty_supplier');
    }
}
