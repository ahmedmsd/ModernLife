<?php

namespace App\Filament\Widgets;

use App\Models\ProductionTask;
use Filament\Widgets\ChartWidget;

class DepartmentWorkloadBar extends ChartWidget
{
    protected static ?string $heading = 'حجم الأعمال على الأقسام (عدد المهام)';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $rows = ProductionTask::selectRaw('department_id, COUNT(*) as c')
            ->groupBy('department_id')
            ->with(['department:dept_id,dept_name'])
            ->orderByDesc('c')
            ->get();

        $labels = $rows->map(fn($r) => $r->department->dept_name ?? 'غير محدد')->all();
        $data   = $rows->map(fn($r) => (int)$r->c)->all();

        if (empty($labels)) {
            $labels = ['لا بيانات'];
            $data   = [1];
        }

        return [
            'datasets' => [[ 'label' => 'عدد المهام', 'data' => $data ]],
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
