<?php

// app/Models/Project.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\ProjectFile;

class Project extends Model
{
    protected $fillable = [
        'production_request_id',
        'client_id',
        'project_name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    public function productionRequest(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(ProductionTask::class);
    }
    public function files(): Project|HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'id');
    }

    public function showroom(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Showroom::class, 'showroom_id', 'id');
    }


}
