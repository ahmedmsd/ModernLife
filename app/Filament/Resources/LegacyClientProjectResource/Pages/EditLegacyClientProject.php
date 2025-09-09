<?php

namespace App\Filament\Resources\LegacyClientProjectResource\Pages;

use App\Filament\Resources\LegacyClientProjectResource;
use App\Filament\Resources\LegacyClientProjectResource\RelationManagers\LegacyFilesRelationManager;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLegacyClientProject extends EditRecord
{
    protected static string $resource = LegacyClientProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('addFile')
                ->label('إضافة ملف')
                ->icon('heroicon-o-plus')
                ->action(fn () => $this->mountRelationAction('files', 'create')), // يستدعي CreateAction للـ Relation
        ];
    }

    public function getRelations(): array
    {
        return [
            LegacyFilesRelationManager::class,
        ];
    }
}
