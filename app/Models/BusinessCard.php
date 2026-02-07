<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessCard extends Model
{
    protected $table = 'business_cards';

    protected $fillable = [
        'user_id',
        'full_name',
        'position',
        'company_name',
        'bio',
        'profile_image',
    ];

    /**
     * One Business Card belongs to one User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * One Business Card has many contact infos
     */
    public function contactInfos(): HasMany
    {
        return $this->hasMany(ContactInfo::class);
    }

    /**
     * One Business Card has many social links
     */
    public function socialLinks(): HasMany
    {
        return $this->hasMany(SocialLink::class);
    }
}
