<?php

// app/Filament/Pages/MyNotifications.php
namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Notifications\DatabaseNotification;

class MyNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'التنبيهات';
    protected static ?string $title           = 'التنبيهات';
    protected static ?string $navigationGroup = 'إدارة النظام';


    protected static string $view = 'filament.pages.my-notifications';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(auth()->user()->notifications()->getQuery())
            ->columns([
                Tables\Columns\IconColumn::make('read_at')
                    ->label('مقروء؟')
                    ->boolean()
                    ->trueIcon('heroicon-o-check')
                    ->falseIcon('heroicon-o-bell-alert'),

                Tables\Columns\TextColumn::make('data.title')
                    ->label('العنوان')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->since(),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('فتح')
                    ->url(fn (DatabaseNotification $record) => data_get($record->data, 'url'))
                    ->openUrlInNewTab()
                    ->visible(fn (DatabaseNotification $record) => filled(data_get($record->data, 'url'))),

                Tables\Actions\Action::make('markAsRead')
                    ->label('تمييز كمقروء')
                    ->action(fn (DatabaseNotification $record) => $record->markAsRead())
                    ->visible(fn (DatabaseNotification $record) => is_null($record->read_at)),

                Tables\Actions\Action::make('markAsUnread')
                    ->label('تمييز كغير مقروء')
                    ->action(function (DatabaseNotification $record) {
                        $record->read_at = null;
                        $record->save();
                    })
                    ->visible(fn (DatabaseNotification $record) => ! is_null($record->read_at)),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markSelectedRead')
                    ->label('تمييز المحدد كمقروء')
                    ->action(fn ($records) => $records->each->markAsRead()),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
