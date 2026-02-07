<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactInfo extends Model
{
    protected $fillable = ['business_card_id','type','value'];

    public function businessCard()
    {
        return $this->belongsTo(BusinessCard::class);
    }
}
