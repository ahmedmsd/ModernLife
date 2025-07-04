<?php

namespace App\Filament\Resources\DepartmentResource\Pages;

use App\Filament\Resources\DepartmentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartment extends CreateRecord
{
    protected static string $resource = DepartmentResource::class;
    protected function getCreatedNotificationMessage(): ?string
    {
        return 'تم إنشاء القسم بنجاح';
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the index page instead of the edit page
        return DepartmentResource::getUrl('index');
    }

}
