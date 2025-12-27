<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProductionRequestsDone extends ListRecords
{
    protected static string $resource = ProductionRequestResource::class;

    protected static ?string $title = 'طلبات التصنيع المكتملة';

    public function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
