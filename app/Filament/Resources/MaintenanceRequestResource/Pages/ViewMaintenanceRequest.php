<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewMaintenanceRequest extends ViewRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('add_comment')
                ->label('إضافة ملاحظة')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('gray')
                ->visible(fn () => Auth::user()?->hasAnyRole(['sales','showroom_manager','factory_manager','admin','super-admin']) ?? false)
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('الملاحظة')
                        ->rows(4)
                        ->required()
                        ->maxLength(5000),
                ])
                ->action(function (array $data) {
                    /** @var MaintenanceRequest $record */
                    $record = $this->getRecord();
                    $record->comments()->create([
                        'user_id' => Auth::id(),
                        'note'    => (string) $data['note'],
                    ]);

                    Notification::make()
                        ->success()
                        ->title('تمت إضافة الملاحظة')
                        ->send();

                    $this->redirect(MaintenanceRequestResource::getUrl('view', ['record' => $record]));

                }),
        ];
    }
}
