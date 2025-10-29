<?php

// app/Models/Project.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasManyThrough, MorphMany};
use Illuminate\Support\Carbon;
use App\Models\Concerns\HasStatusScopes;

class Project extends Model
{
    use HasStatusScopes;
    protected $table = 'projects';
    protected $primaryKey = 'id';

    protected $fillable = [
        'production_request_id',
        'client_id',
        'showroom_id',
        'project_name',
        'description',
        'start_date',
        'end_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function productionRequest(): BelongsTo
    {
        return $this->belongsTo(ProductionRequest::class, 'production_request_id', 'id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }



    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function tasks(): HasMany
    {
        // production_tasks.project_id -> projects.id
        return $this->hasMany(ProductionTask::class, 'project_id', 'id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ProjectFile::class, 'project_id', 'id');
    }


    public function materialRequests(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialRequest::class,
            ProductionTask::class,
            'project_id',
            'task_id',
            'id',
            'id'
        );
    }

    /* ===================== ملحقات عملية ===================== */

    public function getDueDateAttribute(): ?Carbon
    {
        return $this->end_date;
    }

    public function overdueTasks(): HasMany
    {
        return $this->tasks()
            ->whereNotIn('status', ['completed', 'closed'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now());
    }

    public function openMaterialRequests(): HasManyThrough
    {
        return $this->materialRequests()->whereNull('provided_at');
    }

    /* ===================== Scopes اختيارية ===================== */

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    public function scopeWithBasics($query)
    {
        return $query->with(['client', 'showroom']);
    }
}
