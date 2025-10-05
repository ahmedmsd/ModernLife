<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\MaterialRequest;
use App\Models\ProductionRequest;
use App\Models\ProductionTask;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MainStats extends BaseWidget
{
    protected static ?int $sort = 10;
    protected int|string|array $columnSpan = ['default' => 2, 'lg' => 2];
    protected function getColumns(): int { return 3; }

    protected function getStats(): array
    {
        $u   = auth()->user();
        $uid = $u?->id;

        $terminalReqStatuses = ['completed','cancelled','rejected','approved_final'];
        $activeTaskStatuses  = ['pending','in_progress','materials_prep','materials_done','waiting_production','under_review'];

        $stats = [];

        // ========== عام للجميع ==========
        $myRequests = ProductionRequest::query()
            ->where(fn($q) => $q->where('created_by', $uid)->orWhere('current_owner_user_id', $uid))
            ->whereNotIn('phase_status', $terminalReqStatuses)
            ->count();

        $myTasks = ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereIn('status', $activeTaskStatuses)
            ->count();

        $stats[] = Stat::make('طلباتي النشطة', $this->nf($myRequests))
            ->icon('heroicon-o-clipboard-document-list')->color('primary');

        $stats[] = Stat::make('مهامي الحالية', $this->nf($myTasks))
            ->icon('heroicon-o-briefcase')->color('info');

        // ========== مدير القسم ==========
        if ($u?->hasRole('department_manager')) {
            $deptIds = Department::query()
                ->where(fn($q) => $q->where('manager_id', $uid))
                ->pluck('dept_id')->toArray();

            $deptTasks = ProductionTask::query()
                ->whereIn('department_id', $deptIds)
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            $stats[] = Stat::make('مهام أقسامي', $this->nf($deptTasks))
                ->icon('heroicon-o-building-office-2')->color('success');
        }

        // ========== مدير المصنع ==========
        if ($u?->hasRole('factory_manager')) {
            $factoryRequests = ProductionRequest::query()
                ->where('current_owner_role', 'factory_manager')
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();

            $factoryTasks = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            $stats[] = Stat::make('طلبات المصنع', $this->nf($factoryRequests))
                ->icon('heroicon-o-document-text')->color('secondary');

            $stats[] = Stat::make('مهام المصنع', $this->nf($factoryTasks))
                ->icon('heroicon-o-queue-list')->color('gray');
        }

        // ========== مدير المشتريات ==========
        if ($u?->hasRole('purchasing_manager')) {
            $pendingMaterials = MaterialRequest::query()
                ->whereIn('status', ['requested','approved'])
                ->whereNull('provided_at')
                ->count();

            $stats[] = Stat::make('طلبات خامات معلّقة', $this->nf($pendingMaterials))
                ->icon('heroicon-o-truck')->color('warning');
        }

        // ========== مدير المعرض ==========
        if ($u?->hasRole('showroom_manager')) {
            $showroomRequests = ProductionRequest::query()
                ->where('current_owner_role', 'showroom_manager')
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();

            $stats[] = Stat::make('طلبات المعرض الجارية', $this->nf($showroomRequests))
                ->icon('heroicon-o-building-storefront')->color('success');
        }

        // ========== الأدمن / المشرف ==========
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
}
