<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Models\ProductionRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;
use App\Notifications\ProductionPhaseNotification;
use Throwable;

class ProductionRequestNotifier
{

    public function sendInApp(User $user, string $title, ?string $body = null, ?string $url = null): void
    {
        try {
            $note = FNotification::make()
                ->title($title)
                ->body($body ?? '');

            if ($url) {
                $note->actions([
                    FAction::make('view')
                        ->label('عرض')
                        ->url($url)
                        ->openUrlInNewTab(),
                ]);
            }

            $note->sendToDatabase($user);
        } catch (Throwable $e) {
            Log::error('Failed to send Filament in-app notification', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function sendLaravelNotification(iterable $users, ?int $prId, string $event, array $context = []): void
    {
        $users = collect($users)->filter()->unique('id');

        if ($users->isEmpty()) {
            return;
        }

        try {
            $notification = new ProductionPhaseNotification(
                prId: $prId,
                event: $event,
                context: $context
            );

            // نقطة موحدة لإرسال Laravel Notification (ستستخدم via() في Notification)
            LaravelNotification::send($users->values()->all(), $notification);
        } catch (Throwable $e) {
            Log::error('Failed to send Laravel Notification for production request', [
                'pr_id' => $prId,
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }


    public function notifyBatch(iterable $recipients, ?ProductionRequest $pr, string $title, ?string $body = null, ?string $url = null, string $event = 'transition', bool $sendMail = true): void
    {
        $users = collect($recipients)->filter()->unique('id');

        if ($users->isEmpty()) {
            Log::warning('ProductionRequestNotifier: no recipients for notification', [
                'pr_id' => $pr?->id,
                'title' => $title,
                'event' => $event,
            ]);
            return;
        }

        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            $this->sendInApp($user, $title, $body, $url);
        }

        if ($sendMail) {
            $context = [
                'title' => $title,
                'body' => $body,
                'url' => $url,
                'pr_id' => $pr?->id,
            ];

            $this->sendLaravelNotification($users, $pr?->id, $event, $context);
        }
    }
}
