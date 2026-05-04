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
        'city_id',
        'country_id',
        'is_active',
        'credit_limit',
        'payment_terms',
        'created_by',
        'notes',
        'zoho_account_id',
        'zoho_contact_id',
    ];

    public function contacts()
    {
        return $this->hasMany(ClientContact::class, 'client_id', 'client_id');
    }
    public function city()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function country()
    {
        return $this->belongsTo(\App\Models\Country::class, 'country_id');
    }

    public function legacyProjects(): HasMany
    {
        return $this->hasMany(\App\Models\LegacyClientProject::class, 'client_id', 'client_id');
    }

}
