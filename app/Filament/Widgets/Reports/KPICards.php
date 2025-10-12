<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Support\Reports\{ReportFilters, ReportService};

class KPICards extends BaseWidget
{
    protected ?string $heading = 'المؤشرات الرئيسية';

    protected function getStats(): array
    {
        $svc  = new ReportService(ReportFilters::fromRequest());
        $kpis = $svc->kpis();

        return [
            Stat::make('إجمالي المهام', number_format($kpis['total']))
                ->description('خلال الفترة المحددة')
                ->icon('heroicon-o-clipboard-document-list'),

            Stat::make('نسبة الإنجاز', $kpis['completion'].'%')
                ->description('المهام المكتملة من الإجمالي')
                ->icon('heroicon-o-check-badge'),

            Stat::make('متوسط زمن الإنجاز', $kpis['avg_duration'].' ساعة')
                ->description('من الإسناد حتى الإكمال')
                ->icon('heroicon-o-clock'),

            Stat::make('نسبة التأخير', $kpis['delay_rate'].'%')
                ->description('مقارنة بالهدف المخطّط')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
