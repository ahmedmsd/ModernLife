<?php

namespace App\Filament\Widgets;

use App\Models\ProductionRequest;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class RequestsPerMonthChart extends ChartWidget
{
    protected static ?string $heading = 'تطور الطلبات (آخر 12 شهر)';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];
    protected static ?int $sort = 70;

    protected function getData(): array
    {
        $start = \Carbon\Carbon::now()->startOfMonth()->subMonths(11);

        $raw = \App\Models\ProductionRequest::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('c', 'ym');

        $labels = [];
        $data   = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');
            $labels[] = $m->translatedFormat('M Y');
            $data[]   = (int) ($raw[$key] ?? 0);
        }

        if (array_sum($data) === 0) {
            $labels = ['لا بيانات'];
            $data   = [1];
        }

        return [
            'datasets' => [[
                'label'   => 'عدد الطلبات',
                'data'    => $data,
                'tension' => 0.3,
                'fill'    => false,
            ]],
            'labels' => $labels,
        ];
    }


    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => true, 'position' => 'bottom'],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
