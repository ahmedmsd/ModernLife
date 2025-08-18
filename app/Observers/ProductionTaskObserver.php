<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskAssignedInAppNotification;
use App\Models\TaskLog;
use App\Services\TaskTimerService;
class ProductionTaskObserver
{
    public function created(ProductionTask $task): void
    {
        if ($task->assigned_to_employee_id) {
            if (blank($task->assigned_at)) {
                $task->forceFill(['assigned_at' => now()])->saveQuietly();
            }

            if ($task->employee?->routeNotificationForMail(null)) {
                $task->employee->notify(new TaskAssignedNotification($task, false));
            }

            if ($user = $task->employee?->user) {
                $user->notify(new TaskAssignedInAppNotification($task, false));
            }

        }

        $task->logs()->create([
            'type' => 'created',
            'data' => ['status' => $task->status, 'department_id' => $task->department_id],
            'causer_id' => auth()->id(),
            'happened_at' => now(),
        ]);

        if ($task->status?->value === 'in_progress' || $task->status === 'in_progress') {
            TaskTimerService::start($task, 'auto_on_create');
        }

    }

    public function updated(ProductionTask $task): void
    {
        if ($task->wasChanged('assigned_to_employee_id')) {
            $task->forceFill(['assigned_at' => now()])->saveQuietly();

            if ($task->assigned_to_employee_id && $task->employee?->routeNotificationForMail(null)) {
                $task->employee->notify(new TaskAssignedNotification($task, true));
            }

            if ($user = $task->employee?->user) {
                $user->notify(new TaskAssignedInAppNotification($task, true));
            }

        }

        $original = $task->getOriginal();

        // تغيير الحالة
        if (array_key_exists('status', $task->getDirty())) {
            $from = $original['status'] ?? null;
            $to = $task->status;

            $task->logs()->create([
                'type' => 'status_changed',
                'data' => compact('from', 'to'),
                'causer_id' => auth()->id(),
                'happened_at' => now(),
            ]);

            // إدارة المؤقّت حسب الحالة
            if ($to === 'in_progress' && $from !== 'in_progress') {
                TaskTimerService::start($task, "status_to_in_progress");
            }

            if (in_array($to, ['blocked', 'under_review', 'rework', 'completed', 'closed', 'cancelled']) && $from === 'in_progress') {
                TaskTimerService::stop($task, "status_to_{$to}");
            }

            if (in_array($to, ['in_progress']) && in_array($from, ['blocked', 'rework'])) {
                TaskTimerService::start($task, "resume_from_{$from}");
            }

            if (in_array($to, ['completed', 'closed'])) {
                $task->forceFill(['completed_at' => now()])->saveQuietly();
            }
        }

        // تغيير الإسناد
        if (array_key_exists('assigned_to_employee_id', $task->getDirty())) {
            $from = $original['assigned_to_employee_id'] ?? null;
            $to = $task->assigned_to_employee_id;

            $task->logs()->create([
                'type' => 'assigned_changed',
                'data' => compact('from', 'to'),
                'causer_id' => auth()->id(),
                'happened_at' => now(),
            ]);
        }

        // تغيير تاريخ التسليم
        if (array_key_exists('due_date', $task->getDirty())) {
            $from = $original['due_date'] ?? null;
            $to = $task->due_date;

            $task->logs()->create([
                'type' => 'due_changed',
                'data' => ['from' => $from, 'to' => optional($to)?->toDateString()],
                'causer_id' => auth()->id(),
                'happened_at' => now(),
            ]);
        }
    }
}
