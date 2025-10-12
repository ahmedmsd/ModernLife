<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};

class TasksByDepartmentChart extends ChartWidget
{
    protected static ?string $heading = 'المهام حسب الأقسام';
    protected static ?int $sort = 20;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromRequest());
        $d   = $svc->tasksByDepartment();

        return [
            'datasets' => [
                [
                    'label' => 'عدد المهام',
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
