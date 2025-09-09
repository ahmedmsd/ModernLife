<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class LegacyClientProject extends Model
{
    protected $fillable = [
        'client_id', 'project_name', 'start_date', 'end_date',
        'services', 'details', 'reference_code',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'services'   => 'array',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(LegacyClientProjectFile::class, 'legacy_project_id');
    }
}
