<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Resources\Pages\CreateRecord;

// لإرسال بريد

class CreateProductionRequest extends CreateRecord
{
    protected static string $resource = ProductionRequestResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by']   = auth()->id();
        $data['submitted_at'] = now();

        $type = $data['request_type'] ?? 'direct';

        $user = auth()->user();
        $canDirect   = $user?->hasAnyRole(['sales','factory_manager','admin','super-admin']);
        $canIndirect = $user?->hasAnyRole(['showroom_manager','admin','super-admin','sales']);

        if ($type === 'direct' && ! $canDirect) {
            abort(403, 'ليست لديك صلاحية إنشاء طلب مباشر.');
        }
        if ($type === 'indirect' && ! $canIndirect) {
            abort(403, 'ليست لديك صلاحية إنشاء طلب غير مباشر.');
        }

        if ($type === 'direct') {
            $data['showroom_id'] = null;
        }

        unset($data['current_phase'], $data['phase_status'], $data['current_owner_role'], $data['sent_to_owner_at'], $data['received_by_owner_at']);

        return $data;
    }

    protected function afterCreate(): void
    {

//        \Filament\Notifications\Notification::make()
//            ->title('تم إنشاء طلب التصنيع بنجاح')
//            ->success()
//            ->send();

        $this->redirect(\App\Filament\Resources\ProductionRequestResource::getUrl('index'));

//        $record = $this->record->fresh();
//        app(ProductionRequestWorkflow::class)->start($record);
    }
}
