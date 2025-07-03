<?php

namespace App\Filament\Resources\DepartmentCategoriesResource\Pages;

use App\Filament\Resources\DepartmentCategoriesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDepartmentCategories extends EditRecord
{
    protected static string $resource = DepartmentCategoriesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
