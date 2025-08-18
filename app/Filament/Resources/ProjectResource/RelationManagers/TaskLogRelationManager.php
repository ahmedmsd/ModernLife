<?php
namespace App\Filament\Resources\ProjectResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TaskLogRelationManager extends RelationManager
{
    protected static string $relationship = 'logs';
    protected static ?string $title = 'سجل العمليات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                Tables\Columns\TextColumn::make('happened_at')
                    ->label('التاريخ')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('type')
                    ->label('الحدث')
                    ->badge()
                    ->color(fn ($state) => match($state) {
                        'created'         => 'gray',
                        'status_changed'  => 'info',
                        'assigned_changed'=> 'warning',
                        'due_changed'     => 'purple',
                        'timer_started'   => 'success',
                        'timer_stopped'   => 'danger',
                        default           => 'gray',
                    }),
                Tables\Columns\TextColumn::make('data')
                    ->label('تفاصيل')
                    ->formatStateUsing(fn ($state) => is_array($state) ? json_encode($state, JSON_UNESCAPED_UNICODE) : $state)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('causer.name')->label('بواسطة')->toggleable(),
            ])
            ->defaultSort('happened_at', 'desc');
    }
}
