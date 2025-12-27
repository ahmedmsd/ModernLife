<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Support\Reports\{ReportFilters, ReportService};
use App\Filament\Widgets\Reports\Concerns\UsesPerformanceFilters;

class KPICards extends BaseWidget
{
    use UsesPerformanceFilters;

    protected ?string $heading = 'المؤشرات الرئيسية';

    protected function getStats(): array
    {
        $svc  = new ReportService(ReportFilters::fromArray($this->filters));
        $kpis = $svc->kpis();

        return [
            Stat::make('إجمالي المهام', number_format($kpis['total'] ?? 0))
                ->description('خلال الفترة المحددة')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('gray')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('نسبة الإنجاز', ($kpis['completion'] ?? 0).'%')
                ->description('المهام المكتملة من الإجمالي')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success')
                ->icon('heroicon-o-check-badge'),

            Stat::make('متوسط زمن الإنجاز', ($kpis['avg_duration'] ?? 0).' ساعة')
                ->description('من الإسناد حتى الإكمال')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info')
                ->icon('heroicon-o-clock'),

            Stat::make('نسبة التأخير', ($kpis['delay_rate'] ?? 0).'%')
                ->description('مقارنة بالهدف المخطّط')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color(($kpis['delay_rate'] ?? 0) > 15 ? 'danger' : 'warning')
                ->icon('heroicon-o-exclamation-triangle'),

            Stat::make('المهام الجارية (WIP)', number_format($kpis['wip'] ?? 0))
                ->description('المهام غير المكتملة حالياً')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('secondary')
                ->icon('heroicon-o-bolt'),

            Stat::make('الالتزام بالموعد (SLA)', ($kpis['sla_rate'] ?? 0).'%')
                ->description('نسبة الإغلاق قبل الاستحقاق')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color(($kpis['sla_rate'] ?? 0) > 80 ? 'success' : 'info')
                ->icon('heroicon-o-chart-bar'),
        ];
    }
}
