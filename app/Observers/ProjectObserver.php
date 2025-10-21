<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\ProductionRequestWorkflow;

class ProjectObserver
{
    public function updated(Project $project): void
    {

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

        if ($project->relationLoaded('tasks')) {
            $tasks = $project->tasks;
        } else {
            $tasks = $project->tasks()->get(['id','status']);
        }

        if ($tasks->count() > 0 && $tasks->every(fn($t) => $t->status === 'completed')) {
            $project->update(['status' => 'completed']);
            app(ProductionRequestWorkflow::class)->finalizeRequestAfterProjectDone($pr);
        }
    }
}
