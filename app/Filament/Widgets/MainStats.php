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

        // معارض يديرها هذا الموظف: showrooms.manager_id = employee_id
        $managedShowroomIds = [];
        if ($employeeId) {
            $managedShowroomIds = Showroom::query()
                ->where('manager_id', $employeeId)
                ->pluck('id')
                ->all();
        }

        // القوائم المرجعية
        $terminalReqStatuses = ['completed','cancelled','rejected','approved_final','on_hold'];
        $activeTaskStatuses  = [
            'pending','assigned','received','under_review','approved','rejected',
            'in_progress','materials_wait','materials_prep','materials_done','waiting_production'
        ];

        $now = Carbon::now();

        $stats = [];

        /* =================== بطاقات شخصية لكل مستخدم =================== */

        // طلباتي النشطة
        $myRequests = ProductionRequest::query()
            ->where(fn($q) => $q->where('created_by', $uid)->orWhere('current_owner_user_id', $uid))
            ->whereNotIn('phase_status', $terminalReqStatuses)
            ->count();

        // مهامي الحالية
        $myTasks = ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereIn('status', $activeTaskStatuses)
            ->count();

        // مهامي المتأخرة
        $myOverdue = ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereIn('status', $activeTaskStatuses)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $now)
            ->count();

        // مهامي المكتملة
        $myCompleted = ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereIn('status', ['completed','closed'])
            ->count();

        // متوسط زمن إنجاز "مهامي"
        $myAvgMins = (int) (ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
            ->value('avg_mins') ?? 0);

        $stats[] = Stat::make('طلباتي النشطة', $this->nf($myRequests))
            ->icon('heroicon-o-clipboard-document-list')->color('primary');

        $stats[] = Stat::make('مهامي الحالية', $this->nf($myTasks))
            ->icon('heroicon-o-briefcase')->color('info');

        $stats[] = Stat::make('مهامي المتأخرة', $this->nf($myOverdue))
            ->icon('heroicon-o-exclamation-triangle')->color('danger');

        $stats[] = Stat::make('مهامي المكتملة', $this->nf($myCompleted))
            ->icon('heroicon-o-check-badge')->color('success');

        $stats[] = Stat::make('متوسط زمن إنجاز مهامي', $this->fmtDuration($myAvgMins))
            ->icon('heroicon-o-clock')->color('gray');

        /* =================== department_manager =================== */

        if ($u?->hasRole('department_manager')) {
            // مهام القسم الجارية
            $deptActive = ProductionTask::query()
                ->when($deptId, fn($q) => $q->where('department_id', $deptId), fn($q) => $q->whereRaw('1=0'))
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            // مهام القسم المتأخرة
            $deptOverdue = ProductionTask::query()
                ->when($deptId, fn($q) => $q->where('department_id', $deptId), fn($q) => $q->whereRaw('1=0'))
                ->whereIn('status', $activeTaskStatuses)
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            // مهام القسم المكتملة
            $deptCompleted = ProductionTask::query()
                ->when($deptId, fn($q) => $q->where('department_id', $deptId), fn($q) => $q->whereRaw('1=0'))
                ->whereIn('status', ['completed','closed'])
                ->count();

            // متوسط زمن إنجاز مهام القسم
            $deptAvgMins = (int) (ProductionTask::query()
                ->when($deptId, fn($q) => $q->where('department_id', $deptId), fn($q) => $q->whereRaw('1=0'))
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                ->value('avg_mins') ?? 0);

            $stats[] = Stat::make('مهام القسم (جارية)', $this->nf($deptActive))
                ->icon('heroicon-o-building-office-2')->color('info');

            $stats[] = Stat::make('مهام القسم (متأخرة)', $this->nf($deptOverdue))
                ->icon('heroicon-o-exclamation-triangle')->color('danger');

            $stats[] = Stat::make('مهام القسم (مكتملة)', $this->nf($deptCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('متوسط زمن إنجاز القسم', $this->fmtDuration($deptAvgMins))
                ->icon('heroicon-o-clock')->color('gray');
        }

        /* =================== showroom_manager =================== */

        if ($u?->hasRole('showroom_manager')) {
            // الطلبات الجارية للمعارض المُدارة
            $showroomRequestsActive = ProductionRequest::query()
                ->whereIn('showroom_id', $managedShowroomIds ?: [-1])
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();

            // الطلبات المكتملة/منتهية
            $showroomRequestsClosed = ProductionRequest::query()
                ->whereIn('showroom_id', $managedShowroomIds ?: [-1])
                ->whereIn('phase_status', $terminalReqStatuses)
                ->count();

            // المهام الجارية للمعارض
            $showroomTasksActive = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                    $w->whereIn('showroom_id', $managedShowroomIds ?: [-1]);
                })
                ->count();

            // المهام المتأخرة للمعارض
            $showroomTasksOverdue = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                    $w->whereIn('showroom_id', $managedShowroomIds ?: [-1]);
                })
                ->count();

            // المهام المكتملة للمعارض
            $showroomTasksCompleted = ProductionTask::query()
                ->whereIn('status', ['completed','closed'])
                ->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                    $w->whereIn('showroom_id', $managedShowroomIds ?: [-1]);
                })
                ->count();

            // متوسط زمن إنجاز مهام المعارض
            $showroomAvgMins = (int) (ProductionTask::query()
                ->whereNotNull('completed_at')
                ->whereHas('project.productionRequest', function (Builder $w) use ($managedShowroomIds) {
                    $w->whereIn('showroom_id', $managedShowroomIds ?: [-1]);
                })
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                ->value('avg_mins') ?? 0);

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
        }

        /* =================== quality_manager =================== */

        if ($u?->hasRole('quality_manager')) {
            // المهام بانتظار الجودة (مالك حالي)
            $qaPending = ProductionTask::query()
                ->where('current_owner_role', 'quality_manager')
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            // المهام المكتملة (بعد الجودة عادةً)
            $qaCompleted = ProductionTask::query()
                ->whereIn('status', ['completed','closed'])
                ->count();

            // متوسط زمن الإنجاز العام (يمكن تخصيصه لمرحلة الجودة لاحقًا عبر TaskLog)
            $qaAvgMins = (int) (ProductionTask::query()
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                ->value('avg_mins') ?? 0);

            $stats[] = Stat::make('بانتظار إجراء الجودة', $this->nf($qaPending))
                ->icon('heroicon-o-shield-check')->color('warning');

            $stats[] = Stat::make('مهام أُنجزت (عامة)', $this->nf($qaCompleted))
                ->icon('heroicon-o-check-badge')->color('success');

            $stats[] = Stat::make('متوسط زمن الإنجاز (عام)', $this->fmtDuration($qaAvgMins))
                ->icon('heroicon-o-clock')->color('gray');
        }

        /* =================== factory_manager =================== */

        if ($u?->hasRole('factory_manager')) {
            $factoryRequestsActive = ProductionRequest::query()
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();

            $factoryTasksActive = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            $factoryTasksOverdue = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->whereNotNull('due_date')
                ->where('due_date', '<', $now)
                ->count();

            $factoryTasksCompleted = ProductionTask::query()
                ->whereIn('status', ['completed','closed'])
                ->count();

            $factoryAvgMins = (int) (ProductionTask::query()
                ->whereNotNull('completed_at')
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, completed_at)) as avg_mins')
                ->value('avg_mins') ?? 0);

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

        /* =================== purchasing_manager =================== */

        if ($u?->hasRole('purchasing_manager')) {
            $pendingMaterials = MaterialRequest::query()
                ->whereIn('status', ['requested','approved'])
                ->whereNull('provided_at')
                ->count();

            $stats[] = Stat::make('طلبات خامات معلّقة', $this->nf($pendingMaterials))
                ->icon('heroicon-o-truck')->color('warning');
        }

        /* =================== admin / super-admin (كلّي) =================== */

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
