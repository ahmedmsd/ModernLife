<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaterialRequest extends Model
{
    protected $table = 'production_tasks_material_requests';

    protected $guarded = [];


//    protected $fillable = [
//        'task_id','department_id','requested_by','requested_at',
//        'status','po_number','note','provided_by','provided_at',
//    ];
    protected $casts = [
        'requested_at'         => 'datetime',
        'expected_delivery_at' => 'datetime',
        'provided_at'          => 'datetime',
        'estimated_cost'       => 'decimal:2',
        'actual_cost'           => 'decimal:2',
    ];

    public function requestedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by', 'id');
    }

    public function providedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'provided_by', 'id');
    }
    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id');
    }
    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id','dept_id');
    }

    public function scopeOpen($q){
        return $q->where('status','requested');
    }
}
