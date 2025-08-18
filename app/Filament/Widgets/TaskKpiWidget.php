<?php
// app/Filament/Widgets/TaskKpiWidget.php
namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as Widget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TaskKpiWidget extends Widget
{
    public ?\App\Models\ProductionTask $task = null;

    protected function getStats(): array
    {
        $active = $this->task?->active_seconds ?? 0;
        $delay  = $this->task?->delay_seconds ?? 0;

        $fmt = fn($sec) => gmdate('H:i:s', $sec);

        return [
            Stat::make('زمن العمل الفعلي', $fmt($active))->description('مجموع جلسات العمل'),
            Stat::make('التأخير', $fmt($delay))->description('عن تاريخ التسليم'),
            Stat::make('عدد الأحداث', (string) ($this->task?->logs()->count() ?? 0)),
        ];
    }
}
