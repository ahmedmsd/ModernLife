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
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('نسبة الإنجاز', ($kpis['completion'] ?? 0).'%')
                ->description('المهام المكتملة من الإجمالي')
                ->icon('heroicon-o-check-badge'),

            Stat::make('متوسط زمن الإنجاز', ($kpis['avg_duration_h'] ?? $kpis['avg_duration'] ?? 0).' ساعة')
                ->description('من الإسناد حتى الإكمال')
                ->icon('heroicon-o-clock'),

            Stat::make('نسبة التأخير', ($kpis['delay_rate'] ?? 0).'%')
                ->description('مقارنة بالهدف المخطّط')
                ->icon('heroicon-o-exclamation-triangle'),

            // توسعات تحليلية
            Stat::make('WIP (غير مكتملة)', number_format($kpis['wip'] ?? 0))
                ->description('المهام الجارية الآن')
                ->icon('heroicon-o-bolt'),

            Stat::make('SLA في/قبل الموعد', ($kpis['sla_rate'] ?? 0).'%')
                ->description('نسبة الإغلاق في/قبل الاستحقاق')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('متوسط زمن الإنجاز', ($kpis['median_duration_h'] ?? $kpis['median_duration'] ?? 0).' ساعة')
                ->description('أقل حساسية للتطَرّفات')
                ->icon('heroicon-o-chart-pie'),
        ];
    }
}
