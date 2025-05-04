<?php

namespace Modules\Asset\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Collection;
use Modules\Asset\Repositories\AssetParametersRepository;
use Illuminate\Support\Str;

/**
 * @class AssetReportResource
 * @brief Clase que maneja el recurso para los reportes de depreciaciones
 *
 * Se usa para devolver un array que contiene un objeto. la llave "acumulated_depreciation" contiene los datos para la tabla de depreciacion acomulada, y la llave "table_depreciation" contiene los datos para la tabla de depreciacion
 *
 * @author <ndrojo@cenditel.gob.ve> | <rojonatanael99@gmail.com>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetReportResource extends ResourceCollection
{
    /**
     * El emboltorio "records" que se debe aplicar
     *
     * @var string
     */
    public static $wrap = 'records';

    /**
     * Transforma el recurso en un array
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): Collection
    {
        return $this->collection->transform(function ($asset) {
            $total_amount = 0;
            $table_depreciation = [];
            foreach ($asset->assetDepreciationAsset as $item) {
                $total_amount += (float) $item['amount'];
                array_push($table_depreciation, [
                    'asset_institutional_code' => $asset->asset_institutional_code,
                    'description' => isset($asset->asset_details['description']) ? $asset->asset_details['description'] : 'No hay descripción',
                    'year' =>  $item['assetDepreciation']['year'],
                    'depreciation_year' =>   currency_format((float) $item['amount']),
                    'acummulated_depreciation' => currency_format($total_amount),
                    'asset_book_value' =>  currency_format((float) $item->assetBook->amount)
                ]);
            }
            usort($table_depreciation, function ($a, $b) {
                return $a['year'] <=> $b['year'];
            });

            return [
                'acumulated_depreciation' => [
                    [
                        'asset_institutional_code' => $asset->asset_institutional_code,
                        'asset_subcategory' => $asset->assetSubcategory->name,
                        'asset_specific_category' => $asset->assetSpecificCategory->name,
                        'acquisition_date' => $asset->acquisition_date,
                        'acquisition_value' => $asset->asset_details['acquisition_value'],
                        'depresciation_years' => $asset->asset_details['depresciation_years'],
                        'acumulated_depreciation' => $table_depreciation[0]['acummulated_depreciation'],
                    ],
                ],
                'table_depreciation' => $table_depreciation,
            ];
        });
    }
}
