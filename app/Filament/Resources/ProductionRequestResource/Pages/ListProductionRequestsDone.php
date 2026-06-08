<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListProductionRequestsDone extends ListRecords
{
    protected static string $resource = ProductionRequestResource::class;

    protected static ?string $title = 'طلبات التصنيع المكتملة';

    public function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery();
    }

    public function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return parent::table($table)->modifyQueryUsing(fn (Builder $query) => $query->completed());
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
