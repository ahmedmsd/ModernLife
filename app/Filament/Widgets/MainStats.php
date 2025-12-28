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
        $u = Auth::user();
        if (!$u || !$u->hasAnyRole(['admin', 'super-admin', 'owner', 'factory_manager'])) {
            return $this->getPersonalStats($u);
        }

        $stats = [];
        
        // 1. Production Requests
        $prActive = cache()->remember('stats:pr_active', 60, fn() => ProductionRequest::active()->count());
        $prDone   = cache()->remember('stats:pr_done', 60, fn() => ProductionRequest::completed()->count());
        
        $stats[] = Stat::make('طلبات التصنيع (الجارية)', $this->nf($prActive))
            ->description('قيد المراجعة أو التنفيذ')
            ->icon('heroicon-o-document-plus')
            ->color('primary');
            
        $stats[] = Stat::make('طلبات التصنيع (المكتملة)', $this->nf($prDone))
            ->description('إجمالي ما تم إنجازه')
            ->icon('heroicon-o-document-check')
            ->color('success');

        // 2. Production Tasks
        $taskActive = cache()->remember('stats:task_active', 60, fn() => ProductionTask::whereIn('status', ['pending','assigned','received','under_review','approved','in_progress','materials_wait','materials_prep','materials_done','waiting_production'])->count());
        $taskDone   = cache()->remember('stats:task_done', 60, fn() => ProductionTask::whereIn('status', ['completed', 'closed'])->count());

        $stats[] = Stat::make('مهمات المصنع (قيد العمل)', $this->nf($taskActive))
            ->description('عمليات جارية في الأقسام')
            ->icon('heroicon-o-cog-6-tooth')
            ->color('info');

        $stats[] = Stat::make('مهمات المصنع (المنجزة)', $this->nf($taskDone))
            ->description('مهمات تم تسليمها نهائياً')
            ->icon('heroicon-o-check-circle')
            ->color('success');

        // 3. Maintenance Requests
        $maintActive = cache()->remember('stats:maint_active', 60, fn() => \App\Models\MaintenanceRequest::active()->count());
        $maintDone   = cache()->remember('stats:maint_done', 60, fn() => \App\Models\MaintenanceRequest::completed()->count());

        $stats[] = Stat::make('طلبات الصيانة (النشطة)', $this->nf($maintActive))
            ->description('تحتاج متابعة أو زيارة')
            ->icon('heroicon-o-wrench-screwdriver')
            ->color('warning');

        $stats[] = Stat::make('طلبات الصيانة (المكتملة)', $this->nf($maintDone))
            ->description('إجمالي الصيانات المنتهية')
            ->icon('heroicon-o-shield-check')
            ->color('success');

        // 4. Projects
        $projActive = cache()->remember('stats:proj_active', 60, fn() => Project::where('status', 'in_progress')->count());
        $projDone   = cache()->remember('stats:proj_done', 60, fn() => Project::where('status', 'completed')->count());

        $stats[] = Stat::make('المشاريع (تحت التنفيذ)', $this->nf($projActive))
            ->description('مشاريع فعالة حالياً')
            ->icon('heroicon-o-briefcase')
            ->color('primary');

        $stats[] = Stat::make('المشاريع (المنتهية)', $this->nf($projDone))
            ->description('مشاريع تم إغلاقها')
            ->icon('heroicon-o-archive-box')
            ->color('success');

        // 5. Material Requests
        $matPending = cache()->remember('stats:mat_pending', 60, fn() => \App\Models\MaterialRequest::whereNull('provided_at')->count());
        $matDone    = cache()->remember('stats:mat_done', 60, fn() => \App\Models\MaterialRequest::whereNotNull('provided_at')->count());

        $stats[] = Stat::make('طلبات المواد (المعلقة)', $this->nf($matPending))
            ->description('بانتظار توفير الخامات')
            ->icon('heroicon-o-truck')
            ->color('danger');

        $stats[] = Stat::make('طلبات المواد (الموفرة)', $this->nf($matDone))
            ->description('تم صرفها للمصنع')
            ->icon('heroicon-o-shopping-cart')
            ->color('success');

        return $stats;
    }

    protected function getPersonalStats($u): array
    {
        if (!$u) return [];
        $uid = $u->id;
        
        $myTasks = ProductionTask::where(function($q) use ($uid) {
                $q->where('current_owner_user_id', $uid)
                  ->orWhere('assigned_to_user_id', $uid);
            })
            ->whereNotIn('status', ['completed', 'closed', 'cancelled'])
            ->count();

        $myDone  = ProductionTask::where('assigned_to_user_id', $uid)
            ->whereIn('status', ['completed', 'closed'])
            ->count();

        return [
            Stat::make('مهامي الجارية', $this->nf($myTasks))
                ->icon('heroicon-o-user-circle')
                ->color('primary'),
            Stat::make('مهامي المكتملة', $this->nf($myDone))
                ->icon('heroicon-o-check-badge')
                ->color('success'),
        ];
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
