<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class,'department_id' , 'dept_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_to_employee_id');
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductionTask::class);
    }
}
