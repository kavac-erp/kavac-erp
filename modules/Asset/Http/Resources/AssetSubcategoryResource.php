<?php

namespace Modules\Asset\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @class AssetAsignationResource
 * @brief Clase que maneja el recurso para las subcategorías de bienes
 *
 * @author Fabián Palmera <fpalmera@cenditel.gob.ve>
 *
 * @license
 *     [LICENCIA DE SOFTWARE CENDITEL](http://conocimientolibre.cenditel.gob.ve/licencia-de-software-v-1-3/)
 */
class AssetSubcategoryResource extends JsonResource
{
    /**
     * Transforma el recurso en un array.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        $common_fields = [
          'id' => $this->id,

          'code' => [
            'label' => "Código",
            'name' => $this->code,
          ],

          'name' => [
            'label' => "Nombre",
            'name' => $this->name,
          ],

          'asset_category' => [
            'id' => $this->asset_category_id,
            'label' => "Categoría principal",
            'name' => $this->assetCategory->name,
          ],

          'asset_type' => [
            'id' => $this->asset_type_id,
            'label' => "Tipo",
            'name' => $this->assetCategory->assetType->name,
          ]

        ];

        return $common_fields;
    }
}