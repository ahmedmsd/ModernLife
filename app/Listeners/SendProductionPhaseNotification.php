<?php

namespace App\Listeners;

use App\Events\ProductionRequestPhaseEvent;
use App\Enums\ProductionRequestPhase as Phase;
use App\Models\User;
use App\Notifications\ProductionPhaseNotification;
use Filament\Notifications\Notification as FilamentNotification;

class SendProductionPhaseNotification
{
    public function handle(ProductionRequestPhaseEvent $event): void
    {
        $pr      = $event->pr;
        $context = $event->context ?? [];
        $type    = (string) $event->type;

        $recipients = collect();

        // 1) المالك الحالي (مستخدم محدد)
        $ownerUserId = $context['owner_user_id'] ?? $pr->current_owner_user_id ?? null;
        if ($ownerUserId) {
            $user = User::find($ownerUserId);
            if ($user) {
                $recipients->push($user);
            }
        }

        // 2) دور المالك الحالي (مثلاً factory_manager, showroom_manager, sales)
        $ownerRole = $context['owner_role'] ?? $pr->current_owner_role ?? null;
        if (is_string($ownerRole) && $ownerRole !== '') {
            $roleUsers = User::role($ownerRole)->get();
            $recipients = $recipients->merge($roleUsers);
        }

        // 3) منشئ الطلب في حالات الرفض فقط (رفض عام أو رفض المصنع)
        $creatorId = $context['creator_id'] ?? $pr->created_by ?? null;
        if ($creatorId && in_array($type, ['rejected', 'factory_rejected'], true)) {
            $creator = User::find($creatorId);
            if ($creator) {
                $recipients->push($creator);
            }
        }

        // 4) تنبيه المبيعات عند قرار مدير المعرض (اعتماد أو رفض)
        // قرار مدير المعرض = المرحلة ShowroomReview
        $from      = $context['from'] ?? [];
        $to        = $context['to'] ?? [];
        $fromPhase = $from['phase'] ?? null;
        $toPhase   = $to['phase'] ?? null;
        $phase     = $context['phase'] ?? null;

        // حالة الاعتماد: انتقال داخل نفس المرحلة ShowroomReview من حالة إلى حالة Approved
        $isShowroomApproval = $type === 'transition'
            && $fromPhase === Phase::ShowroomReview->value
            && $toPhase === Phase::ShowroomReview->value;

        // حالة الرفض: حدث rejected والـ phase هي ShowroomReview
        $isShowroomRejection = $type === 'rejected'
            && $phase === Phase::ShowroomReview->value;

        if ($isShowroomApproval || $isShowroomRejection) {
            // المبيعات = من أنشأ الطلب (أو من قدّمه)
            $salesId = $pr->created_by ?? $pr->submitted_by ?? null;
            if ($salesId) {
                $salesUser = User::find($salesId);
                if ($salesUser) {
                    $recipients->push($salesUser);
                }
            }
        }

        // 5) استبعاد منفّذ الحركة (إن وجد) + ترتيب القائمة
        $causerId = $context['causer_id'] ?? auth()->id();

        $recipients = $recipients
            ->filter(fn ($user) => $user && $user->id)
            ->unique('id');

        if ($causerId) {
            $recipients = $recipients->reject(fn ($user) => $user->id === $causerId);
        }

        if ($recipients->isEmpty()) {
            return;
        }

        $phaseNotification = new ProductionPhaseNotification(
            prId: $pr->id,
            event: $type,
            context: $context,
        );

        FilamentNotification::make()
            ->title($phaseNotification->getTitle())
            ->body($phaseNotification->getBody())
            ->icon('heroicon-o-clipboard-document-check')
            ->color($phaseNotification->getColor())
//            ->url($phaseNotification->getUrl())
            ->sendToDatabase($recipients);
    }
}
