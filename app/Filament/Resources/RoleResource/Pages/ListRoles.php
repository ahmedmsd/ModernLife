<?php
// app/Filament/Resources/RoleResource/Pages/ListRoles.php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('view_any_role') ?? false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('إنشاء دور')
                ->visible(fn () => auth()->user()?->can('create_role') ?? false),
        ];
    }
}
