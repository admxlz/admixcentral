<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Firewall extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'url',
        'api_key',
        'api_secret',
        'description',
        'is_dirty',
        'netgate_id',
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
