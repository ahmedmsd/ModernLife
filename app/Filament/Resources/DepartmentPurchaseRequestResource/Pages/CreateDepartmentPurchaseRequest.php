<?php

namespace App\Filament\Resources\DepartmentPurchaseRequestResource\Pages;

use App\Filament\Resources\DepartmentPurchaseRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDepartmentPurchaseRequest extends CreateRecord
{
    protected static string $resource = DepartmentPurchaseRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requested_by'] = auth()->id();
        return $data;
    }

    public function getHeading(): string
    {
        return 'طلب شراء جديد';
    }
}
