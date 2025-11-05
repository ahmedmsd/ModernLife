<?php

namespace App\Support\Notifications;

use App\Models\User;
use App\Models\ProductionTask;
use Illuminate\Support\Collection;

class ResolveRecipients
{
    public static function showroomManagerForTask(ProductionTask $task): Collection
    {
        $employee = optional(
            optional(
                optional(
                    optional($task->project)->productionRequest
                )->showroom
            )->manager
        );

        if ($employee && $employee->user instanceof User) {
            return collect([$employee->user]);
        }

        return collect();
    }

    public static function deptManager(ProductionTask $task): Collection { /* ... */ return collect(); }
    public static function assignee(ProductionTask $task): Collection { /* ... */ return collect(); }
    public static function projectOwner(ProductionTask $task): Collection { /* ... */ return collect(); }
    public static function role(string $name): Collection { return \App\Models\User::role($name)->get(); }
}
