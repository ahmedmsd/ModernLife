<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProductionRequests extends ListRecords
{
    protected static string $resource = ProductionRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // زر: طلب مباشر
            Actions\Action::make('createDirect')
                ->label('＋ طلب مباشر')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->url(fn () => ProductionRequestResource::getUrl('create', ['request_type' => 'direct']))
                ->visible(fn () => auth()->user()?->hasAnyRole([
                    'sales','factory_manager','admin','super-admin',
                ])),

            // زر: طلب غير مباشر
            Actions\Action::make('createIndirect')
                ->label('＋ طلب غير مباشر')
                ->icon('heroicon-o-building-storefront')
                ->color('info')
                ->url(fn () => ProductionRequestResource::getUrl('create', ['request_type' => 'indirect']))
                ->visible(fn () => auth()->user()?->hasAnyRole([
                    'showroom_manager','admin','super-admin',
                ])),

            // (اختياري) زر الإنشاء الافتراضي لمن تريد
            Actions\CreateAction::make()
                ->label('إنشاء طلب')
                ->visible(fn () => auth()->user()?->hasAnyRole(['admin','super-admin'])),
        ];
    }
}
