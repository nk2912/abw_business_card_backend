<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLink extends Model
{
    protected $fillable = [
        'business_card_id',
        'platform',
        'url',
    ];

    public function businessCard(): BelongsTo
    {
        return $this->belongsTo(BusinessCard::class);
    }
}
