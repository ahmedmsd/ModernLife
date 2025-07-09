<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Showroom extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city_id',
        'phone',
        'email',
        'manager_id',
    ];

    public function city()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function manager()
    {
        return $this->belongsTo(\App\Models\Employee::class, 'manager_id');
    }
}
