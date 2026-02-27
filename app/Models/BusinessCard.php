<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessCard extends Model
{
    use HasFactory, SoftDeletes;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
