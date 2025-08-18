<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskTimeEntry extends Model
{
        protected $table = 'production_tasks_time_entries';

    protected $fillable = ['task_id','started_by','started_at','ended_at','duration_sec','reason'];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    public function task()     { return $this->belongsTo(ProductionTask::class, 'task_id'); }
    public function starter()  { return $this->belongsTo(User::class, 'started_by'); }
}

