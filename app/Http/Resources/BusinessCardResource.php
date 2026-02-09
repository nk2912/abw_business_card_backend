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
            'full_name'     => $this->user?->name,
            'position'      => $this->position,
            'phones'        => $this->phones ?? [],
            'emails'        => $this->emails ?? [],
            'addresses'     => $this->addresses ?? [],
            'bio'           => $this->bio,
            'profile_image' => $this->profile_image,
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
