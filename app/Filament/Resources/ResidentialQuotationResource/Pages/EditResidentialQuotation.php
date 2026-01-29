<?php

namespace App\Filament\Resources\ResidentialQuotationResource\Pages;

use App\Filament\Resources\ResidentialQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditResidentialQuotation extends EditRecord
{
    protected static string $resource = ResidentialQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
