<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;

class ClientsMonthlyChart extends ChartWidget
{
    protected static ?string $heading = 'العملاء الجدد (آخر 12 شهر)';
    protected static ?string $maxHeight = '320px'; // نفس القيمة في جميع الودجت
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];

    protected static ?int $sort = 2;
    protected function getData(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $raw = Client::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as ym, COUNT(*) as c')
            ->where('created_at', '>=', $start)
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('c','ym');

        $labels = [];
        $data   = [];
        for ($i = 0; $i < 12; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');
            $labels[] = $m->translatedFormat('M Y');
            $data[]   = (int) ($raw[$key] ?? 0);
        }

        return [
            'datasets' => [[ 'label' => 'عملاء جدد', 'data' => $data ]],
            'labels'   => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'scales' => [
                'y' => ['beginAtZero' => true, 'ticks' => ['precision' => 0]],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
