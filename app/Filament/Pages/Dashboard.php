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

            \App\Filament\Widgets\Sales\SalesInProgressRequests::class,
            \App\Filament\Widgets\Showroom\ShowroomManagerNeedsResponse::class,
            \App\Filament\Widgets\Factory\FactoryManagerCurrentRequests::class,
            \App\Filament\Widgets\Factory\FactoryManagerCurrentTasks::class,
            \App\Filament\Widgets\Department\DepartmentManagerCurrentTasks::class,
            \App\Filament\Widgets\Purchasing\PurchasingOpenMaterialsRequests::class,
            \App\Filament\Widgets\Quality\QualityManagerCurrentTasks::class,

//            \App\Filament\Widgets\RequestsPerMonthChart::class,
//            \App\Filament\Widgets\ProjectsPerClientBar::class,
//            \App\Filament\Widgets\ClientsMonthlyChart::class,
//            \App\Filament\Widgets\EmployeesByDepartmentDonut::class,
//            \App\Filament\Widgets\RequestsByStatusChart::class,
//            \App\Filament\Widgets\TasksCompletionDoughnut::class,
//            \App\Filament\Widgets\DepartmentWorkloadBar::class,
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
