<?php

namespace App\Filament\Resources\DepartmentCategoriesResource\Pages;

use App\Filament\Resources\DepartmentCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDepartmentCategories extends ListRecords
{
    protected static string $resource = DepartmentCategoriesResource::class;
    protected static ?string $title = 'تصنيفات الأقسام';

    public function getBreadcrumbs(): array
{
    return [
        route('filament.admin.pages.dashboard') => 'الرئيسية',
        route('filament.admin.resources.department-categories.index') => 'التصنيفات',
    ];
}

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إضافة تصنيف جديد')
                ->modalHeading('إضافة تصنيف جديد'),
        ];
    }
}
