<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'industry',
        'business_type',
        'description',
        'address',
        'website',
        'phone',
        'email',
        'created_by',
        'updated_by',
    ];
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function socials()
    {
        return $this->hasMany(CompanySocial::class);
    }

    public function businessCards()
    {
        return $this->hasMany(BusinessCard::class);
    }
}
