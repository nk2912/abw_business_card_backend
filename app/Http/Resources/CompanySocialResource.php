<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanySocialResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'platform' => $this->platform,
            'url' => $this->url,
        ];
    }
}
