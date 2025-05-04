<?php

namespace Modules\Sale\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class SaleSettingProduct
 * @brief Datos de productos
 *
 * Gestiona el modelo de los productos
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 * @license<a href='http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/'>
 *              LICENCIA DE SOFTWARE CENDITEL
 *          </a>
 */
class SaleSettingProduct extends Model implements Auditable
{
    use SoftDeletes;
    use AuditableTrait;
    use ModelsTrait;

    /**
     * Lista de atributos para la gestión de fechas
     * @var array $dates
     */
    protected $dates = ['deleted_at'];

    /**
     * Lista de atributos que pueden ser asignados masivamente
     * @var array $fillable
     */
    protected $fillable = ['name', 'description', 'attributes', 'sale_setting_product_type_id'];


    /**
     * Método que obtiene la lista de atributos de un producto
     *
     * @author PHD. Juan Vizcarrondo <jvizcarrondo@cenditel.gob.ve> | <juanvizcarrondo@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\hasMany Objeto con el registro relacionado al modelo
     * SaleSettingProductAttribute
     */
    public function saleSettingProductAttribute()
    {
        return $this->hasMany(SaleSettingProductAttribute::class);
    }

    /**
     * Método que obtiene el tipo de producto al que pertenece un producto
     *
     * @author Francisco J. P. Ruiz <fjpenya@cenditel.gob.ve | javierrupe19@gmail.com>
     * @return \Illuminate\Database\Eloquent\Relations\hasMany Objeto con el registro relacionado al modelo
     * SaleSettingProductType
     */
    public function saleSettingProductType()
    {
        return $this->belongsTo(SaleSettingProductType::class);
    }
}
