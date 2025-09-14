<?php

namespace App\Observers;

use App\Models\ProductionTask;
use App\Models\Employee;
use App\Notifications\TaskAssignedInAppNotification;
use App\Notifications\TaskAssignedNotification;
use App\Services\TaskTimerService;
use App\Support\Notify;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;

class ProductionTaskObserver
{
    protected function normStatus(null|string|\BackedEnum $s): ?string
    {
        return $s instanceof \BackedEnum ? $s->value : ($s === null ? null : (string) $s);
    }

    /**
     * قبل الحفظ: عند تغيير الإسناد نضبط الملكية الحالية للقسم تلقائيًا.
     * هذا يضمن أن updated() سيرصد تغيّر current_owner_* ويُسجل اللوجات ويُحدث الطوابع.
     */
    public function updating(ProductionTask $task): void
    {
        if ($task->isDirty('assigned_to_employee_id')) {
            // مالك المهمة يصبح مدير القسم، والمستخدم هو المستخدم المرتبط بالموظف المُسند إليه (إن وُجد)
            $ownerUserId = null;
            if ($task->assigned_to_employee_id) {
                $emp = Employee::with('user:id')->find($task->assigned_to_employee_id);
                $ownerUserId = $emp?->user?->id;
            }

            $task->current_owner_role    = 'department_manager';
            $task->current_owner_user_id = $ownerUserId;

            // لا نلمس sent_to_owner_at/received_by_owner_at هنا؛ سيتم ضبطها في updated() عند رصد التغيير.
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

            // ⚙️ كتحوّط: إذا أُنشئت المهمة مُسندة ولم تُحدد الملكية، اضبطها لمدير القسم.
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
            // حدّث assigned_at بهدوء
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

            $isDeptManagerOwnership =
                ($task->current_owner_role === 'department_manager') &&
                !empty($task->current_owner_user_id);

            if ($isDeptManagerOwnership) {
                // إن لم يكن المالك هو نفس الموظف المُسند إليه، أرسل إشعارًا لمدير القسم
                if (!$task->employee || $task->employee->user_id !== $task->current_owner_user_id) {
                    $tmp = clone $task;
                    $tmp->setRelation('department', $task->department()->first());
                    Notify::departmentManager($tmp, 'ownership');
                }
            }
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
        if ($task->wasChanged('status')) {
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

        /*** 6) تغيّر المواعيد المخططة (اختياري إن كانت الأعمدة موجودة) ***/
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
            $dept = $task->department()->with(['manager', 'managerEmployee.user'])->first();
            $managerUser = $dept?->manager ?: ($dept?->managerEmployee?->user);

            if ($managerUser) {
                $url = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $task->project_id]);

                FNotification::make()
                    ->title('مهمة انتقلت إلى قسمك')
                    ->body("المهمة (#{$task->id}) على المشروع #{$task->project_id}")
                    ->icon('heroicon-o-arrow-right')
                    ->info()
                    ->actions([ FAction::make('عرض المشروع')->button()->url($url) ])
                    ->sendToDatabase($managerUser);

                // (اختياري) بريد:
                // $managerUser->notify(new \App\Notifications\DepartmentTaskMail($task, 'reassigned'));
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
