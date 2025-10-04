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

    protected int|string|array $columnSpan = [
        'default' => 2,
        'lg'      => 2,
    ];

    /** عدد الأعمدة داخل الودجت (كل صف يعرض 3 بطاقات) */
    protected function getColumns(): int
    {
        return 3;
    }

    /**
     * تجميع البطاقات ديناميكيًا حسب الدور والمستخدم الحالي.
     */
    protected function getStats(): array
    {
        $u   = auth()->user();
        $uid = $u?->id;

        // حدد حالات "غير نهائية" للطلبات/المهام المستخدمة في بعض المؤشرات
        $terminalReqStatuses  = ['completed', 'cancelled', 'rejected', 'approved_final'];
        $activeTaskStatuses   = ['in_progress', 'materials_wait', 'materials_prep', 'materials_done', 'waiting_production', 'under_review'];

        // === 1) بطاقات "أرقامي" (My KPIs) — مقيّدة بالمستخدم نفسه ===
        $myRequestsCount = ProductionRequest::query()
            ->where(fn($q) => $q->where('created_by', $uid)->orWhere('current_owner_user_id', $uid))
            ->whereNotIn('phase_status', $terminalReqStatuses)
            ->count();

        $myTasksCount = ProductionTask::query()
            ->where('current_owner_user_id', $uid)
            ->whereIn('status', $activeTaskStatuses)
            ->count();

        $myOpenMaterialsCount = MaterialRequest::query()
            ->whereIn('status', ['requested', 'approved'])
            ->whereNull('provided_at')
            ->where(function ($q) use ($uid) {
                $q->where('requested_by', $uid)
                    ->orWhere('provided_by', $uid)
                    ->orWhereHas('task', fn($t) => $t->where('current_owner_user_id', $uid));
            })
            ->count();

        $stats = [
            // طلباتي الجارية
            Stat::make('طلباتي تحت الإجراء', $this->nf($myRequestsCount))
                ->icon('heroicon-o-clipboard-document-list')
                ->color('primary')
                ->description('طلبات التصنيع الخاصة بي (غير نهائية)'),

            // مهامي الحالية
            Stat::make('مهامي الحالية', $this->nf($myTasksCount))
                ->icon('heroicon-o-briefcase')
                ->color('info')
                ->description('المهام المملوكة لي الآن'),

            // مشترياتي قيد المعالجة
            Stat::make('طلبات خاماتي', $this->nf($myOpenMaterialsCount))
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->description('طلبات خامات تخصني (قيد المعالجة)'),
        ];

        // === 2) إضافات حسب الدور ===
        if ($u?->hasAnyRole(['department_manager'])) {
            // أقسام يديرها المستخدم (manager_user_id/head_user_id)
            $deptIds = Department::query()
                ->where(fn($q) => $q->where('manager_id', $uid))
                ->pluck('dept_id') // المفتاح عندك اسمه dept_id في العلاقات
                ->all();

            $deptActiveTasks = ProductionTask::query()
                ->whereIn('department_id', $deptIds)
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            $stats[] = Stat::make('مهام أقسامي النشطة', $this->nf($deptActiveTasks))
                ->icon('heroicon-o-building-office-2')
                ->color('success')
                ->description('إجمالي المهام الجارية في أقسامي');
        }

        if ($u?->hasAnyRole(['factory_manager'])) {
            $factoryOpenRequests = ProductionRequest::query()
                ->whereNotIn('phase_status', $terminalReqStatuses)
                ->count();

            $factoryActiveTasks = ProductionTask::query()
                ->whereIn('status', $activeTaskStatuses)
                ->count();

            $stats[] = Stat::make('طلبات المصنع الجارية', $this->nf($factoryOpenRequests))
                ->icon('heroicon-o-document-text')
                ->color('secondary')
                ->description('جميع الطلبات غير النهائية');

            $stats[] = Stat::make('مهام المصنع الجارية', $this->nf($factoryActiveTasks))
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->description('جميع المهام النشطة عبر الأقسام');
        }

        if ($u?->hasAnyRole(['purchasing_manager'])) {
            $purchasingOpen = MaterialRequest::query()
                ->whereIn('status', ['requested', 'approved'])
                ->whereNull('provided_at')
                ->count();

            $stats[] = Stat::make('طلبات خامات قيد المعالجة', $this->nf($purchasingOpen))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->description('إجمالي قيد الاعتماد/التوريد');
        }

        // === 3) بطاقات شاملة للأدمن ===
        if ($u?->hasAnyRole(['admin', 'super-admin'])) {
            $stats[] = Stat::make('الطلبات (إجمالي)', $this->nf(ProductionRequest::count()))
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->description('إجمالي طلبات التصنيع');

            $stats[] = Stat::make('المشروعات (إجمالي)', $this->nf(Project::count()))
                ->icon('heroicon-o-briefcase')
                ->color('info')
                ->description('إجمالي المشروعات');

            $stats[] = Stat::make('العملاء (إجمالي)', $this->nf(Client::count()))
                ->icon('heroicon-o-users')
                ->color('primary')
                ->description('إجمالي العملاء');

            $stats[] = Stat::make('الأقسام (إجمالي)', $this->nf(Department::count()))
                ->icon('heroicon-o-building-office-2')
                ->color('gray')
                ->description('إجمالي الأقسام');

            $stats[] = Stat::make('الموظفون (إجمالي)', $this->nf(Employee::count()))
                ->icon('heroicon-o-user')
                ->color('success')
                ->description('إجمالي الموظفين');

            $stats[] = Stat::make('المهام (إجمالي)', $this->nf(ProductionTask::count()))
                ->icon('heroicon-o-briefcase')
                ->color('secondary')
                ->description('إجمالي المهام');
        }

        return $stats;
    }

    /**
     * تنسيق الأرقام بشكل موحّد (بدون كسور)
     */
    private function nf(int $n): string
    {
        return number_format($n);
    }
}
