<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductionRequest extends CreateRecord
{
    protected static string $resource = ProductionRequestResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }
}
