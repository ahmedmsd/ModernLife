<?php

namespace App\Filament\Widgets\Reports;

use Filament\Widgets\ChartWidget;
use App\Support\Reports\{ReportFilters, ReportService};

class CompletionTrendChart extends ChartWidget
{
    protected static ?string $heading = 'ترند المهام المكتملة';
    protected static ?int $sort = 30;

    protected function getData(): array
    {
        $svc = new ReportService(ReportFilters::fromRequest());
        $d   = $svc->completionTrend();

        return [
            'datasets' => [
                [
                    'label' => 'مكتمل يوميًا',
                    'data'  => $d['data'],
                ],
            ],
            'labels' => $d['labels'],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
