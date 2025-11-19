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

        $phaseNotification = new ProductionPhaseNotification(
            prId: $pr->id,
            event: $event->type,
            context: $context
        );

        $recipients = $this->resolveRecipients($pr, $context);

        if ($recipients->isEmpty()) {
            Log::warning('SendProductionPhaseNotification: no recipients resolved', [
                'pr_id' => $pr->id,
                'context' => $context,
                'event' => $event->type,
            ]);
            return;
        }

        try {
            $notifier = app(ProductionRequestNotifier::class);

            $notifier->notifyBatch(
                recipients: $recipients->unique('id')->values(),
                pr: $pr,
                title: $phaseNotification->getTitle(),
                body: $phaseNotification->getBody(),
                url: $phaseNotification->getUrl(),
                event: $phaseNotification->getEvent() ?? $event->type,
                sendMail: true
            );
        } catch (\Throwable $e) {
            Log::error('SendProductionPhaseNotification: failed to notify', [
                'pr_id' => $pr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function resolveRecipients($pr, array $context): Collection
    {
        $recipients = collect();

        // owner_user_id مُمرّر في الـ context (أولوية)
        if (!empty($context['owner_user_id']) && (int)$context['owner_user_id'] > 0) {
            $user = User::find($context['owner_user_id']);
            if ($user) $recipients->push($user);
        }

        // owner role في الـ context
        if (!empty($context['owner_role'])) {
            $users = User::role($context['owner_role'])->get();
            if ($users->count()) $recipients = $recipients->merge($users);
        }

        // fallback: current_owner_user_id من الـ PR
        if (!empty($pr->current_owner_user_id) && (int)$pr->current_owner_user_id > 0) {
            $u = User::find($pr->current_owner_user_id);
            if ($u) $recipients->push($u);
        }

        // fallback: current_owner_role من الـ PR
        if (!empty($pr->current_owner_role)) {
            $us = User::role($pr->current_owner_role)->get();
            if ($us->count()) $recipients = $recipients->merge($us);
        }

        $creator = User::find($pr->created_by);
        if ($creator) $recipients->push($creator);

        return $recipients->filter()->unique('id')->values();
    }
}
