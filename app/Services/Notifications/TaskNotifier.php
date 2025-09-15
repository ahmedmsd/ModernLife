<?php

namespace App\Services\Notifications;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Notifications\ActionHandoffNotification;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Spatie\Permission\Models\Role;

class TaskNotifier
{
    private function viewUrl(?ProductionTask $task): ?string
    {
        return $task ? TaskResource::getUrl('view', ['record' => $task->getKey()]) : null;
    }

    /** أرسل لِـ UserId محدد (داخلي + بريد) مع زر "عرض المهمة" */
    public function notifyUserId(
        int $userId,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null
    ): void {
        $user = \App\Models\User::find($userId);
        if (! $user) return;

        $url = $url ?? $this->viewUrl($task);

        $note = FNotification::make()
            ->title($title)
            ->body($body ?? '');

        if ($url) {
            $note->actions([
                FAction::make('عرض المهمة')->button()->url($url)->openUrlInNewTab(),
            ]);
        }

        $note->sendToDatabase($user);

        // بريد
        LaravelNotification::send($user, new ActionHandoffNotification($title, $body, $url));
    }

    /** أرسل لكل مستخدمي الدور */
    public function notifyRole(
        string $role,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null
    ): void {
        $r = Role::where('name', $role)->first();
        if (! $r) return;

        $url = $url ?? $this->viewUrl($task);

        foreach ($r->users as $u) {
            $note = FNotification::make()
                ->title($title)
                ->body($body ?? '');

            if ($url) {
                $note->actions([
                    FAction::make('عرض المهمة')->button()->url($url)->openUrlInNewTab(),
                ]);
            }

            $note->sendToDatabase($u);
        }

        if ($r->users->count()) {
            LaravelNotification::send($r->users, new ActionHandoffNotification($title, $body, $url));
        }
    }

    /** handoff قياسي عند تحويل الملكية (يشمل الرابط) */
    public function handoffToOwner(
        ProductionTask $task,
        ?string $toRole,
        ?int $toUserId,
        string $title,
        ?string $body = null
    ): void {
        $url = $this->viewUrl($task);

        if ($toUserId) {
            $this->notifyUserId($toUserId, $title, $body, $url, $task);
        } elseif ($toRole) {
            $this->notifyRole($toRole, $title, $body, $url, $task);
        }
    }

    /** بلّغ المنفّذ الحالي (المستخدم الحالي) مع زر عرض المهمة */
    public function notifyActor(string $title, ?string $body = null, ?ProductionTask $task = null): void
    {
        $uid = auth()->id();
        if (!$uid) return;
        $this->notifyUserId($uid, $title, $body, $this->viewUrl($task), $task);
    }

    public function defaultHandoffBody(?string $note = null): string
    {
        return 'تم تحويل ملكية المهمة إليك.' . ($note ? " ملاحظة: {$note}" : '');
    }
}
