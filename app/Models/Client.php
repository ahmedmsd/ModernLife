<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;
    protected $primaryKey = 'client_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'client_name',
        'client_type',
        'tax_number',
        'commercial_registration',
        'email',
        'phone',
        'secondary_phone',
        'address',
        'city',
        'country',
        'is_active',
        'credit_limit',
        'payment_terms',
        'created_by',
        'notes',
    ];

    public function contacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id', 'client_id');
    }
    public function city()
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function legacyProjects(): HasMany
    {
        return $this->hasMany(\App\Models\LegacyClientProject::class, 'client_id', 'client_id');
    }

}
