<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ProductionRequestWorkflow;

class ProjectObserver
{
    public function updated(Project $project): void
    {
        // متى نعتبر المشروع منتهيًا؟
        // - إن كانت حالة المشروع = completed
        // - أو كل المهام ضمنه status = completed وغيّرنا حالة المشروع ثم نقفل الطلب

        $pr = $project->productionRequest ?? null;
        if (! $pr) {
            return;
        }

        $statusChangedToCompleted =
            $project->wasChanged('status') && $project->status === 'completed';

        if ($statusChangedToCompleted) {
            app(ProductionRequestWorkflow::class)->finalizeRequestAfterProjectDone($pr);
            return;
        }

        // في حال لا تغيّر status المشروع، لكن كل المهام اكتملت:
        if ($project->relationLoaded('tasks')) {
            $tasks = $project->tasks;
        } else {
            $tasks = $project->tasks()->get(['id','status']);
        }

        if ($tasks->count() > 0 && $tasks->every(fn($t) => $t->status === 'completed')) {
            // حدّث حالة المشروع ثم أغلق الطلب
            $project->update(['status' => 'completed']);
            app(ProductionRequestWorkflow::class)->finalizeRequestAfterProjectDone($pr);
        }
    }
}
