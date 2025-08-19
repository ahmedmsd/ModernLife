<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $table = 'production_tasks_material_requests';

    protected $fillable = [
        'task_id','department_id','requested_by','requested_at',
        'status','po_number','note','provided_by','provided_at',
    ];

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(ProductionTask::class, 'task_id'); }
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(Department::class, 'department_id','dept_id'); }

    public function scopeOpen($q){ return $q->where('status','requested'); }
}
