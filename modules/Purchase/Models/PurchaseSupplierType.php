<?php

namespace Modules\Purchase\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class PurchaseSupplierType
 * @brief Datos de los tipos de proveedores
 *
 * Gestiona el modelo de datos para los tipos de proveedores
 *
 * @author Ing. Roldan Vargas <rvargas@cenditel.gob.ve> | <roldandvg@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class PurchaseSupplierType extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     *
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos del modelo
     *
     * @var array $fillable
     */
    protected $fillable = ['name'];

    /**
     * Establece la relación con los proveedores
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseSuppliers()
    {
        return $this->hasMany(PurchaseSupplier::class);
    }

    /**
     * Establece la relación con los requerimientos de compra
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function purchaseRequirements()
    {
        return $this->hasMany(PurchaseRequirement::class);
    }
}
