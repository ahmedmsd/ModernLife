<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class, 'manager_id' ,'employee_id');
    }
}
