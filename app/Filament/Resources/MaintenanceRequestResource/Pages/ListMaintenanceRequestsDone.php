<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceRequestsDone extends ListRecords
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): \Illuminate\Contracts\Support\Htmlable|string
    {
        return 'طلبات الصيانة المكتملة';
    }
}
