<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskAssignedInAppNotification;
use App\Services\TaskTimerService;
use Illuminate\Support\Facades\Schema;

class ProductionTaskObserver
{
    /** حوّل الحالة إلى نص مهما كان نوعها (Enum/String/Null) */
    protected function normStatus(null|string|\BackedEnum $s): ?string
    {
        return $s instanceof \BackedEnum ? $s->value : ($s === null ? null : (string) $s);
    }

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

        $statusNow = $this->normStatus($task->status);

        $task->logs()->create([
            'type'        => 'created',
            'data'        => ['status' => $statusNow, 'department_id' => $task->department_id],
            'causer_id'   => auth()->id(),
            'happened_at' => now(),
        ]);

        if ($statusNow === 'in_progress') {
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

            $task->logs()->create([
                'type'        => 'assigned_changed',
                'data'        => [
                    'from' => $task->getOriginal('assigned_to_employee_id'),
                    'to'   => $task->assigned_to_employee_id,
                ],
                'causer_id'   => auth()->id(),
                'happened_at' => now(),
            ]);
        }

        // تغيّر الحالة؟
        if (array_key_exists('status', $task->getDirty())) {
            $from = $this->normStatus($task->getOriginal('status'));
            $to   = $this->normStatus($task->status);

            $task->logs()->create([
                'type'        => 'status_changed',
                'data'        => compact('from', 'to'),
                'causer_id'   => auth()->id(),
                'happened_at' => now(),
            ]);

            // إدارة المؤقّت
            if ($to === 'in_progress' && $from !== 'in_progress') {
                TaskTimerService::start($task, 'status_to_in_progress');
            }

            if (in_array($to, ['blocked','under_review','rework','completed','closed','cancelled'], true) && $from === 'in_progress') {
                TaskTimerService::stop($task, "status_to_{$to}");
            }

            if ($to === 'in_progress' && in_array($from, ['blocked','rework'], true)) {
                TaskTimerService::start($task, "resume_from_{$from}");
            }

            // تعبئة الطوابع الزمنية إن وُجدت الأعمدة
            if ($to === 'completed' && Schema::hasColumn('production_tasks', 'completed_at')) {
                $task->forceFill(['completed_at' => now()])->saveQuietly();
            }
            if ($to === 'closed' && Schema::hasColumn('production_tasks', 'closed_at')) {
                $task->forceFill(['closed_at' => now()])->saveQuietly();
            }
        }

        // تغيّر تاريخ التسليم؟
        if (array_key_exists('due_date', $task->getDirty())) {
            $from = $task->getOriginal('due_date');
            $to   = $task->due_date;

            $task->logs()->create([
                'type'        => 'due_changed',
                'data'        => ['from' => $from, 'to' => optional($to)?->toDateString()],
                'causer_id'   => auth()->id(),
                'happened_at' => now(),
            ]);
        }
    }
}
