<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BusinessCardResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'            => $this->id,
            // If card has its own name (manual entry), use it. Otherwise fallback to User's name.
            'full_name'     => $this->name ?? $this->user?->name,
            'position'      => $this->position,
            'phones'        => $this->phones ?? [],
            'emails'        => $this->emails ?? [],
            'addresses'     => $this->addresses ?? [],
            'bio'           => $this->bio,
            'profile_image' => $this->profile_image,
            
            'card_type'     => $this->card_type,
            'qr_code_data'  => $this->qr_code_data,
            'social_links'  => $this->social_links ?? [],
            
            // Pivot data (if collected)
            'is_friend'     => $this->pivot?->is_friend ?? false,
            'friend_status' => $this->pivot?->friend_status ?? 'none',
            'tag'           => $this->pivot?->tag,

            'company'       => $this->whenLoaded('company', function () {
                return new CompanyResource($this->company);
            }),

            'user'          => $this->whenLoaded('user', function () {
                return [
                    'id'   => $this->user->id,
                    'name' => $this->user->name,
                ];
            }),
            'created_at'    => $this->created_at?->toDateTimeString(),
            'updated_at'    => $this->updated_at?->toDateTimeString(),
        ];
    }
}
