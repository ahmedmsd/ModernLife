<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};

class TopEmployeesBarChart extends ChartWidget
{
    protected static ?string $heading = 'أفضل الموظفين (عدد المهام المكتملة)';
    protected static ?int $sort = 50;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromRequest());
        $d   = $svc->topEmployeesChart(10);

        return [
            'datasets' => [
                [
                    'label' => 'مكتمل',
                    'data'  => $d['data'],
                ],
            ],
            'labels' => $d['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
