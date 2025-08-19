<?php

namespace App\Services;

use App\Models\ProductionTask;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskTimerService
{
    /** افتح سجل وقت إن لم يوجد سجل مفتوح */
    public static function start(ProductionTask $task, string $reason = 'manual'): void
    {
        if (! method_exists($task, 'timeEntries')) {
            return;
        }

        // لو في سجل مفتوح لا تفتح واحد جديد
        $hasOpen = $task->timeEntries()->whereNull('ended_at')->exists();
        if ($hasOpen) {
            return;
        }

        $nowUtc = Carbon::now('UTC');

        $task->timeEntries()->create([
            'started_at'   => $nowUtc,     // دايمًا UTC
            'ended_at'     => null,
            'duration_sec' => 0,
            'reason'       => $reason,
        ]);
    }

    /** أغلق السجل المفتوح إن وجد — بحساب صفري كحد أدنى مهما كان */
    public static function stop(ProductionTask $task, string $reason = 'manual'): void
    {
        if (! method_exists($task, 'timeEntries')) {
            return;
        }

        $entry = $task->timeEntries()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $entry) {
            return;
        }

        $endedAtUtc = Carbon::now('UTC')->format('Y-m-d H:i:s');

        DB::table('production_tasks_time_entries')
            ->where('id', $entry->id)
            ->update([
                'ended_at'     => $endedAtUtc,
                'duration_sec' => DB::raw("GREATEST(TIMESTAMPDIFF(SECOND, started_at, '{$endedAtUtc}'), 0)"),
                'reason'       => $reason,
                'updated_at'   => $endedAtUtc,
            ]);
    }
}
