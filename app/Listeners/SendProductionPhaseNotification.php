<?php


namespace App\Listeners;

use App\Events\ProductionRequestPhaseEvent;
use App\Models\User;
use App\Notifications\ProductionPhaseNotification;
use Illuminate\Support\Facades\Notification;

class SendProductionPhaseNotification
{
    public function handle(ProductionRequestPhaseEvent $event): void
    {
        $pr = $event->pr;
        $context = $event->context ?? [];
        $type = $event->type;

        $recipients = collect();

        // 1) المالك السابق في كل انتقال
        if ($type === 'transition') {
            $prevId = $context['prev_owner_id'] ?? ($context['from']['owner_user_id'] ?? null);
            if ($prevId) {
                if ($u = User::find($prevId)) {
                    $recipients->push($u);
                }
            }
        }

        // 2) منشئ الطلب في الاستلام أو الرفض أو الانتقال المهم
        $creatorId = $context['creator_id'] ?? $pr->created_by ?? null;
        if ($creatorId && in_array($type, ['received', 'rejected'], true)) {
            if ($u = User::find($creatorId)) {
                $recipients->push($u);
            }
        }

        // 3) المالك الحالي عند استلامه أو انتقال الملكية إليه
        $ownerId = $context['owner_user_id'] ?? ($context['to']['owner_user_id'] ?? null);
        if ($ownerId && in_array($type, ['transition', 'received', 'project_bootstrap'], true)) {
            if ($u = User::find($ownerId)) {
                $recipients->push($u);
            }
        }

        // إزالة التكرارات
        $recipients = $recipients->filter()->unique('id');

        if ($recipients->isEmpty()) {
            return;
        }

        Notification::send(
            $recipients,
            new ProductionPhaseNotification($pr->id, $type, $context)
        );
    }
}
