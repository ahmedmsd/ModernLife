<?php

namespace App\Filament\Widgets\Factory;

use App\Filament\Resources\ProductionRequestResource;
use App\Filament\Resources\TaskResource;
use App\Models\ProductionRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class FactoryManagerCurrentRequests extends TableWidget
{
    protected static ?string $heading = 'طلباتي الجارية (المصنع)';
    protected static ?int $sort = 22;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check()
            && (auth()->user()->hasAnyRole(['factory_manager'], 'web'));
    }

    public function table(Table $table): Table
    {
        $uid = auth()->id();
        $terminal = ['completed','cancelled','rejected'];

        return $table
            ->query(
                ProductionRequest::query()
                    ->with(['project','client'])
                    ->whereNotIn('phase_status', $terminal)
                    ->where('current_owner_user_id', $uid)
                    ->latest('id')
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('#'),
                Tables\Columns\TextColumn::make('project.project_name')->label('المشروع'),
                Tables\Columns\TextColumn::make('client.client_name')->label('العميل'),
                Tables\Columns\TextColumn::make('phase_status')->label('الحالة')->badge(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) =>
                    \App\Filament\Resources\ProductionRequestResource::getUrl('review', ['record' => $record])
                    ),
            ])
            ->emptyStateHeading('لا توجد طلبات تخصّك.');
    }
}
