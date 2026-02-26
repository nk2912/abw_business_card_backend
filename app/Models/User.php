<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'otp',
        'otp_expires_at',
        'email_verified_at',
        'is_verified'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function businessCard()
    {
        return $this->hasOne(BusinessCard::class);
    }
    
    // Cards I have collected/added
    public function collectedCards()
    {
        return $this->belongsToMany(BusinessCard::class, 'user_cards', 'user_id', 'business_card_id')
                    ->withPivot(['is_friend', 'friend_status', 'tag'])
                    ->withTimestamps();
    }
}
