<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};

class StatusDistributionChart extends ChartWidget
{
    protected static ?string $heading = 'توزيع الحالات';
    protected static ?int $sort = 40;
    public array $filters = [];

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromRequest());
        $d   = $svc->statusDistribution();

        return [
            'datasets' => [
                [
                    'label' => 'الحالات',
                    'data'  => $d['data'],
                ],
            ],
            'labels' => $d['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
