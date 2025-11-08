<?php

namespace App\Filament\Resources\MaintenanceRequestResource\Pages;

use App\Filament\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateMaintenanceRequest extends CreateRecord
{
    protected static string $resource = MaintenanceRequestResource::class;

    public static function afterCreate(MaintenanceRequest $record): void
    {
        $record->update([
            'current_owner_role'    => 'factory_manager',
            'current_owner_user_id' => null,
            'sent_to_owner_at'      => now(),
        ]);

        app(\App\Services\MaintenanceNotifier::class)->notifyNewRequest($record);

        \Filament\Notifications\Notification::make()
            ->title('تم إنشاء طلب صيانة جديد وإبلاغ مدير المصنع')
            ->success()
            ->send();
    }
}
