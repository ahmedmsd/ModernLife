<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Enums\ProductionRequestStatus;

class ProductionRequest extends Model
{
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

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(Showroom::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProductionRequestFile::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ProductionRequestLog::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'production_request_id');
    }
}
