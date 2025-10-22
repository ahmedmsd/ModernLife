<?php

// app/Models/TaskHold.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskHold extends Model
{
    protected $table = 'production_task_holds';

    public const TYPE_AWAITING_MATERIALS = 'awaiting_materials';
    public const TYPE_AWAITING_DEPENDENCY = 'awaiting_dependency';
    public const TYPE_CLIENT_FEEDBACK = 'client_feedback';
    public const TYPE_OTHER = 'other';

    protected $fillable = [
        'task_id',
        'reason',
        'type',
        'related_task_id',
        'started_at',
        'ended_at',
        'created_by',
        'approved_by',
        'note',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    /* ===================== العلاقات ===================== */

    public function task(): BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id');
    }

    public function relatedTask(): BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'related_task_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }


    public function scopeOpen($query)
    {
        return $query->whereNull('ended_at');
    }

    public function scopeClosed($query)
    {
        return $query->whereNotNull('ended_at');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /* ===================== Accessors/Helpers ===================== */

    public function getIsOpenAttribute(): bool
    {
        return $this->ended_at === null;
    }

    public function getDurationMinutesAttribute(): int
    {
        $end = $this->ended_at ?? now();
        return $this->started_at ? $this->started_at->diffInMinutes($end) : 0;
    }
}
