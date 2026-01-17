<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = ['name', 'description', 'address', 'latitude', 'longitude'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function firewalls()
    {
        return $this->hasMany(Firewall::class);
    }
}
