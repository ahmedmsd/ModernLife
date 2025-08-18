<?php

namespace App\Filament\Widgets;

use App\Models\ProductionRequest;
use Filament\Widgets\ChartWidget;

class RequestsByStatusChart extends ChartWidget
{
    protected static ?string $heading = 'الطلبات حسب الحالة';
    protected static ?string $maxHeight = '320px';
    protected int|string|array $columnSpan = ['default' => 1, 'lg' => 1];

    protected static ?int $sort = 9;
    protected array $statusColors = [
        'draft'        => '#9CA3AF', // Gray-400
        'submitted'    => '#3B82F6', // Blue-500
        'under_review' => '#F59E0B', // Amber-500
        'approved'     => '#10B981', // Emerald-500
        'rejected'     => '#EF4444', // Red-500
    ];

    protected function getData(): array
    {
        // الترتيب الظاهر في المخطط
        $labels = ['مسودة','مقدمة','تحت المراجعة','معتمدة','مرفوضة'];
        $keys   = ['draft','submitted','under_review','approved','rejected'];

        // البيانات
        $data = [];
        foreach ($keys as $k) {
            $data[] = ProductionRequest::where('status', $k)->count();
        }

        // الألوان المتطابقة مع نفس الترتيب
        $background = array_map(fn($k) => $this->statusColors[$k] ?? '#6B7280', $keys);
        $hover      = array_map(fn($hex) => $this->lighten($hex, 12), $background);

        // معالجة عدم وجود بيانات
        if (array_sum($data) === 0) {
            return [
                'datasets' => [[
                    'label' => 'الطلبات',
                    'data'  => [1],
                    'backgroundColor' => ['#E5E7EB'], // رمادي فاتح
                    'hoverBackgroundColor' => ['#E5E7EB'],
                    'borderWidth' => 1,
                ]],
                'labels' => ['لا بيانات'],
            ];
        }

        return [
            'datasets' => [[
                'label' => 'الطلبات',
                'data'  => $data,
                'backgroundColor'     => $background,
                'hoverBackgroundColor' => $hover,
                'borderWidth' => 1,
            ]],
            'labels' => $labels,
        ];
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    // يمكن ضبط لون النص إن أردت:
                    // 'labels' => ['color' => '#111827'],
                ],
                // إظهار القيم داخل التولتيب
                'tooltip' => [
                    'callbacks' => [
                        'label' => \Illuminate\Support\Js::from(
                        // نطبع: الحالة: العدد
                        // (Chart.js سيضيف النسبة تلقائياً لو أردت إضافات أخرى)
                            function($ctx) { return $ctx->label.': '.$ctx->parsed; }
                        ),
                    ],
                ],
            ],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    /**
     * تفتيح بسيط للّون للهوفر
     */
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
