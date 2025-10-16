<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\ProductionTask;
use App\Notifications\TaskAssignedInAppNotification;
use App\Notifications\TaskAssignedNotification;
use App\Services\Tasks\TaskTimerService;
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
        // لو تغيّر الإسناد، حدث المالك تلقائياً (بدون لوج هنا)
        if ($task->isDirty('assigned_to_employee_id')) {
            $ownerUserId = null;
            if ($task->assigned_to_employee_id) {
                $emp = Employee::with('user:id')->find($task->assigned_to_employee_id);
                $ownerUserId = $emp?->user?->id;
            }
            $task->current_owner_role    = 'department_manager';
            $task->current_owner_user_id = $ownerUserId;
        }
    }

    public function created(ProductionTask $task): void
    {
        if ($task->assigned_to_employee_id) {
            if (blank($task->assigned_at)) {
                $task->forceFill(['assigned_at' => now()])->saveQuietly();
            }

            if ($task->employee?->routeNotificationForMail(null)) {
                $task->employee->notify(new TaskAssignedNotification($task, false));
            }

            if ($user = $task->employee?->user) {
                $user->notify(new TaskAssignedInAppNotification($task, false));
            }

            if (blank($task->current_owner_role)) {
                $ownerUserId = $task->employee?->user?->id;
                $task->forceFill([
                    'current_owner_role'    => 'department_manager',
                    'current_owner_user_id' => $ownerUserId,
                    'sent_to_owner_at'      => now(),
                    'received_by_owner_at'  => null,
                ])->saveQuietly();
            }
        }

        $statusNow = $this->normStatus($task->status);

        // إنشاء
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
        /*** 1) تغيّر الإسناد ***/
        if ($task->wasChanged('assigned_to_employee_id')) {
            $task->forceFill(['assigned_at' => now()])->saveQuietly();

            if ($task->assigned_to_employee_id && $task->employee?->routeNotificationForMail(null)) {
                $task->employee->notify(new TaskAssignedNotification($task, true));
            }
            if ($user = $task->employee?->user) {
                $user->notify(new TaskAssignedInAppNotification($task, true));
            }

            $task->logs()->create([
                'type'        => 'assigned_changed',
                'data'        => [
                    'from' => $task->getOriginal('assigned_to_employee_id'),
                    'to'   => $task->assigned_to_employee_id,
                ],
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        /*** 2) تغيّر المالك (role/user) ***/
        if ($task->wasChanged('current_owner_role') || $task->wasChanged('current_owner_user_id')) {
            $from = [
                'role' => $task->getOriginal('current_owner_role'),
                'user' => $task->getOriginal('current_owner_user_id'),
            ];
            $to = [
                'role' => $task->current_owner_role,
                'user' => $task->current_owner_user_id,
            ];

            // لمس وقت الإرسال للمالك (لا لوج هنا؛ نضيف sent_to_* لاحقاً)
            $task->forceFill([
                'sent_to_owner_at'     => now(),
                'received_by_owner_at' => null,
            ])->saveQuietly();

            // ownership_changed
            $task->logs()->create([
                'type'        => 'ownership_changed',
                'data'        => compact('from', 'to'),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            // حدث إرسال موحّد حسب الدور الجديد (sent_to_*)
            $sentEvent = match ($to['role']) {
                'showroom_manager'    => 'sent_to_showroom',
                'factory_manager'     => 'sent_to_factory',
                'department_manager'  => 'sent_to_department',
                'purchasing_manager'  => 'sent_to_purchasing',
                'quality_manager'     => 'sent_to_quality',
                'installation_manager'=> 'sent_to_install',
                default               => null,
            };

            if ($sentEvent) {
                $task->logs()->create([
                    'type'        => $sentEvent,
                    'data'        => [
                        'from_owner_role' => $from['role'],
                        'to_owner_role'   => $to['role'],
                    ],
                    'causer_id'   => Auth::id(),
                    'happened_at' => now(),
                ]);
            }

            // إشعار داخلي عند نقل الملكية لقسم مدير القسم
            $isDeptManagerOwnership =
                ($task->current_owner_role === 'department_manager') &&
                !empty($task->current_owner_user_id);

            if ($isDeptManagerOwnership) {
                if (!$task->employee || $task->employee->user_id !== $task->current_owner_user_id) {
                    $tmp = clone $task;
                    $tmp->setRelation('department', $task->department()->first());
                    $dept = $task->department()->with(['managerUser'])->first();
                    $targets = collect([$dept?->managerUser])->filter();
                    if ($targets->isNotEmpty()) {
                        foreach ($targets as $user) {
                            FNotification::make()
                                ->title('تم نقل ملكية مهمة إلى قسمك')
                                ->body("تم تعيين المهمة #{$task->id} كمسؤولية قسمك.")
                                ->success()
                                ->sendToDatabase($user);
                        }
                    }
                }
            }
        }

        /*** 3) تأكيد استلام المالك ***/
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

        /*** 4) تغيّر الحالة ***/
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
                'completed','cancelled',
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
        }

        /*** 5) تغيّر تاريخ التسليم ***/
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

        /*** 6) تغيّر المواعيد المخططة ***/
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

        /*** 7) تغيّر القسم ***/
        if ($task->wasChanged('department_id')) {
            $dept = $task->department()->with(['managerUser', 'headUser'])->first();
            $targets = collect([$dept?->managerUser, $dept?->headUser])->filter();

            if ($targets->isNotEmpty()) {
                $url = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $task->project_id]);
                foreach ($targets as $user) {
                    FNotification::make()
                        ->title('مهمة انتقلت إلى قسمك')
                        ->body("المهمة (#{$task->id}) على المشروع #{$task->project_id}")
                        ->icon('heroicon-o-arrow-right')
                        ->info()
                        ->actions([
                            FAction::make('عرض المشروع')->button()->url($url),
                        ])
                        ->sendToDatabase($user);
                }
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
