<?php

namespace App\Filament\Resources\DepartmentPurchaseRequestResource\Pages;

use App\Filament\Resources\DepartmentPurchaseRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use App\Filament\Widgets\DprKpis;

class ListDepartmentPurchaseRequests extends ListRecords
{
    protected static string $resource = DepartmentPurchaseRequestResource::class;
    protected function getHeaderWidgets(): array { return [DprKpis::class]; }
    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('طلب مشتريات جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getEmptyStateActions(): array
    {
        return [
            CreateAction::make()
                ->label('طلب مشتريات جديد')
                ->icon('heroicon-o-plus'),
        ];
    }

}
