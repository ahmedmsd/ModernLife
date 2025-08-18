<?php

namespace App\Filament\Widgets;

use App\Models\ProductionTask;
use Filament\Widgets\ChartWidget;

class TasksCompletionDoughnut extends ChartWidget
{
    protected static ?string $heading = 'نسبة إنجاز المهام';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];

    protected static ?int $sort = 8;
    protected array $doneStatuses = ['completed','done','closed','approved'];

    protected function getData(): array
    {
        $total = ProductionTask::count();
        $done  = ProductionTask::whereIn('status', $this->doneStatuses)->count();

        $percent = $total > 0 ? round(($done / $total) * 100, 1) : 0;
        $data = [$percent, max(0, 100 - $percent)];
        $labels = ['% الإنجاز', 'متبقي'];

        if ($total === 0) {
            $labels = ['لا بيانات'];
            $data   = [1];
        }

        return [
            'datasets' => [[ 'label' => 'التقدم', 'data' => $data ]],
            'labels'   => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
