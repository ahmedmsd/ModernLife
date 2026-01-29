<?php

namespace App\Filament\Resources\ResidentialQuotationResource\Pages;

use App\Filament\Resources\ResidentialQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewResidentialQuotation extends ViewRecord
{
    protected static string $resource = ResidentialQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
