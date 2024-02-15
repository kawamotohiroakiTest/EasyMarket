<?php

namespace App\Http\Resources\easymarket\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Deal */
class DealForMyPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'is_purchasable' => $this->is_purchasable,
            'seller_info' => new SellerInfoResource($this->seller),
            'buyer_shipping_info' => new BuyerShippingInfoResource($this->buyer),
            'status' => $this->status,
            'deal_events' => DealEventResource::collection($this->whenLoaded('dealEvents')),
        ];
    }
}
