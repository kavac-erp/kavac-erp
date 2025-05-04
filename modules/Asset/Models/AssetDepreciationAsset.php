<?php

namespace Modules\Asset\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use App\Traits\ModelsTrait;

/**
 * @class AssetDepreciationAsset
 * @brief Modelo para la gestión de depreciación de bienes
 *
 * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetDepreciationAsset extends Model implements Auditable
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
    protected $fillable = [
        'asset_depreciation_id',
        'asset_id',
        'asset_book_id',
        'amount',
        'depreciated_years',
        'days_remaining'
    ];

    /**
     * Método que obtiene la depreciación asociada al bien
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetDepreciation()
    {
        return $this->belongsTo(AssetDepreciation::class);
    }

    /**
     * Método que obtiene el bien asociado a la depreciación
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Método que obtiene el libro asociado al bien
     *
     * @return  \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assetBook()
    {
        return $this->belongsTo(AssetBook::class);
    }

    /**
     * Scope para buscar y filtrar datos de emisiones de pago
     *
     * @author Daniel Contreras <dcontreras@cenditel.gob.ve>
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query Objeto con la consulta
     * @param  string         $search    Cadena de texto a buscar
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query
            ->where('amount', 'LIKE', '%' . $search . '%')
            ->orWhereHas('asset', function ($query) use ($search) {
                $query
                    ->whereRaw("TO_CHAR(assets.acquisition_date, 'YYYY-MM-DD') LIKE (?)", '%' . strtoupper($search) . '%')
                    ->orWhereRaw('upper(asset_institutional_code) LIKE (?)', '%' . strtoupper($search) . '%')
                    ->orWhereHas('assetSpecificCategory', function ($query) use ($search) {
                        $query->whereRaw('upper(name) LIKE (?)', strtoupper($search) . '%');
                    });
            });
    }
}
