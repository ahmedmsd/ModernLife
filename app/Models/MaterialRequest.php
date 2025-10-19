<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialRequest extends Model
{
    protected $table = 'production_tasks_material_requests';

    protected $guarded = [];

    protected $fillable = [
        'task_id',
        'department_id',
        'requested_by',
        'requested_at',
        'status',
        'note',
        'po_file',
        'po_number',
        'estimated_cost',
        'expected_delivery_at',
        'provided_by',
        'provided_at',
        'actual_cost',
        'invoice_no',
        'invoice_date',
        'invoice_file',
        'cancelled_at',
        'cancelled_by',
        'approved_at',
        'approved_by',
        'parent_id',
    ];

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

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MaterialRequest::class, 'parent_id');
    }

    public function scopeRoots($q)
    {
        return $q->whereNull('parent_id');
    }

    public function getKindLabelAttribute(): string
    {
        return $this->parent_id ? 'تكميلي' : 'أساسي';
    }

    public function scopeOpen($q){
        return $q->where('status','requested');
    }
}
