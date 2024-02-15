<?php

namespace App\Http\Resources\easymarket\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\DealEvent */
class DealEventResource extends JsonResource
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
            'actor_type' => $this->actor_type,
            'event_type' => $this->event_type,
            'created_at' => $this->created_at,
        ];
    }
}
