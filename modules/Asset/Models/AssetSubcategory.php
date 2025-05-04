<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;
use Nwidart\Modules\Facades\Module;

/**
 * Gestiona el modelo de datos de las subcategorias de un bien
 *
 * @class AssetSubcategory
 *
 * @brief Datos de las subcategorias de un bien
 *
 * @author  Henry Paredes <hparedes@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSubcategory extends Model implements Auditable
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
    protected $fillable = ['code', 'name', 'asset_category_id', 'accounting_account_debit', 'accounting_account_asset'];

    /**
     * Lista de atributos personalizados obtenidos por defecto
     *
     * @var array $appends
     */
    protected $appends = [
        'asset_type_id'
    ];

    /**
     * Método que obtiene el valor asociado al campo asset_type_id
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve> | <henryp2804@gmail.com>
     *
     * @return object    Objeto con las propiedades registrados
     */
    public function getAssetTypeIdAttribute()
    {
        $data = '';
        if (isset($this->assetCategory)) {
            $data = $this->assetCategory->assetType->id;
        }
        return $data;
    }

    /**
     * Método que obtiene la categoria asociada a la subcategoria del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetCategory()
    {
        return $this->belongsTo(AssetCategory::class);
    }

    /**
     * Método que obtiene las categorias especificas asociadas a la subcategoria del bien
     *
     * @author Henry Paredes <hparedes@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assetSpecificCategories()
    {
        return $this->hasMany(AssetSpecificCategory::class);
    }

    /**
     * Método que obtiene los bienes asociados a la subcategoria del bien
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
     * Método que obtiene la cuenta contable de gastos asociada a la subcategoria del bien
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccountDebit()
    {
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        return $exist_accounting
               ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class, 'accounting_account_debit')
               : [];
    }

    /**
     * Método que obtiene la cuenta contable de depreciación acumulada asociada a la subcategoria del bien
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accountingAccountAsset()
    {
        $exist_accounting = Module::has('Accounting') && Module::isEnabled('Accounting');
        return $exist_accounting
               ? $this->belongsTo(\Modules\Accounting\Models\AccountingAccount::class, 'accounting_account_asset')
               : [];
    }
}
