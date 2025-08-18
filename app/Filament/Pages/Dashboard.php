<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'لوحة التحكم';

    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getWidgets(): array
    {
        return [

            \App\Filament\Widgets\MainStats::class,


            
            \App\Filament\Widgets\RequestsPerMonthChart::class,

            \App\Filament\Widgets\ProjectsPerClientBar::class,

            \App\Filament\Widgets\ClientsMonthlyChart::class,
            \App\Filament\Widgets\EmployeesByDepartmentDoughnut::class,


            \App\Filament\Widgets\RequestsByStatusChart::class,
            \App\Filament\Widgets\TasksCompletionDoughnut::class,

            \App\Filament\Widgets\DepartmentWorkloadBar::class,


        ];
    }

    public function getColumns(): int | array
    {
        return [
            'default' => 1,
            'lg'      => 2,
        ];
    }
}
