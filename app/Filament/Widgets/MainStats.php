<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\MaterialRequest;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use App\Models\Project;
use App\Models\Showroom;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class MainStats extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = ['default' => 2, 'lg' => 2];
    protected function getColumns(): int { return 3; }

    protected function getStats(): array
    {
        $u   = Auth::user();
        $uid = $u?->id;

        // علاقات الموظف
        $employeeId = null;
        $deptId     = null;

        if ($u instanceof User) {
            $u->loadMissing('employee');
            $employeeId = $u->employee?->employee_id;
            $deptId     = $u->employee?->department_id;
        }

        // أقسام يديرها المستخدم (قد تكون أكثر من قسم)
        $managedDeptIds = Department::query()
            ->where('manager_id', $u->id)
            ->pluck('dept_id')
            ->toArray();

        // fallback: أضف قسم الموظف إن لم يكن للمستخدم أقسام مُدارة أصلاً
        if (empty($managedDeptIds) && $deptId) {
            $managedDeptIds[] = $deptId;
        }

        $managedShowroomIds = [];
        if ($employeeId) {
            $managedShowroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id')
                ->all();
        }

        $terminalReqStatuses = ['completed','cancelled','rejected','approved_final','on_hold'];
        $activeTaskStatuses  = [
            'pending','assigned','received','under_review','approved','rejected',
            'in_progress','materials_wait','materials_prep','materials_done','waiting_production'
        ];

        $now = Carbon::now();

        $stats = [];

        /*
         * ------------- بطاقات عامة للمستخدم -------------
         */

        // استخدم cache قصير للطلبات المشتركة ثقيلة الحوسبة
        $myRequests = cache()->remember("user:{$uid}:myRequests", 60, function() use ($uid, $terminalReqStatuses) {
            return ProductionRequest::query()
                ->where(fn($q) => $q->where('created_by', $uid)->orWhere('current_owner_user_id', $uid))
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();
        });

        $myTasks = cache()->remember("user:{$uid}:myTasks", 30, function() use ($uid, $activeTaskStatuses) {
            return ProductionTask::query()
                ->where('current_owner_user_id', $uid)
                ->whereIn('status', $activeTaskStatuses)
                ->count();
        });

        $myOverdue = cache()->remember("user:{$uid}:myOverdue", 30, function() use ($uid, $activeTaskStatuses, $now) {
            return ProductionTask::query()
                ->where('current_owner_user_id', $uid)
                ->whereIn('status', $activeTaskStatuses)
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();
        });

        $myCompleted = cache()->remember("user:{$uid}:myCompleted", 60, function() use ($uid) {
            return ProductionTask::query()
                ->where('current_owner_user_id', $uid)
                ->whereIn('status', ['completed','closed'])
                ->count();
        });

        $myAvgMins = (int) cache()->remember("user:{$uid}:myAvgMins", 120, function() use ($uid) {
            return (int) (ProductionTask::query()
                ->where('current_owner_user_id', $uid)
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                ->value('avg_mins') ?? 0);
        });

        // متوسط زمن الاستجابة (time-to-ack) للمهمات التي استُدعي فيها ارسال/استلام
        $myAvgTimeToAck = (int) cache()->remember("user:{$uid}:time_to_ack", 120, function() use ($uid) {
            return (int) (ProductionTask::query()
                ->where('current_owner_user_id', $uid)
                ->whereNotNull('sent_to_owner_at')
                ->whereNotNull('received_by_owner_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, sent_to_owner_at, received_by_owner_at)) as avg_mins')
                ->value('avg_mins') ?? 0);
        });

        $stats[] = Stat::make('طلباتي النشطة', $this->nf($myRequests))
            ->icon('heroicon-o-document-text')->color('primary');

        $stats[] = Stat::make('مهامي الحالية', $this->nf($myTasks))
            ->icon('heroicon-o-briefcase')->color('info');

        $stats[] = Stat::make('مهامي المتأخرة', $this->nf($myOverdue))
            ->icon('heroicon-o-exclamation-triangle')->color('danger');

        $stats[] = Stat::make('مهامي المكتملة', $this->nf($myCompleted))
            ->icon('heroicon-o-check-badge')->color('success');

        $stats[] = Stat::make('متوسط زمن إنجاز مهامي', $this->fmtDuration($myAvgMins))
            ->icon('heroicon-o-clock')->color('gray');

        $stats[] = Stat::make('متوسط استجابة الاستلام (دق)', $this->fmtDuration($myAvgTimeToAck))
            ->icon('heroicon-o-clock')->color('secondary');

        /*
         * ------------- Department Manager -------------
         */
        if ($u?->hasRole('department_manager')) {

            // استخدم managedDeptIds (قد يدير أكثر من قسم)
            $deptIds = $managedDeptIds ?: [];

            $deptScope = ProductionTask::query()
                ->when(!empty($deptIds), fn($q) => $q->whereIn('department_id', $deptIds), fn($q) => $q->whereRaw('1=0'));

            $deptActive = cache()->remember("dept:".md5(implode(',', $deptIds)).":active", 60, function() use ($deptScope, $activeTaskStatuses) {
                return (clone $deptScope)->whereIn('status', $activeTaskStatuses)->count();
            });

            $deptOverdue = cache()->remember("dept:".md5(implode(',', $deptIds)).":overdue", 60, function() use ($deptScope, $activeTaskStatuses, $now) {
                return (clone $deptScope)
                    ->whereIn('status', $activeTaskStatuses)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $now)
                    ->count();
            });

            $deptCompleted = cache()->remember("dept:".md5(implode(',', $deptIds)).":completed", 120, function() use ($deptScope) {
                return (clone $deptScope)->whereIn('status', ['completed','closed'])->count();
            });

            $deptAvgMins = (int) cache()->remember("dept:".md5(implode(',', $deptIds)).":avg", 300, function() use ($deptScope) {
                return (int) ((clone $deptScope)
                    ->whereNotNull('completed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                    ->value('avg_mins') ?? 0);
            });

            // نسبة الإنجاز للقسم (completed / total)
            $deptTotal = cache()->remember("dept:".md5(implode(',', $deptIds)).":total", 120, function() use ($deptScope) {
                return (clone $deptScope)->count();
            });

            $deptPercentComplete = $deptTotal > 0 ? round($deptCompleted / $deptTotal * 100) : 0;

            // rework indicators
            $deptReworkCount = cache()->remember("dept:".md5(implode(',', $deptIds)).":rework_count", 120, function() use ($deptScope) {
                return (clone $deptScope)->where('rework_cycle', '>', 0)->count();
            });

            $stats[] = Stat::make('مهام القسم (جارية)', $this->nf($deptActive))
                ->icon('heroicon-o-building-office-2')->color('info');

            $stats[] = Stat::make('مهام القسم (متأخرة)', $this->nf($deptOverdue))
                ->icon('heroicon-o-exclamation-triangle')->color('danger');

            $stats[] = Stat::make('مهام القسم (مكتملة)', $this->nf($deptCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('نسبة إتمام القسم', $deptPercentComplete . '%')
                ->icon('heroicon-o-chart-pie')->color('secondary');

            $stats[] = Stat::make('متوسط زمن إنجاز القسم', $this->fmtDuration($deptAvgMins))
                ->icon('heroicon-o-clock')->color('gray');

            $stats[] = Stat::make('مهام القسم في إعادة عمل', $this->nf($deptReworkCount))
                ->icon('heroicon-o-arrow-uturn-left')->color('warning');
        }

        /*
         * ------------- Showroom Manager -------------
         */
        if ($u?->hasRole('showroom_manager')) {

            $srIds = $managedShowroomIds ?: [-1];

            $showroomRequestsActive = cache()->remember("sr:".md5(implode(',', $srIds)).":requests_active", 60, function() use ($srIds, $terminalReqStatuses) {
                return ProductionRequest::query()
                    ->whereIn('showroom_id', $srIds)
                    ->whereNotIn('phase_status', $terminalReqStatuses)
                    ->count();
            });

            $showroomRequestsClosed = cache()->remember("sr:".md5(implode(',', $srIds)).":requests_closed", 300, function() use ($srIds, $terminalReqStatuses) {
                return ProductionRequest::query()
                    ->whereIn('showroom_id', $srIds)
                    ->whereIn('phase_status', $terminalReqStatuses)
                    ->count();
            });

            $showroomTasksActive = cache()->remember("sr:".md5(implode(',', $srIds)).":tasks_active", 60, function() use ($srIds, $activeTaskStatuses) {
                return ProductionTask::query()
                    ->whereIn('status', $activeTaskStatuses)
                    ->whereHas('project.productionRequest', function (Builder $w) use ($srIds) {
                        $w->whereIn('showroom_id', $srIds);
                    })
                    ->count();
            });

            $showroomTasksOverdue = cache()->remember("sr:".md5(implode(',', $srIds)).":tasks_overdue", 60, function() use ($srIds, $activeTaskStatuses, $now) {
                return ProductionTask::query()
                    ->whereIn('status', $activeTaskStatuses)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $now)
                    ->whereHas('project.productionRequest', function (Builder $w) use ($srIds) {
                        $w->whereIn('showroom_id', $srIds);
                    })
                    ->count();
            });

            $showroomTasksCompleted = cache()->remember("sr:".md5(implode(',', $srIds)).":tasks_completed", 120, function() use ($srIds) {
                return ProductionTask::query()
                    ->whereIn('status', ['completed','closed'])
                    ->whereHas('project.productionRequest', function (Builder $w) use ($srIds) {
                        $w->whereIn('showroom_id', $srIds);
                    })
                    ->count();
            });

            $showroomAvgMins = (int) cache()->remember("sr:".md5(implode(',', $srIds)).":avg", 300, function() use ($srIds) {
                return (int) (ProductionTask::query()
                    ->whereNotNull('completed_at')
                    ->whereHas('project.productionRequest', function (Builder $w) use ($srIds) {
                        $w->whereIn('showroom_id', $srIds);
                    })
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                    ->value('avg_mins') ?? 0);
            });

            // completed last 30 days (trend)
            $showroomCompleted30d = cache()->remember("sr:".md5(implode(',', $srIds)).":completed_30d", 60, function() use ($srIds) {
                return ProductionTask::query()
                    ->whereIn('status', ['completed','closed'])
                    ->whereHas('project.productionRequest', function (Builder $w) use ($srIds) {
                        $w->whereIn('showroom_id', $srIds);
                    })
                    ->where('completed_at', '>=', Carbon::now()->subDays(30))
                    ->count();
            });

            $stats[] = Stat::make('طلبات المعارض (جارية)', $this->nf($showroomRequestsActive))
                ->icon('heroicon-o-building-storefront')->color('primary');

            $stats[] = Stat::make('طلبات المعارض (منتهية)', $this->nf($showroomRequestsClosed))
                ->icon('heroicon-o-archive-box')->color('gray');

            $stats[] = Stat::make('مهام المعارض (جارية)', $this->nf($showroomTasksActive))
                ->icon('heroicon-o-clipboard-document-check')->color('info');

            $stats[] = Stat::make('مهام المعارض (متأخرة)', $this->nf($showroomTasksOverdue))
                ->icon('heroicon-o-exclamation-triangle')->color('danger');

            $stats[] = Stat::make('مهام المعارض (مكتملة)', $this->nf($showroomTasksCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('متوسط زمن مهام المعارض', $this->fmtDuration($showroomAvgMins))
                ->icon('heroicon-o-clock')->color('gray');

            $stats[] = Stat::make('إنجازات 30 يوم', $this->nf($showroomCompleted30d))
                ->icon('heroicon-o-chart-bar')->color('secondary');
        }

        /*
         * ------------- Quality Manager -------------
         */
        if ($u?->hasRole('quality_manager')) {
            $qaPending = cache()->remember("qa:pending", 30, function() use ($activeTaskStatuses) {
                return ProductionTask::query()
                    ->where('current_owner_role', 'quality_manager')
                    ->whereIn('status', $activeTaskStatuses)
                    ->count();
            });

            $qaCompleted = cache()->remember("qa:completed", 300, function() {
                return ProductionTask::query()
                    ->whereIn('status', ['completed','closed'])
                    ->count();
            });

            $qaAvgMins = (int) cache()->remember("qa:avg", 300, function() {
                return (int) (ProductionTask::query()
                    ->whereNotNull('completed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                    ->value('avg_mins') ?? 0);
            });

            $stats[] = Stat::make('بانتظار إجراء الجودة', $this->nf($qaPending))
                ->icon('heroicon-o-shield-check')->color('warning');

            $stats[] = Stat::make('مهام أُنجزت (عام)', $this->nf($qaCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('متوسط زمن الإنجاز (عام)', $this->fmtDuration($qaAvgMins))
                ->icon('heroicon-o-clock')->color('gray');
        }

        /*
         * ------------- Factory Manager -------------
         */
        if ($u?->hasRole('factory_manager')) {
            $factoryRequestsActive = cache()->remember("factory:requests_active", 60, function() use ($terminalReqStatuses) {
                return ProductionRequest::query()
                    ->whereNotIn('phase_status', $terminalReqStatuses)
                    ->count();
            });

            $factoryTasksActive = cache()->remember("factory:tasks_active", 60, function() use ($activeTaskStatuses) {
                return ProductionTask::query()
                    ->whereIn('status', $activeTaskStatuses)
                    ->count();
            });

            $factoryTasksOverdue = cache()->remember("factory:tasks_overdue", 60, function() use ($activeTaskStatuses, $now) {
                return ProductionTask::query()
                    ->whereIn('status', $activeTaskStatuses)
                    ->whereNotNull('due_date')
                    ->where('due_date', '<', $now)
                    ->count();
            });

            $factoryTasksCompleted = cache()->remember("factory:tasks_completed", 120, function() {
                return ProductionTask::query()
                    ->whereIn('status', ['completed','closed'])
                    ->count();
            });

            $factoryAvgMins = (int) cache()->remember("factory:avg", 300, function() {
                return (int) (ProductionTask::query()
                    ->whereNotNull('completed_at')
                    ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                    ->value('avg_mins') ?? 0);
            });

            $stats[] = Stat::make('طلبات المصنع (جارية)', $this->nf($factoryRequestsActive))
                ->icon('heroicon-o-document-text')->color('secondary');

            $stats[] = Stat::make('مهام المصنع (جارية)', $this->nf($factoryTasksActive))
                ->icon('heroicon-o-queue-list')->color('info');

            $stats[] = Stat::make('مهام المصنع (متأخرة)', $this->nf($factoryTasksOverdue))
                ->icon('heroicon-o-exclamation-triangle')->color('danger');

            $stats[] = Stat::make('مهام المصنع (مكتملة)', $this->nf($factoryTasksCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('متوسط زمن مهام المصنع', $this->fmtDuration($factoryAvgMins))
                ->icon('heroicon-o-clock')->color('gray');
        }

        /*
         * ------------- Purchasing Manager -------------
         */
        if ($u?->hasRole('purchasing_manager')) {
            $pendingMaterials = cache()->remember("purchasing:pending_mat", 30, function() {
                return MaterialRequest::query()
                    ->whereIn('status', ['requested','approved'])
                    ->whereNull('provided_at')
                    ->count();
            });

            $stats[] = Stat::make('طلبات خامات معلّقة', $this->nf($pendingMaterials))
                ->icon('heroicon-o-truck')->color('warning');
        }

        /*
         * ------------- Admin / Super-admin -------------
         */
        if ($u?->hasAnyRole(['admin','super-admin'])) {
            $stats[] = Stat::make('الطلبات الكلية', $this->nf(ProductionRequest::count()))
                ->icon('heroicon-o-document-text')->color('warning');
            $stats[] = Stat::make('المشروعات', $this->nf(Project::count()))
                ->icon('heroicon-o-briefcase')->color('info');
            $stats[] = Stat::make('العملاء', $this->nf(Client::count()))
                ->icon('heroicon-o-users')->color('primary');
            $stats[] = Stat::make('الأقسام', $this->nf(Department::count()))
                ->icon('heroicon-o-building-office-2')->color('gray');
            $stats[] = Stat::make('الموظفون', $this->nf(Employee::count()))
                ->icon('heroicon-o-user')->color('success');
            $stats[] = Stat::make('المهام', $this->nf(ProductionTask::count()))
                ->icon('heroicon-o-briefcase')->color('secondary');
        }

        return $stats;
    }

    private function nf(int $n): string
    {
        return number_format($n);
    }

    private function fmtDuration(int $mins): string
    {
        if ($mins <= 0) return '—';
        $days  = intdiv($mins, 1440);
        $hours = intdiv($mins % 1440, 60);
        $m     = $mins % 60;
        $parts = [];
        if ($days)  $parts[] = $days . ' يوم';
        if ($hours) $parts[] = $hours . ' ساعة';
        if ($m || (!$days && !$hours)) $parts[] = $m . ' دقيقة';
        return implode(' و ', $parts);
    }
}
