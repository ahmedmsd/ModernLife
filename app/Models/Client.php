<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
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
        'notes',
    ];

    public function contacts()
{
    return $this->hasMany(ClientContact::class, 'client_id', 'client_id');
}
}
