<?php

namespace App\Services\Tasks\Workflow\Concerns;

use App\Models\ProductionTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait HasTaskWorkflowHelpers
{
    protected function ensureOwner(
        ProductionTask $task,
        ?string $role,
        ?int $userId,
        bool $touchSent = false,
        ?string $note = null,
        bool $force = false
    ): bool {
        // Normalize values for reliable comparison
        $currRole = $task->current_owner_role ?? null;
        $currUser = isset($task->current_owner_user_id) ? (int)$task->current_owner_user_id : null;
        $newUser  = $userId !== null ? (int)$userId : null;

        $sameRole = ($currRole === $role);
        $sameUser = ($currUser === $newUser);

        if ($sameRole && $sameUser && ! $force) {
            if ($note) {
                $this->log($task, 'owner_note_updated', [
                    'role'    => $role,
                    'user_id' => $userId,
                    'note'    => $note,
                ]);
            }
            return false;
        }

        $task->current_owner_role    = $role;
        $task->current_owner_user_id = $userId;

        if ($touchSent) {
            if (blank($task->sent_to_owner_at) || ! ($sameRole && $sameUser)) {
                $task->sent_to_owner_at = now();
            }
            // reset received flag on re-send
            $task->received_by_owner_at = null;
        }

        $task->save();

        // Log the ownership change (only once per actual change)
        $this->log($task, 'ownership_changed', [
            'role'    => $role,
            'user_id' => $userId,
            'note'    => $note,
        ]);

        return true;
    }

    protected function setOwner(
        ProductionTask $task,
        ?string $role,
        ?int $userId,
        bool $touchSent = false,
        ?string $note = null,
        bool $force = false
    ): bool {
        return $this->ensureOwner($task, $role, $userId, $touchSent, $note, $force);
    }

    protected function markOwnerReceived(ProductionTask $task, ?string $reason = null): void
    {
        $task->received_by_owner_at = now();
        $task->save();

        $this->log($task, 'owner_received', [
            'reason' => $reason,
        ]);
    }

    protected function resolveDeptManagerUserId(ProductionTask $task): ?int
    {
        $dept = $task->department;

        if (! $dept || ! $dept->managerUser) {
            return null;
        }

        return (int) $dept->managerUser->id;
    }

    protected function resolveInstallationManagerUserId(ProductionTask $task): ?int
    {
        return $this->resolveDeptManagerUserId($task);
    }

    protected function resolvePurchasingManagerUserId(): ?int
    {
        return User::role('purchasing_manager')->value('id');
    }

    protected function resolveFactoryManagerUserId(): ?int
    {
        return User::role('factory_manager')->value('id');
    }

    protected function resolveQualityManagerUserId(): ?int
    {
        return User::role('quality_manager')->value('id');
    }

    protected function log(ProductionTask $task, string $type, array $data = []): void
    {
        $payload = [
            'type'      => $type,
            'data'      => $data,
            'causer_id' => Auth::id(),
        ];

        if (method_exists($task, 'logs')) {
            $task->logs()->create($payload);
        } elseif (method_exists($task, 'taskLogs')) {
            $task->taskLogs()->create($payload);
        }
    }
}
