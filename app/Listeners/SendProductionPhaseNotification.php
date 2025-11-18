<?php

namespace App\Listeners\ProductionRequest;

use App\Events\ProductionRequest\ProductionPhaseChanged;
use App\Models\User;
use App\Services\Notifications\ProductionRequestNotifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class SendProductionPhaseNotification
{
    public function __construct() {}

    public function handle(ProductionPhaseChanged $event): void
    {
        $phaseNotification = $event->phaseNotification;
        $pr = $event->productionRequest;
        $context = $phaseNotification->getContext() ?? [];

        $recipients = $this->resolveRecipients($pr, $context);

        if ($recipients->isEmpty()) {
            Log::warning('SendProductionPhaseNotification: no recipients resolved', [
                'pr_id' => $pr->id,
                'context' => $context,
            ]);
            return;
        }

        $notifier = app(ProductionRequestNotifier::class);

        $notifier->notifyBatch(
            recipients: $recipients,
            pr: $pr,
            title: $phaseNotification->getTitle(),
            body: $phaseNotification->getBody(),
            url: $phaseNotification->getUrl(),
            event: $phaseNotification->getEvent() ?? 'transition',
            sendMail: true
        );
    }

    private function resolveRecipients($pr, array $context): Collection
    {
        $recipients = collect();

        if (!empty($context['owner_user_id']) && (int)$context['owner_user_id'] > 0) {
            $user = User::find($context['owner_user_id']);
            if ($user) {
                $recipients->push($user);
            }
        }

        if ($recipients->isNotEmpty()) {
            return $recipients;
        }

        if (!empty($context['owner_role'])) {
            $users = User::role($context['owner_role'])->get();
            if ($users->count()) {
                $recipients = $recipients->merge($users);
            }
        }

        if ($recipients->isNotEmpty()) {
            return $recipients;
        }

        if ($pr->current_owner_user_id && $pr->current_owner_user_id > 0) {
            $u = User::find($pr->current_owner_user_id);
            if ($u) {
                $recipients->push($u);
            }
        }

        if ($pr->current_owner_role) {
            $us = User::role($pr->current_owner_role)->get();
            if ($us->count()) {
                $recipients = $recipients->merge($us);
            }
        }

        if ($recipients->isNotEmpty()) {
            return $recipients;
        }

        $creator = User::find($pr->created_by);
        if ($creator) {
            $recipients->push($creator);
        }

        return $recipients->unique('id');
    }
}
