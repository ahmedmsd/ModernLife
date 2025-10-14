<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};
use App\Filament\Widgets\Reports\Concerns\UsesPerformanceFilters;

class StatusDistributionChart extends ChartWidget
{
    use UsesPerformanceFilters;

    protected static ?string $heading = 'توزيع الحالات';
    protected static ?int $sort = 40;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromArray($this->filters));
        $d   = $svc->statusDistribution(); // ['labels'=>[], 'data'=>[]]

        $palette = $this->palette(count($d['data'] ?? []));
        return [
            'datasets' => [[
                'label'           => 'الحالات',
                'data'            => $d['data'] ?? [],
                'backgroundColor' => $palette['bg'],
                'borderColor'     => $palette['border'],
                'borderWidth'     => 1,
            ]],
            'labels' => $d['labels'] ?? [],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
