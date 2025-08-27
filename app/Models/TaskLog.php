<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'production_tasks_log';

    protected $fillable = ['task_id', 'type', 'data', 'causer_id', 'happened_at'];

    protected $casts = [
        'data'        => 'array',
        'happened_at' => 'datetime',
    ];
    protected function typeLabel(): Attribute
    {
        return Attribute::get(function () {
            $key = "tasks.logs.types.{$this->type}";
            return __($key) !== $key
                ? __($key)
                : str($this->type)->replace('_', ' ')->title();
        });
    }

    protected function statusLabel(): array|\Illuminate\Contracts\Translation\Translator|\Illuminate\Foundation\Application|string|null
    {
        $status = data_get($this->data, 'status');
        if (!$status) return null;
        $key = "tasks.statuses.$status";
        return __($key) !== $key ? __($key) : $status;
    }
    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductionTask::class, 'task_id', 'id');
    }
    public function causer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    { return $this->belongsTo(User::class, 'causer_id'); }
}
