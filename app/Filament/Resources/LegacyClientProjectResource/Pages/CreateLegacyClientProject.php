<?php

namespace App\Filament\Resources\LegacyClientProjectResource\Pages;

use App\Filament\Resources\LegacyClientProjectResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\EditRecord;

class CreateLegacyClientProject extends CreateRecord
{
    protected static string $resource = LegacyClientProjectResource::class;
    protected function getRedirectUrl(): string
    {
        $clientId = $this->record->client_id;
        return \App\Filament\Resources\LegacyClientProjectResource::getUrl('index', [
            'tableFilters' => ['client_id' => ['value' => $clientId]],
            'client_id'    => $clientId,
        ]);
    }
}
