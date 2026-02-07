<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedCard extends Model
{
    protected $fillable = [
        'user_id',
        'business_card_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function businessCard()
    {
        return $this->belongsTo(BusinessCard::class);
    }
}
