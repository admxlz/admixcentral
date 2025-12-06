<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'url',
        'auth_method',
        'api_key',
        'api_secret',
        'api_token',
        'description',
        'is_dirty',
        'netgate_id',
    ];

    protected $casts = [
        'api_key' => 'encrypted',
        'api_secret' => 'encrypted',
        'api_token' => 'encrypted',
    ];

    public function getRouteKeyName()
    {
        return 'netgate_id';
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
