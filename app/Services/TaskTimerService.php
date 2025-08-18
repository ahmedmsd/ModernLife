<?php
// app/Services/TaskTimerService.php
namespace App\Services;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Models\TaskTimeEntry;
use Illuminate\Support\Facades\Auth;

class TaskTimerService
{
    public static function start(ProductionTask $task, ?string $reason = null): TaskTimeEntry
    {
        $open = $task->times()->whereNull('ended_at')->first();
        if ($open) return $open;

        $entry = $task->times()->create([
            'started_by' => Auth::id(),
            'started_at' => now(),
            'reason'     => $reason,
        ]);

        $task->logs()->create([
            'type'        => 'timer_started',
            'data'        => ['reason' => $reason],
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        return $entry;
    }

    public static function stop(ProductionTask $task, ?string $reason = null): ?TaskTimeEntry
    {
        $open = $task->times()->whereNull('ended_at')->first();
        if (! $open) return null;

        $open->ended_at    = now();
        $open->duration_sec = $open->ended_at->diffInSeconds($open->started_at);
        $open->reason      = $reason ?? $open->reason;
        $open->save();

        $task->logs()->create([
            'type'        => 'timer_stopped',
            'data'        => ['reason' => $reason, 'duration_sec' => $open->duration_sec],
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        return $open;
    }
}
