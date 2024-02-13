<?php

namespace App\Http\Resources\easymarket\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\User */
class MeResource extends JsonResource
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
            'email' => $this->email,
            'name' => $this->name,
            'postal_code' => $this->postal_code,
            'address' => $this->address,
            'tel' => $this->tel,
            'nickname' => $this->nickname,
            'profile_image_url' => $this->present()->profileImageUrl,
            'description' => $this->description,
        ];
    }
}