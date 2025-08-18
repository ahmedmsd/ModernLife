<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;

class ProjectsPerClientBar extends ChartWidget
{
    protected static ?string $heading = 'أكثر العملاء من حيث عدد المشروعات (أفضل 10)';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];
    protected static ?int $sort = 5;

    protected function getData(): array
    {
        $rows = Project::selectRaw('client_id, COUNT(*) as c')
            ->groupBy('client_id')
            ->with(['client:client_id,client_name'])
            ->orderByDesc('c')
            ->limit(10)
            ->get();

        $labels = $rows->map(fn($r) => $r->client->client_name ?? 'بدون عميل')->all();
        $data   = $rows->map(fn($r) => (int) $r->c)->all();

        if (empty($labels)) {
            $labels = ['لا بيانات'];
            $data   = [1];
        }

        return [
            'datasets' => [[ 'label' => 'المشروعات', 'data' => $data ]],
            'labels'   => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'indexAxis' => 'x',
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
