<?php

namespace App\Services\Tasks;

use App\Models\ProductionTask;
use App\Models\TaskHold;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TaskTimerService
{
    public static function start(ProductionTask $task, string $reason = 'manual'): void
    {
        if (! method_exists($task, 'timeEntries')) {
            return;
        }

        $hasOpen = $task->timeEntries()->whereNull('ended_at')->exists();
        if ($hasOpen) {
            return;
        }

        $nowUtc = Carbon::now('UTC');

        $task->timeEntries()->create([
            'started_at'   => $nowUtc,
            'ended_at'     => null,
            'duration_sec' => 0,
            'reason'       => $reason,
        ]);
    }

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

    public function startHold(ProductionTask $task, array $data): TaskHold
    {
        // \Illuminate\Support\Facades\Log::critical('START HOLD EXECUTION START', ['task_id' => $task->id, 'data' => $data]);
        return DB::transaction(function () use ($task, $data) {
            // أغلق أي تعليق مفتوح
            $task->holds()->whereNull('ended_at')->update(['ended_at' => now()]);

            // غيّر حالة المهمة إلى on_hold
            $task->forceFill(['status' => 'on_hold'])->save();

            try {
                $hold = $task->holds()->create([
                    'reason'          => $data['reason'] ?? null,
                    'type'            => $data['type'] ?? 'awaiting_dependency',
                    'related_task_id' => $data['related_task_id'] ?? null,
                    'started_at'      => $data['started_at'] ?? now(),
                    'created_by'      => $data['created_by'],
                    'note'            => $data['note'] ?? null,
                ]);
                \Illuminate\Support\Facades\Log::info('TaskTimerService: Hold created', ['hold_id' => $hold->id, 'task_id' => $task->id]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('TaskTimerService: Failed to create hold', ['error' => $e->getMessage()]);
                throw $e;
            }

            // task_log($task->id, 'hold_started', [...]);

            return $hold;
        });
    }

    public function endHold(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $open = $task->holds()->whereNull('ended_at')->latest('started_at')->first();
            if ($open) {
                $open->update(['ended_at' => now(), 'note' => $note]);
                // task_log($task->id, 'hold_ended', [...]);
            }

            if (in_array($task->status, ['on_hold','blocked'])) {
                $task->forceFill(['status' => 'in_progress'])->save();
            }
        });
    }

    public function totalHoldMinutes(ProductionTask $task): int
    {
        return (int) $task->holds()
            ->selectRaw('SUM(TIMESTAMPDIFF(MINUTE, started_at, COALESCE(ended_at, NOW()))) as mins')
            ->value('mins');
    }
}
