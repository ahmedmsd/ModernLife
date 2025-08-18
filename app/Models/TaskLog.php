<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'production_tasks_log';

    protected $fillable = ['task_id', 'type', 'data', 'causer_id', 'happened_at'];

    protected $casts = [
        'data'        => 'array',
        'happened_at' => 'datetime',
    ];

    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id', 'id');
    }
    public function causer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(User::class, 'causer_id'); }
}
