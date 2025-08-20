<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductionTask extends Model
{
    protected $fillable = [
        'project_id',
        'department_id',
        'assigned_budget',
        'assigned_to_employee_id',
        'file_path',
        'due_date',
        'status',
        'notes',
        'assigned_at',
        'received_at',
        'completed_at',
        'closed_at',
    ];

    protected $casts = [
        'received_at'  => 'datetime',
        'due_date'    => 'datetime',
        'assigned_at' => 'datetime',
        'completed_at'=> 'datetime',
        'closed_at'=> 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'dept_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class, 'task_id', 'id')
            ->orderBy('happened_at')
            ->orderBy('created_at');
    }

    public function times()
    {
        return $this->hasMany(TaskTimeEntry::class, 'task_id')->latest('started_at');
    }

    public function materialRequests(): ProductionTask|HasMany
    {
        return $this->hasMany(\App\Models\MaterialRequest::class, 'task_id');
    }
    public function getHasOpenMaterialRequestAttribute(): bool
    {
        return $this->materialRequests()->open()->exists();
    }

    public function timeEntries(): ProductionTask|HasMany
    {
        return $this->hasMany(\App\Models\TaskTimeEntry::class, 'task_id');
    }

    public function getActiveSecondsAttribute(): int
    {
        return $this->times->sum(function ($t) {
            return $t->duration_sec !== null
                ? $t->duration_sec
                : now()->diffInSeconds($t->started_at);
        });
    }

    public function getDelaySecondsAttribute(): int
    {
        if (! $this->due_date) return 0;
        $end = $this->completed_at ?? now();
        return $end->gt($this->due_date) ? $end->diffInSeconds($this->due_date) : 0;
    }

    public function getStatusLabelAttribute(): string
    {
        $map = [
            'pending'       => 'قيد الإنشاء',
            'assigned'      => 'مُسندة',
            'acknowledged'  => 'تأكيد الاستلام',
            'in_progress'   => 'قيد التنفيذ',
            'blocked'       => 'متوقفة مؤقتًا',
            'under_review'  => 'قيد المراجعة',
            'rework'        => 'إعادة عمل',
            'completed'     => 'مكتملة',
            'closed'        => 'مغلقة',
            'cancelled'     => 'ملغاة',
            'draft'         => 'مسودة', // إن وُجدت قديمًا
        ];

        return $map[$this->status] ?? $this->status;
    }
}
