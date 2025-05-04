<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AssetSupplierType
 * @brief Datos de los tipos de proveedores
 *
 * Gestiona el modelo de datos para los tipos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSupplierType extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Nombre de la tabla
     *
     * @var array $table
     */
    protected $table = 'purchase_supplier_types';

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
    protected $fillable = ['name'];

    /**
     * Obtiene la relación con los proveedor de bienes
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetSuppliers()
    {
        return $this->hasMany(AssetSupplier::class);
    }
}
