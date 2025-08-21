<?php

namespace App\Services;

use App\Models\ProductionTask;
use Illuminate\Support\Facades\Auth;

class TaskWorkflow
{
    public function sendToOwner(
        ProductionTask $task,
        ?string $ownerRole = null,
        ?int $ownerUserId = null,
        ?string $newStatus = null,
        ?string $note = null
    ): ProductionTask {
        $task->forceFill([
            'current_owner_role'   => $ownerRole,
            'current_owner_user_id'=> $ownerUserId,
            'sent_to_owner_at'     => now(),
            'received_by_owner_at' => null,
        ])->save();

        if ($newStatus && $newStatus !== $task->status) {
            $from = $task->status;
            $task->update(['status' => $newStatus]);

            $task->logs()->create([
                'type'        => 'status_changed',
                'data'        => ['from' => $from, 'to' => $newStatus, 'note' => $note, 'by' => auth()->user()?->name],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        } else {
            $task->logs()->create([
                'type'        => 'owner_changed',
                'data'        => ['owner_role' => $ownerRole, 'owner_user_id' => $ownerUserId, 'note' => $note],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        return $task->refresh();
    }

    /** تأكيد استلام من المالك الحالي */
    public function markReceived(ProductionTask $task, ?string $note = null): ProductionTask
    {
        $task->update(['received_by_owner_at' => now()]);

        $task->logs()->create([
            'type'        => 'owner_received',
            'data'        => ['owner_role' => $task->current_owner_role, 'owner_user_id' => $task->current_owner_user_id, 'note' => $note],
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        return $task->refresh();
    }
}
