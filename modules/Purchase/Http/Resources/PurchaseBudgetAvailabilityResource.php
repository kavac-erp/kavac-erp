<?php

namespace Modules\Purchase\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseBudgetAvailabilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $key = array_key_last($this->resource->relatable->toArray());
        $common_fields = [ 
            'id' => $this->resource->id,
            'code' => $this->resource->relatable[$key]['purchaseRequirementItem']['purchaseRequirement']['code'] ?? '',
            'description' => $this->resource->relatable[$key]['purchaseRequirementItem']['purchaseRequirement']['description'] ?? '',
            'currency_name' => $this->currency->name,
            'available' => $this->availability, 
            'module' => 'Purchase'
        ];

        return $common_fields;
    }
}