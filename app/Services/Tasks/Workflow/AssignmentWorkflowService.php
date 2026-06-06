<?php

namespace App\Services\Tasks\Workflow;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use Illuminate\Support\Facades\DB;

class AssignmentWorkflowService
{
    use HasTaskWorkflowHelpers;

    public function assignToDeptManager(ProductionTask $task, int $userId, string $dueDate, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $userId, $dueDate , $note) {
            $task->forceFill([
                'assigned_to_user_id' => $userId,
                'status'                  => 'pending',
                'assigned_at'             => now(),
                'due_date'                => $dueDate,
            ])->save();

            $changed = $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $userId,
                touchSent: true,
                note: 'إسناد المهمة لمدير القسم' . ($note ? ' - ' . $note : '')
            );

            if ($changed) {
                $this->log($task, 'assign_to_dept_manager', [
                    'note' => $note,
                    'user_id' => $userId,
                ]);
            } else {
                $this->log($task, 'assign_to_dept_manager_noop', [
                    'note' => $note,
                    'user_id' => $userId,
                ]);
            }
        });
    }

    public function deptAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);
            $task->save();

            $this->markOwnerReceived($task, 'استلام مدير القسم للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'dept_acknowledge', [
                'note' => $note,
            ]);
        });
    }

    public function deptRejectToFactory(ProductionTask $task, string $reason): void
    {
        DB::transaction(function () use ($task, $reason) {
            $task->status = 'returned_to_factory';
            $task->save();

            $this->markOwnerReceived($task, 'رفض مدير القسم للمهمة وإعادتها للمصنع');

            $factoryUserId = $this->resolveFactoryManagerUserId();
            $this->setOwner(
                task: $task,
                role: 'factory_manager',
                userId: $factoryUserId,
                touchSent: true,
                note: 'إعادة من مدير القسم للمصنع'
            );

            $this->log($task, 'dept_reject_to_factory', [
                'reason' => $reason,
            ]);
        });
    }

    public function resubmitToDeptManager(ProductionTask $task, string $note): void
    {
        $deptManagerId = $this->resolveDeptManagerUserId($task);
        
        // If department manager not found, maybe fallback or let valid nullable logic handle it?
        // standard behavior:
        
        $this->setOwner(
            task: $task,
            role: 'department_manager',
            userId: $deptManagerId,
            touchSent: true,
            note: $note
        );

        $task->update(['status' => 'pending']);
    }
}
