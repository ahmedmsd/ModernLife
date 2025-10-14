<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};
use App\Filament\Widgets\Reports\Concerns\UsesPerformanceFilters;

class CompletionTrendChart extends ChartWidget
{
    use UsesPerformanceFilters;

    protected static ?string $heading = 'ترند المهام المكتملة';
    protected static ?int $sort = 30;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromArray($this->filters));
        $d   = $svc->completionTrend(); // ['labels'=>[], 'data'=>[]]

        $palette = $this->palette(1);
        return [
            'datasets' => [[
                'label'           => 'مكتمل يوميًا',
                'data'            => $d['data'] ?? [],
                'borderColor'     => $palette['border'][0],
                'backgroundColor' => $palette['bg'][0],
                'tension'         => 0.35,
                'fill'            => true,
                'pointRadius'     => 2.5,
            ]],
            'labels' => $d['labels'] ?? [],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
