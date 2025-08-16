<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskAssignedInAppNotification;
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

    }

    public function updated(ProductionTask $task): void
    {
        if ($task->wasChanged('assigned_to_employee_id')) {
            // حدّد/حدّث وقت الإسناد عند تغييره
            $task->forceFill(['assigned_at' => now()])->saveQuietly();

            if ($task->assigned_to_employee_id && $task->employee?->routeNotificationForMail(null)) {
                $task->employee->notify(new TaskAssignedNotification($task, true));
            }
            if ($user = $task->employee?->user) {
                $user->notify(new TaskAssignedInAppNotification($task, true));
            }
        }
    }
}
