<?php

namespace App\Services\Notifications;

use App\Filament\Resources\TaskResource;
use App\Models\ProductionTask;
use App\Models\User;
use App\Notifications\ActionHandoffNotification;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Spatie\Permission\Models\Role;

class TaskNotifier
{

    private array $criticalRouting = [
        'MaterialsRequested' => ['roles' => ['purchasing_manager','department_manager'], 'include_showroom_manager' => true],
        'MaterialsProvided'  => ['roles' => ['department_manager'], 'include_showroom_manager' => true],
        'MaterialsIssue'     => ['roles' => ['purchasing_manager','department_manager'], 'include_showroom_manager' => true],
        'QARejected'         => ['roles' => ['quality_manager','department_manager'], 'include_showroom_manager' => true],
        'QAApproved'         => ['roles' => ['quality_manager','department_manager'], 'include_showroom_manager' => true],
        'OwnerHandoffSLA'    => ['roles' => ['department_manager'], 'include_showroom_manager' => true],
        'TaskCompleted'      => ['roles' => ['department_manager'], 'include_showroom_manager' => true],
        'TaskClosed'         => ['roles' => ['department_manager'], 'include_showroom_manager' => true],
    ];

    private function viewUrl(?ProductionTask $task): ?string
    {
        return $task ? TaskResource::getUrl('view', ['record' => $task->getKey()]) : null;
    }


    private function sendInApp(User $user, string $title, ?string $body = null, ?string $url = null): void
    {
        $note = FNotification::make()
            ->title($title)
            ->body($body ?? '');

        if ($url) {
            $note->actions([
                FAction::make('عرض المهمة')->button()->url($url)->openUrlInNewTab(),
            ]);
        }

        $note->sendToDatabase($user);
    }

    private function sendMailToUser(User $user, string $title, ?string $body = null, ?string $url = null): void
    {
        LaravelNotification::send($user, new ActionHandoffNotification($title, $body, $url));
    }

    private function sendMailToEmail(string $email, string $title, ?string $body = null, ?string $url = null): void
    {
        LaravelNotification::route('mail', $email)->notify(
            new ActionHandoffNotification($title, $body, $url)
        );
    }


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

        $this->sendInApp($user, $title, $body, $url);

        $this->sendMailToUser($user, $title, $body, $url);
    }

    public function notifyUserIdCritical(
        int $userId,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null
    ): void {
        $user = \App\Models\User::find($userId);
        if (! $user) return;

        $url = $url ?? $this->viewUrl($task);
        $this->sendInApp($user, $title, $body, $url);
        $this->sendMailToUser($user, $title, $body, $url);
    }

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
            $this->sendInApp($u, $title, $body, $url);
        }

        if ($r->users->count()) {
            LaravelNotification::send($r->users, new ActionHandoffNotification($title, $body, $url));
        }
    }

    public function notifyRoleCritical(
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
            $this->sendInApp($u, $title, $body, $url);
        }

        if ($r->users->count()) {
            LaravelNotification::send($r->users, new ActionHandoffNotification($title, $body, $url));
        }
    }

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

    public function notifyCriticalForEvent(string $eventKey, ProductionTask $task, string $title, ?string $body = null): void
    {
        $url = $this->viewUrl($task);
        $cfg = $this->criticalRouting[$eventKey] ?? null;
        if (!$cfg) return;

        foreach (($cfg['roles'] ?? []) as $role) {
            $this->notifyRoleCritical($role, $title, $body, $url, $task);
        }

        if (!empty($cfg['include_showroom_manager'])) {
            $this->notifyShowroomManagerCritical($task, $title, $body);
        }
    }
    public function resolveShowroomManagerUsers(ProductionTask $task): Collection
    {
        $task->loadMissing('project.productionRequest.showroom.manager.user');

        $employee = optional(
            optional(
                optional(
                    optional($task->project)->productionRequest
                )->showroom
            )->manager
        );

        if ($employee && $employee->user instanceof User) {
            return collect([$employee->user]);
        }

        return collect();
    }


    public function resolveShowroomManagerEmployeeEmail(ProductionTask $task): ?string
    {
        $task->loadMissing('project.productionRequest.showroom.manager');

        $employee = optional(
            optional(
                optional(
                    optional($task->project)->productionRequest
                )->showroom
            )->manager
        );

        $email = $employee ? (string) ($employee->email ?? '') : '';
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    public function notifyShowroomManagerCritical(
        ProductionTask $task,
        string $title,
        ?string $body = null
    ): void {
        $url = $this->viewUrl($task);

        $users = $this->resolveShowroomManagerUsers($task);
        foreach ($users as $user) {
            $this->sendInApp($user, $title, $body, $url);
            $this->sendMailToUser($user, $title, $body, $url);
        }

        if ($users->isEmpty()) {
            $fallbackEmail = $this->resolveShowroomManagerEmployeeEmail($task);
            if ($fallbackEmail) {
                $this->sendMailToEmail($fallbackEmail, $title, $body, $url);
            }
        }
    }
}
