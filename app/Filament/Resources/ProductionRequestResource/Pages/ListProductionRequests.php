<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionRequests extends ListRecords
{
    protected static string $resource = ProductionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
