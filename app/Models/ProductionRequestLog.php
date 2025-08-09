<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionRequestLog extends Model
{
    protected $fillable = [
        'production_request_id',
        'user_id',
        'action',
        'note',
        'action_at',
    ];
    protected $casts = [
        'action_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function productionRequest()
    {
        return $this->belongsTo(\App\Models\ProductionRequest::class);
    }
}
