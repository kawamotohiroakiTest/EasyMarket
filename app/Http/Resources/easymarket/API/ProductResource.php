<?php

namespace App\Http\Resources\easymarket\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'image_url' => $this->present()->imageUrl(),
            'image_urls' => $this->present()->imageUrls(),
            'price' => $this->price,
            'deal' => new DealResource($this->deal),
        ];
    }
}
