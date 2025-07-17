<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\ProductionRequestStatus;

class ProductionRequest extends Model
{


    protected $casts = [
        'status' => ProductionRequestStatus::class,
    ];
    protected $fillable = [
        'project_name',
        'project_description',
        'client_id',
        'showroom_id',
        'agreement_file',
        'status',
        'created_by',
        'submitted_at',
    ];

    public function client()
    {
        return $this->belongsTo(\App\Models\Client::class, 'client_id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductionRequestFile::class);
    }

    public function logs()
    {
        return $this->hasMany(\App\Models\ProductionRequestLog::class)->latest('action_at');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
