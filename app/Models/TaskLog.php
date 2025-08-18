<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'production_tasks_log';

    protected $fillable = ['task_id', 'type', 'data', 'causer_id', 'happened_at'];

    protected $casts = [
        'data'        => 'array',     // مهم حتى تعمل data['to'] و data['from']
        'happened_at' => 'datetime',
    ];

    public function task()  { return $this->belongsTo(ProductionTask::class, 'task_id'); }
    public function causer(){ return $this->belongsTo(User::class, 'causer_id'); }
}
