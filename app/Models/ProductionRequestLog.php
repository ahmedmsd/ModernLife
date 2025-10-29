<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRequestLog extends Model
{
    protected $fillable = [
        'production_request_id',
        'causer_id',
        'type',
        'data',
        'note',
        'happened_at',
    ];

    protected $casts = [
        'data'      => 'array',
        'happened_at' => 'datetime',
        'created_at'=> 'datetime',
        'updated_at'=> 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class, 'production_request_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'causer_id', 'id');
    }

}
