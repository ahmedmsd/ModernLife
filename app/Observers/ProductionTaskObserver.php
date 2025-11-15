<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\ProductionTask;
use App\Notifications\TaskAssignedInAppNotification;
use App\Notifications\TaskAssignedNotification;
use App\Services\Tasks\TaskTimerService;
use App\Services\Notifications\TaskNotifier;
use Filament\Notifications\Actions\Action as FAction;
use Filament\Notifications\Notification as FNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProductionTaskObserver
{
    protected function normStatus(null|string|\BackedEnum $s): ?string
    {
        return $s instanceof \BackedEnum ? $s->value : ($s === null ? null : (string) $s);
    }

    public function updating(ProductionTask $task): void
    {
        if ($task->isDirty('assigned_to_user_id')) {
            $ownerUserId = $task->assigned_to_user_id ?: null;

            $task->current_owner_role    = 'department_manager';
            $task->current_owner_user_id = $ownerUserId;
        }
    }

    public function created(ProductionTask $task): void
    {
        if ($task->assigned_to_user_id) {
            // تأكيد وقت الإسناد
            if (blank($task->assigned_at)) {
                $task->forceFill(['assigned_at' => now()])->saveQuietly();
            }

            // المستخدم المسؤول
            $assignedUser = $task->assignedUser;            // User|null
            $employee     = $assignedUser?->employee;       // Employee|null

            // إشعار البريد (على الموظف)
            if ($employee && $employee->routeNotificationForMail(null)) {
                $employee->notify(new TaskAssignedNotification($task, false));
            }

            // إشعار داخل النظام (على المستخدم)
            if ($assignedUser) {
                $assignedUser->notify(new TaskAssignedInAppNotification($task, false));
            }

            // ضبط المالك الحالي إن لم يكن مضبوطًا
            if (blank($task->current_owner_role)) {
                $ownerUserId = $task->assigned_to_user_id;

                $task->forceFill([
                    'current_owner_role'    => 'department_manager',
                    'current_owner_user_id' => $ownerUserId,
                    'sent_to_owner_at'      => now(),
                    'received_by_owner_at'  => null,
                ])->saveQuietly();
            }
        }

        $statusNow = $this->normStatus($task->status);

        $task->logs()->create([
            'type'        => 'created',
            'data'        => [
                'status'     => $statusNow,
                'department' => $task->department_id,
                'owner'      => [
                    'role' => $task->current_owner_role,
                    'user' => $task->current_owner_user_id,
                ],
            ],
            'note'        => 'تم إنشاء المهمة',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        if ($statusNow === 'in_progress') {
            TaskTimerService::start($task, 'auto_on_create');
        }
    }

    public function updated(ProductionTask $task): void
    {
        $notifier = app(TaskNotifier::class);

        // تغيير الإسناد (من مستخدم إلى مستخدم)
        if ($task->wasChanged('assigned_to_user_id')) {
            $task->forceFill(['assigned_at' => now()])->saveQuietly();

            $assignedUser = $task->assignedUser;      // User|null
            $employee     = $assignedUser?->employee; // Employee|null

            if ($employee && $employee->routeNotificationForMail(null)) {
                $employee->notify(new TaskAssignedNotification($task, true));
            }

            if ($assignedUser) {
                $assignedUser->notify(new TaskAssignedInAppNotification($task, true));
            }

            $task->logs()->create([
                'type'        => 'assigned_changed',
                'data'        => [
                    'from' => $task->getOriginal('assigned_to_user_id'),
                    'to'   => $task->assigned_to_user_id,
                ],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        // تغيير المالك الحالي
        if ($task->wasChanged('current_owner_role') || $task->wasChanged('current_owner_user_id')) {
            $from = [
                'role' => $task->getOriginal('current_owner_role'),
                'user' => $task->getOriginal('current_owner_user_id'),
            ];
            $to = [
                'role' => $task->current_owner_role,
                'user' => $task->current_owner_user_id,
            ];

            $task->forceFill([
                'sent_to_owner_at'     => now(),
                'received_by_owner_at' => null,
            ])->saveQuietly();

            $task->logs()->create([
                'type'        => 'ownership_changed',
                'data'        => compact('from', 'to'),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            $toRole   = $task->current_owner_role;
            $sentType = match ($toRole) {
                'showroom_manager'      => 'sent_to_showroom',
                'factory_manager'       => 'sent_to_factory',
                'department_manager'    => 'sent_to_department',
                'purchasing_manager'    => 'sent_to_purchasing',
                'quality_manager'       => 'sent_to_quality',
                'installation_manager'  => 'sent_to_install',
                default                 => null,
            };

            if ($sentType) {
                $task->logs()->create([
                    'type'        => $sentType,
                    'data'        => ['to' => $toRole, 'user' => $task->current_owner_user_id],
                    'causer_id'   => Auth::id(),
                    'happened_at' => now(),
                ]);
            }

            $isDeptManagerOwnership =
                ($task->current_owner_role === 'department_manager') &&
                ! empty($task->current_owner_user_id);

            if ($isDeptManagerOwnership) {
                $task->loadMissing('department.managerUser');

                $intendedOwner = $task->department?->managerUser;      // User|null
                $intendedId    = $intendedOwner?->id;                  // int|null
                $currentId     = $task->current_owner_user_id;         // int|null
                $actorId       = Auth::id();

                if ($intendedId && $intendedId !== $currentId) {
                    if ($actorId !== $intendedId) {
                        FNotification::make()
                            ->title('تم نقل ملكية مهمة إلى قسمك')
                            ->body("تم تعيين المهمة #{$task->id} كمسؤولية قسمك.")
                            ->success()
                            ->sendToDatabase($intendedOwner);
                    }
                }
            }

            try {
                $title = "تسليم مهمة #{$task->id}";
                $body  = "تم تحويل ملكية المهمة إلى {$task->current_owner_role}.";
                if (in_array($task->current_owner_role, ['purchasing_manager', 'quality_manager'], true)) {
                    $notifier->notifyCriticalForEvent('OwnerHandoffSLA', $task, $title, $body);
                }
            } catch (\Throwable $e) {
            }
        }

        if ($task->wasChanged('received_by_owner_at')) {
            $task->logs()->create([
                'type'        => 'ownership_received',
                'data'        => [
                    'owner'   => [
                        'role' => $task->current_owner_role,
                        'user' => $task->current_owner_user_id,
                    ],
                    'recv_at' => $task->received_by_owner_at,
                ],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        if ($task->wasChanged('status')) {
            $from = $this->normStatus($task->getOriginal('status'));
            $to   = $this->normStatus($task->status);

            $task->logs()->create([
                'type'        => 'status_changed',
                'data'        => compact('from', 'to'),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            $stopOnLeaveInProgress = [
                'materials_wait','materials_prep','materials_done',
                'on_hold','under_review','approved','rejected',
                'completed','cancelled','rework',
            ];

            if ($to === 'in_progress' && $from !== 'in_progress') {
                TaskTimerService::start($task, $from ? "resume_from_{$from}" : 'status_to_in_progress');
            }

            if ($from === 'in_progress' && in_array($to, $stopOnLeaveInProgress, true)) {
                TaskTimerService::stop($task, "status_to_{$to}");
            }

            if ($to === 'received' && Schema::hasColumn('production_tasks', 'received_at') && blank($task->received_at)) {
                $task->forceFill(['received_at' => now()])->saveQuietly();
            }
            if ($to === 'completed' && Schema::hasColumn('production_tasks', 'completed_at') && blank($task->completed_at)) {
                $task->forceFill(['completed_at' => now()])->saveQuietly();
            }
            if ($to === 'cancelled' && Schema::hasColumn('production_tasks', 'closed_at') && blank($task->closed_at)) {
                $task->forceFill(['closed_at' => now()])->saveQuietly();
            }

            try {
                switch ($to) {
                    case 'materials_wait':
                        $notifier->notifyCriticalForEvent(
                            'MaterialsRequested',
                            $task,
                            "طلب خامات لمهمة #{$task->id}",
                            "المطلوب تجهيز خامات لهذه المهمة."
                        );
                        break;

                    case 'materials_done':
                        $notifier->notifyCriticalForEvent(
                            'MaterialsProvided',
                            $task,
                            "توريد خامات لمهمة #{$task->id}",
                            "تم توريد الخامات المطلوبة."
                        );
                        break;

                    case 'approved':
                        $notifier->notifyCriticalForEvent(
                            'QAApproved',
                            $task,
                            "اعتماد الجودة لمهمة #{$task->id}",
                            "تم اعتماد نتيجة الجودة."
                        );
                        break;

                    case 'rejected':
                        $notifier->notifyCriticalForEvent(
                            'QARejected',
                            $task,
                            "رفض الجودة لمهمة #{$task->id}",
                            "يرجى مراجعة الملاحظات وإعادة العمل."
                        );
                        break;

                    case 'completed':
                        $notifier->notifyCriticalForEvent(
                            'TaskCompleted',
                            $task,
                            "اكتمال مهمة #{$task->id}",
                            "تم إنهاء المهمة بنجاح."
                        );
                        break;
                }
            } catch (\Throwable $e) {
            }
        }

        if ($task->wasChanged('due_date')) {
            $task->logs()->create([
                'type'        => 'due_changed',
                'data'        => [
                    'from' => $task->getOriginal('due_date'),
                    'to'   => optional($task->due_date)?->toDateString(),
                ],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        if (Schema::hasColumn('production_tasks', 'planned_start_at')) {
            if ($task->wasChanged('planned_start_at') || $task->wasChanged('planned_end_at') || $task->wasChanged('planned_install_at')) {
                $task->logs()->create([
                    'type'        => 'plan_set',
                    'data'        => [
                        'planned_start_at'   => $task->planned_start_at,
                        'planned_end_at'     => $task->planned_end_at,
                        'planned_install_at' => $task->planned_install_at,
                    ],
                    'causer_id'   => Auth::id(),
                    'happened_at' => now(),
                ]);
            }
        }

        if ($task->wasChanged('department_id')) {
            $dept    = $task->department()->with(['managerUser', 'headUser'])->first();
            $targets = collect([$dept?->managerUser, $dept?->headUser])->filter();

            foreach ($targets as $user) {
                FNotification::make()
                    ->title('مهمة انتقلت إلى قسمك')
                    ->body("المهمة (#{$task->id}) على المشروع #{$task->project_id}")
                    ->icon('heroicon-o-arrow-right')
                    ->info()
                    ->actions([
                        FAction::make('عرض المشروع')
                            ->button()
                            ->url(\App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $task->project_id])),
                    ])
                    ->sendToDatabase($user);
            }

            $task->logs()->create([
                'type'        => 'department_changed',
                'data'        => [
                    'from' => $task->getOriginal('department_id'),
                    'to'   => $task->department_id,
                ],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }
    }
}
