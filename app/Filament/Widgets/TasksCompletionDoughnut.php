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

    // حالات الإنجاز
    protected array $doneStatuses = ['completed','done','closed','approved'];

    // ألوان قابلة للتعديل
    protected string $colorDone      = '#16a34a'; // أخضر (green-600)
    protected string $colorRemaining = '#e11d48'; // وردي/أحمر (rose-600)
    protected string $colorEmpty     = '#cbd5e1'; // رمادي عند عدم وجود بيانات (slate-300)

    protected function getData(): array
    {
        $total = ProductionTask::count();
        $done  = ProductionTask::whereIn('status', $this->doneStatuses)->count();

        $labels = ['% الإنجاز', 'متبقي'];
        $data   = [0, 100];
        $colors = [$this->colorDone, $this->colorRemaining];

        if ($total > 0) {
            $percent = round(($done / $total) * 100, 1);
            $data = [$percent, max(0, 100 - $percent)];
        } else {
            // لا توجد مهام
            $labels = ['لا بيانات'];
            $data   = [1];
            $colors = [$this->colorEmpty];
        }

        return [
            'datasets' => [[
                'label'            => 'التقدم',
                'data'             => $data,
                'backgroundColor'  => $colors,
                'borderWidth'      => 0,
                'hoverOffset'      => 4,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'cutout' => '70%', // اختياري: يُظهرها كـ donut أرفع
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    // يمكنك إخفاء الليجند إن رغبت:
                    // 'display' => false,
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
