<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AssetSupplierBranch
 * @brief Datos de las ramas de los proveedores
 *
 * Gestiona el modelo de datos para las ramas de los proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierBranch extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $table = 'purchase_supplier_branches';

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
        return $this->belongsToMany(AssetSupplier::class, 'purchase_branch_supplier');
    }
}
