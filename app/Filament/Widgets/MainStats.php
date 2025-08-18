<?php

namespace App\Filament\Widgets;

use App\Models\Client;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ProductionRequest;
use App\Models\ProductionTask; // غيّرها إلى Task إن كان موديلك اسمه Task
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MainStats extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int|string|array $columnSpan = [
        'default' => 2,
        'lg'      => 2,
    ];

    protected function getColumns(): int
    {
        return 3;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('الطلبات', number_format(ProductionRequest::count()))
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('المشروعات', number_format(Project::count()))
                ->icon('heroicon-o-briefcase')
                ->color('info'),

            Stat::make('العملاء', number_format(Client::count()))
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('الأقسام', number_format(Department::count()))
                ->icon('heroicon-o-building-office-2')
                ->color('gray'),

            Stat::make('الموظفون', number_format(Employee::count()))
                ->icon('heroicon-o-user')
                ->color('success'),

            Stat::make('المهام', number_format(ProductionTask::count()))
                ->icon('heroicon-o-briefcase')
                ->color('secondary'),
        ];
    }
}
