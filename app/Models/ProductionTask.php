<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Concerns\HasStatusScopes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductionTask extends Model
{
    use HasStatusScopes , HasFactory;
    protected $table = 'production_tasks';
    protected $primaryKey = 'id';
    protected $afterCommit = true;
    protected $guarded = [];

    protected $fillable = [
        'project_id','department_id','estimated_cost','assigned_to_employee_id',
        'file_path','due_date','status','notes','assigned_at','received_at',
        'completed_at','closed_at','planned_start_at','planned_end_at',
        'planned_install_at','client_receipt','current_owner_role',
        'current_owner_user_id','sent_to_owner_at','received_by_owner_at',
    ];

    protected $casts = [
        'due_date'             => 'datetime',
        'assigned_at'          => 'datetime',
        'received_at'          => 'datetime',
        'completed_at'         => 'datetime',
        'closed_at'            => 'datetime',
        'planned_start_at'     => 'datetime',
        'planned_end_at'       => 'datetime',
        'planned_install_at'   => 'datetime',
        'sent_to_owner_at'     => 'datetime',
        'received_by_owner_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id', 'id');
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

    public function times(): HasMany
    {
        return $this->hasMany(TaskTimeEntry::class, 'task_id')->latest('started_at');
    }

    public function materialRequests(): HasMany
    {
        return $this->hasMany(\App\Models\MaterialRequest::class, 'task_id');
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(\App\Models\TaskTimeEntry::class, 'task_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(\App\Models\TaskComment::class, 'task_id', 'id')->latest();
    }

    public function getHasOpenMaterialRequestAttribute(): bool
    {
        return $this->materialRequests()->open()->exists();
    }


    public function currentOwnerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'current_owner_user_id');
    }
}
