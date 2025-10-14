<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};
use App\Filament\Widgets\Reports\Concerns\UsesPerformanceFilters;

class TopEmployeesBarChart extends ChartWidget
{
    use UsesPerformanceFilters;

    protected static ?string $heading = 'أفضل الموظفين (عدد المهام المكتملة)';
    protected static ?int $sort = 50;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromArray($this->filters));
        $d   = $svc->topEmployeesChart(10); // ['labels'=>[], 'data'=>[]]

        $palette = $this->palette(count($d['data'] ?? []));
        return [
            'datasets' => [[
                'label'           => 'مكتمل',
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
        return 'bar';
    }
}
