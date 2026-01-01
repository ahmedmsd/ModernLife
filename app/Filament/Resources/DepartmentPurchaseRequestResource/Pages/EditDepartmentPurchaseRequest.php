<?php

namespace App\Filament\Resources\DepartmentPurchaseRequestResource\Pages;

use App\Filament\Resources\DepartmentPurchaseRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditDepartmentPurchaseRequest extends EditRecord
{
    protected static string $resource = DepartmentPurchaseRequestResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('view', ['record'=>$this->record]); }

    public function getHeading(): string
    {
        return 'تعديل طلب الشراء';
    }
}
