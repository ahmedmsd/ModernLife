<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class InProgressTasksReport extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'تقرير المهام الجارية';
    protected static ?string $title           = 'ملخص المهام قيد التنفيذ';
    protected static ?string $navigationGroup = 'التقارير';
    protected static ?int $navigationSort     = 5;

    protected static string $view = 'filament.pages.in-progress-tasks-report';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Allow access to admin roles and managers
        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole([
                'admin',
                'super-admin',
                'factory_manager',
                'department_manager',
                'quality_manager',
                'purchasing_manager',
            ]);
        }

        return true;
    }
}
