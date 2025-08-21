<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRequestLog extends Model
{
    protected $fillable = [
        'production_request_id',
        'user_id',
        'type',           // created | transition | status_changed | received | rejected | deleted | ...
        'data',           // JSON
        'note',
        'action_at',
    ];

    protected $casts = [
        'data'      => 'array',
        'action_at' => 'datetime',
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
}
