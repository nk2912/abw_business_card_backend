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
        'position',
        'phones',
        'emails',
        'addresses',
        'bio',
        'profile_image',
    ];

    protected $casts = [
        'phones' => 'array',
        'emails' => 'array',
        'addresses' => 'array',
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
}
