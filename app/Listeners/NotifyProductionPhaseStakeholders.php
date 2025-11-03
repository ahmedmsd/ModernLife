<?php

namespace App\Listeners;

use App\Events\ProductionRequestPhaseEvent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotifyProductionPhaseStakeholders
{
    public function handle(ProductionRequestPhaseEvent $event): void
    {
        $pr = $event->pr->loadMissing([
            'showroom.manager.user',
            'project.tasks.department.manager.user',
        ]);

        $recipients = $this->resolveRecipients($pr, $event->type, $event->context);

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients->unique('id')->values(),
            new \App\Notifications\ProductionPhaseNotification(
                prId: $pr->id,
                event: $event->type,
                context: $event->context
            )
        );
    }

    protected function resolveRecipients(\App\Models\ProductionRequest $pr, string $type, array $ctx): Collection
    {
        $users = collect();

        if ($pr->created_by && ($u = User::find($pr->created_by))) {
            $users->push($u);
        }

        if ($pr->current_owner_user_id && ($ownerU = User::find($pr->current_owner_user_id))) {
            $users->push($ownerU);
        }

        $role = (string) ($pr->current_owner_role ?? '');

        if ($role === 'showroom_manager') {
            if ($pr->showroom_id) {
                $pr->loadMissing('showroom.manager.user');
                $managerUser = optional(optional($pr->showroom)->manager)->user;

                if ($managerUser) {
                    $users->push($managerUser);
                    return $users->unique('id')->values();
                }
            }

            $users = $users->merge(User::role('showroom_manager')->get());
        } elseif ($role !== '') {
            $users = $users->merge(User::role($role)->get());
        }

        return $users->filter()->unique('id')->values();
    }
}
