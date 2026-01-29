<?php

namespace App\Filament\Resources\ResidentialQuotationResource\Pages;

use App\Filament\Resources\ResidentialQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListResidentialQuotations extends ListRecords
{
    protected static string $resource = ResidentialQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
