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
        // Materials Requested: Only Purchasing needs to know initially. Dept Manager tracks via dashboard.
        'MaterialsRequested' => ['roles' => ['purchasing_manager'], 'send_mail' => false], 
        
        // Materials Provided: Only the Dept Manager needs to know materials arrived.
        'MaterialsProvided'  => ['roles' => ['department_manager'], 'send_mail' => false], 
        
        // Issues: Keep both informed via email.
        'MaterialsIssue'     => ['roles' => ['purchasing_manager','department_manager'], 'send_mail' => true], 

        // QA: Showroom Manager needs to know about Rejection/Approval to inform Client.
        // Rejected is urgent (email), Approved is routine (in-app).
        'QARejected'         => ['roles' => ['quality_manager','department_manager'], 'include_showroom_manager' => true, 'send_mail' => true],
        'QAApproved'         => ['roles' => ['quality_manager','department_manager'], 'include_showroom_manager' => true, 'send_mail' => false],
        
        // Handoff: Urgent alert for SLA.
        'OwnerHandoffSLA'    => ['roles' => ['department_manager'], 'send_mail' => true], 
        
        // Completion: Routine updates.
        'TaskCompleted'      => ['roles' => ['department_manager'], 'include_showroom_manager' => true, 'send_mail' => false],
        'TaskClosed'         => ['roles' => ['department_manager'], 'send_mail' => false],
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

    private function sendMailToUser(User $user, string $title, ?string $body = null, ?string $url = null, bool $sendMail = true): void
    {
        if (!$sendMail) return;
        LaravelNotification::send($user, new ActionHandoffNotification($title, $body, $url, true));
    }

    private function sendMailToEmail(string $email, string $title, ?string $body = null, ?string $url = null, bool $sendMail = true): void
    {
        if (!$sendMail) return;
        LaravelNotification::route('mail', $email)->notify(
            new ActionHandoffNotification($title, $body, $url, true)
        );
    }


    public function notifyUserId(
        int $userId,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null,
        bool $sendMail = false
    ): void {
        $user = \App\Models\User::find($userId);
        if (! $user) return;

        $url = $url ?? $this->viewUrl($task);

        $this->sendInApp($user, $title, $body, $url);

        if ($sendMail) {
            $this->sendMailToUser($user, $title, $body, $url, true);
        }
    }

    public function notifyUserIdCritical(
        int $userId,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null,
        bool $sendMail = true
    ): void {
        $user = \App\Models\User::find($userId);
        if (! $user) return;

        $url = $url ?? $this->viewUrl($task);
        $this->sendInApp($user, $title, $body, $url);
        
        if ($sendMail) {
            $this->sendMailToUser($user, $title, $body, $url, true);
        }
    }

    public function notifyRole(
        string $role,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null,
        bool $sendMail = false
    ): void {
        // Smart Routing: If targeting Department Manager & Task is known, send ONLY to that Dept Manager.
        if ($role === 'department_manager' && $task) {
            $task->loadMissing('department.managerUser');
            $manager = $task->department?->managerUser;
            
            if ($manager instanceof User) {
                // Send only to the specific manager
                $url = $url ?? $this->viewUrl($task);
                $this->sendInApp($manager, $title, $body, $url);
                if ($sendMail) {
                    $this->sendMailToUser($manager, $title, $body, $url, true);
                }
                return; // Stop here, do not blast all managers
            }
        }

        $r = Role::where('name', $role)->first();
        if (! $r) return;

        $url = $url ?? $this->viewUrl($task);

        foreach ($r->users as $u) {
            if ($u->id === auth()->id()) continue;
            $this->sendInApp($u, $title, $body, $url);
        }

        if ($sendMail && $r->users->count()) {
            $recipients = $r->users->reject(fn($u) => $u->id === auth()->id());
            if ($recipients->isNotEmpty()) {
                LaravelNotification::send($recipients, new ActionHandoffNotification($title, $body, $url, true));
            }
        }
    }

    public function notifyRoleCritical(
        string $role,
        string $title,
        ?string $body = null,
        ?string $url = null,
        ?ProductionTask $task = null,
        bool $sendMail = true
    ): void {
        // Smart Routing: If targeting Department Manager & Task is known, send ONLY to that Dept Manager.
        if ($role === 'department_manager' && $task) {
            $task->loadMissing('department.managerUser');
            $manager = $task->department?->managerUser;
            
            if ($manager instanceof User) {
                // Send only to the specific manager
                $url = $url ?? $this->viewUrl($task);
                $this->sendInApp($manager, $title, $body, $url);
                if ($sendMail) {
                    $this->sendMailToUser($manager, $title, $body, $url, true);
                }
                return; // Stop here, do not blast all managers
            }
        }

        $r = Role::where('name', $role)->first();
        if (! $r) return;

        $url = $url ?? $this->viewUrl($task);

        foreach ($r->users as $u) {
            if ($u->id === auth()->id()) continue;
            $this->sendInApp($u, $title, $body, $url);
        }

        if ($sendMail && $r->users->count()) {
            $recipients = $r->users->reject(fn($u) => $u->id === auth()->id());
            if ($recipients->isNotEmpty()) {
                LaravelNotification::send($recipients, new ActionHandoffNotification($title, $body, $url, true));
            }
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
            $this->notifyRoleCritical($role, $title, $body, $url, $task, (bool)($cfg['send_mail'] ?? false));
        }

        if (!empty($cfg['include_showroom_manager'])) {
            $this->notifyShowroomManagerCritical($task, $title, $body, (bool)($cfg['send_mail'] ?? false));
        }
    }
    public function resolveShowroomManagerUsers(ProductionTask $task): Collection
    {
        $task->loadMissing('project.productionRequest.showroom.manager');

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
        ?string $body = null,
        bool $sendMail = true
    ): void {
        $url = $this->viewUrl($task);

        $users = $this->resolveShowroomManagerUsers($task);
        foreach ($users as $user) {
            $this->sendInApp($user, $title, $body, $url);
            if ($sendMail) {
                $this->sendMailToUser($user, $title, $body, $url, true);
            }
        }

        if ($sendMail && $users->isEmpty()) {
            $fallbackEmail = $this->resolveShowroomManagerEmployeeEmail($task);
            if ($fallbackEmail) {
                $this->sendMailToEmail($fallbackEmail, $title, $body, $url, true);
            }
        }
    }
}
