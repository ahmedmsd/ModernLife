<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Notifications\TaskAssignedNotification;
use App\Notifications\TaskAssignedInAppNotification;
use App\Services\TaskTimerService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ProductionTaskObserver
{
    /** حوّل الحالة إلى نص مهما كان نوعها (Enum/String/Null) */
    protected function normStatus(null|string|\BackedEnum $s): ?string
    {
        return $s instanceof \BackedEnum ? $s->value : ($s === null ? null : (string) $s);
    }

    public function created(ProductionTask $task): void
    {
        // لو المَهمة مُسندة عند الإنشاء
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
        }

        $statusNow = $this->normStatus($task->status);

        // لوج الإنشاء
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

        // تشغيل المؤقّت إن كانت في حالة عمل
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

            // إرسال للمالك الجديد => حدّث sent_to_owner_at وافرِغ received_by_owner_at
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
        }

        /*** 3) تأكيد استلام المالك (received_by_owner_at) ***/
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
        if (array_key_exists('status', $task->getDirty())) {
            $from = $this->normStatus($task->getOriginal('status'));
            $to   = $this->normStatus($task->status);

            $task->logs()->create([
                'type'        => 'status_changed',
                'data'        => compact('from', 'to'),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            // إدارة المؤقّت (الحالات الجديدة)
            $stopOnLeaveInProgress = [
                'materials_wait','materials_prep','materials_done',
                'on_hold','under_review','approved','rejected',
                'completed','cancelled',
            ];

            if ($to === 'in_progress' && $from !== 'in_progress') {
                // استئناف/بدء
                TaskTimerService::start($task, $from ? "resume_from_{$from}" : 'status_to_in_progress');
            }

            if ($from === 'in_progress' && in_array($to, $stopOnLeaveInProgress, true)) {
                TaskTimerService::stop($task, "status_to_{$to}");
            }

            // طوابع زمنية
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
        if (array_key_exists('due_date', $task->getDirty())) {
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

        /*** 6) تغيّر المواعيد المخططة (اختياري إن كانت الأعمدة موجودة) ***/
        if (Schema::hasColumn('production_tasks', 'planned_start_at') &&
            (array_key_exists('planned_start_at', $task->getDirty()) || array_key_exists('planned_end_at', $task->getDirty()) || array_key_exists('planned_install_at', $task->getDirty()))) {

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
}
