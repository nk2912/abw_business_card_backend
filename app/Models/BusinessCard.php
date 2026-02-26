<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessCard extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'user_id',
        'company_id',
        'name',
        'position',
        'phones',
        'emails',
        'addresses',
        'bio',
        'profile_image',
        'card_type',
        'qr_code_data',
        'social_links',
    ];

    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
        'addresses' => 'array',
        'social_links' => 'array',
    ];
    protected $dates = ['deleted_at'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    
    // Cards that have added THIS card
    public function addedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_cards', 'business_card_id', 'user_id')
                    ->withPivot(['is_friend', 'friend_status', 'tag'])
                    ->withTimestamps();
    }
}
