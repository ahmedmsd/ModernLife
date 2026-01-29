<?php

namespace App\Listeners;

use App\Events\ProductionRequestPhaseEvent;
use App\Models\User;
use App\Notifications\ProductionPhaseNotification;
use App\Services\Notifications\ProductionRequestNotifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendProductionPhaseNotification
{
    public function __construct() {}

    public function handle(ProductionRequestPhaseEvent $event): void
    {
        $pr = $event->pr;
        $context = $event->context ?? [];
        $eventType = $event->type;

        $phaseNotification = new ProductionPhaseNotification(
            prId: $pr->id,
            event: $eventType,
            context: $context
        );

        $recipients = $this->resolveRecipients($pr, $context, $eventType)
            ->unique('id')
            ->reject(fn($u) => $u->id === auth()->id());

        if ($recipients->isEmpty()) {
            return;
        }

        try {
            $notifier = app(ProductionRequestNotifier::class);

            // Conditional Email: Only for critical events
            $sendMail = in_array($eventType, ['rejected', 'factory_rejected', 'project_bootstrap']);

            $notifier->notifyBatch(
                recipients: $recipients->values(),
                pr: $pr,
                title: $phaseNotification->getTitle(),
                body: $phaseNotification->getBody(),
                url: $phaseNotification->getUrl(),
                event: $phaseNotification->getEvent() ?? $eventType,
                sendMail: $sendMail
            );
        } catch (\Throwable $e) {
            Log::error('SendProductionPhaseNotification: failed to notify', [
                'pr_id' => $pr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveRecipients($pr, array $context, string $eventType): Collection
    {
        $recipients = collect();

        // 1. Specific Owner User ID (Priority)
        $ownerId = $context['owner_user_id'] ?? $pr->current_owner_user_id;
        if (!empty($ownerId) && (int)$ownerId > 0) {
            $user = User::find($ownerId);
            if ($user) {
                $recipients->push($user);
            }
        }

        // 2. Owner Role (Only if no specific user is resolved or it's an initial assignment)
        $ownerRole = $context['owner_role'] ?? $pr->current_owner_role;
        if (!empty($ownerRole) && ($recipients->isEmpty() || $eventType === 'transition')) {
            
            // Smart Showroom Filter: If role is showroom_manager, route to the specific manager of that showroom
            if ($ownerRole === 'showroom_manager' && !empty($pr->showroom_id)) {
                $pr->loadMissing('showroom');
                $managerId = $pr->showroom?->manager_id;
                if ($managerId) {
                    $manager = User::find($managerId);
                    if ($manager) $recipients->push($manager);
                }
            } 
            // Smart Department Filter: If role is department_manager, route to managers of involved departments
            elseif ($ownerRole === 'department_manager') {
                $pr->loadMissing('files.department.managerUser');
                $departmentManagers = $pr->files->map(fn($f) => $f->department?->managerUser)->filter()->unique('id');
                
                if ($departmentManagers->isNotEmpty()) {
                    $recipients = $recipients->merge($departmentManagers);
                } else {
                    // Fallback if no specific managers found
                    $recipients = $recipients->merge(User::role('department_manager')->get());
                }
            }
            else {
                // Fallback for other roles or if no specific showroom manager found
                $roleUsers = User::role($ownerRole)->get();
                if ($roleUsers->count()) {
                    $recipients = $recipients->merge($roleUsers);
                }
            }
        }

        // 3. Creator (Only for critical results)
        $criticalForCreator = ['rejected', 'factory_rejected', 'project_bootstrap'];
        if (in_array($eventType, $criticalForCreator)) {
            $creator = User::find($pr->created_by);
            if ($creator) {
                $recipients->push($creator);
            }
        }

        return $recipients->filter()->unique('id')->values();
    }
}
