<?php

namespace App\Services\Tasks\Workflow;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CompletionWorkflowService
{
    use HasTaskWorkflowHelpers;

    public function uploadClientReceiptAndComplete(
        ProductionTask $task,
        ?string $clientReceiptPath = null,
        ?string $completedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $clientReceiptPath, $completedAt, $note) {
            if (! empty($clientReceiptPath)) {
                $task->client_receipt = $clientReceiptPath;
            }

            if ($completedAt) {
                $task->completed_at = Carbon::parse($completedAt);
            } elseif (! $task->completed_at) {
                $task->completed_at = now();
            }

            $task->status                = 'completed';
            $task->current_owner_role    = null;
            $task->current_owner_user_id = null;
            $task->save();

            $this->log($task, 'upload_client_receipt_and_complete', [
                'note'           => $note,
                'client_receipt' => $clientReceiptPath,
                'completed_at'   => $task->completed_at?->toDateTimeString(),
            ]);

            // إقفال المشروع/الطلب إن لم تبقَ مهام مفتوحة
            $finalStatuses = ['completed', 'cancelled', 'closed'];

            $project = $task->project()
                ->withCount(['tasks as open_tasks_count' => function ($q) use ($finalStatuses) {
                    $q->whereNotIn('status', $finalStatuses);
                }])
                ->first();

            if ($project && (int) $project->open_tasks_count === 0) {
                $projUpdate = ['status' => 'completed'];
                if (Schema::hasColumn($project->getTable(), 'completed_at')) {
                    $projUpdate['completed_at'] = now();
                } elseif (Schema::hasColumn($project->getTable(), 'closed_at')) {
                    $projUpdate['closed_at'] = now();
                }
                $project->update($projUpdate);

                $this->log($task, 'project_completed', [
                    'project_id' => $project->id,
                    'by'         => Auth::id(),
                ]);

                $pr = $project->productionRequest ?? null;
                if ($pr) {
                    $prUpdate = [];
                    if (Schema::hasColumn($pr->getTable(), 'current_phase')) {
                        $prUpdate['current_phase'] = 'closed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'phase_status')) {
                        $prUpdate['phase_status'] = 'completed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'status')) {
                        $prUpdate['status'] = 'completed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'closed_at')) {
                        $prUpdate['closed_at'] = now();
                    } elseif (Schema::hasColumn($pr->getTable(), 'completed_at')) {
                        $prUpdate['completed_at'] = now();
                    }
                    if (!empty($prUpdate)) {
                        $pr->update($prUpdate);
                    }

                    $this->log($task, 'production_request_closed', [
                        'production_request_id' => $pr->id,
                        'by'                    => Auth::id(),
                    ]);
                }


            }

        });
    }
}
