<?php

namespace App\Filament\Widgets;

use App\Models\ProductionTask;
use Filament\Widgets\ChartWidget;

class RequestsByStatusChart extends ChartWidget
{
    protected static ?string $heading   = 'المهام حسب الحالة';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];
    protected static ?int $sort = 90;

    protected array $statusLabels = [
        'pending'       => 'قيد الإنشاء',
        'assigned'      => 'مُسندة',
        'acknowledged'  => 'تأكيد الاستلام',
        'in_progress'   => 'قيد التنفيذ',
        'blocked'       => 'متوقفة مؤقتًا',
        'under_review'  => 'قيد المراجعة',
        'rework'        => 'إعادة عمل',
        'completed'     => 'مكتملة',
        'closed'        => 'مغلقة',
        'cancelled'     => 'ملغاة',
    ];

    protected array $statusColors = [
        'pending'       => '#9CA3AF', // Gray-400
        'assigned'      => '#3B82F6', // Blue-500
        'acknowledged'  => '#06B6D4', // Cyan-500
        'in_progress'   => '#10B981', // Emerald-500
        'blocked'       => '#EF4444', // Red-500
        'under_review'  => '#F59E0B', // Amber-500
        'rework'        => '#A855F7', // Violet-500
        'completed'     => '#16A34A', // Green-600
        'closed'        => '#374151', // Gray-700
        'cancelled'     => '#E11D48', // Rose-600
        'other'         => '#6B7280', // Gray-500
    ];

    protected function getData(): array
    {
        $rows = ProductionTask::query()
            ->selectRaw('COALESCE(status, "unknown") as status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->toArray();

        $labels = [];
        $data   = [];
        $colors = [];

        $knownTotal = 0;
        foreach ($this->statusLabels as $key => $ar) {
            $count   = (int) ($rows[$key] ?? 0);
            $labels[] = $ar;
            $data[]   = $count;
            $colors[] = $this->statusColors[$key] ?? '#6B7280';
            $knownTotal += $count;
        }

        $allTotal = array_sum($rows);
        $other    = max(0, $allTotal - $knownTotal);
        if ($other > 0) {
            $labels[] = 'أخرى';
            $data[]   = $other;
            $colors[] = $this->statusColors['other'];
        }

        if (array_sum($data) === 0) {
            return [
                'datasets' => [[
                    'label'                => 'المهام',
                    'data'                 => [1],
                    'backgroundColor'      => ['#E5E7EB'],
                    'hoverBackgroundColor' => ['#E5E7EB'],
                    'borderWidth'          => 1,
                ]],
                'labels' => ['لا بيانات'],
            ];
        }

        return [
            'datasets' => [[
                'label'                => 'المهام',
                'data'                 => $data,
                'backgroundColor'      => $colors,
                'hoverBackgroundColor' => array_map(fn ($hex) => $this->lighten($hex, 12), $colors),
                'borderWidth'          => 1,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'cutout' => '70%',
            'plugins' => [
                'legend' => ['position' => 'bottom'],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    private function lighten(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, (int) round($r + (255 - $r) * $percent / 100));
        $g = min(255, (int) round($g + (255 - $g) * $percent / 100));
        $b = min(255, (int) round($b + (255 - $b) * $percent / 100));

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
