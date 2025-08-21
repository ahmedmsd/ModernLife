<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Services\ProductionRequestWorkflow;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionRequest extends CreateRecord
{
    protected static string $resource = ProductionRequestResource::class;

    /** قبل الإنشاء: نظّف البيانات */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by']   = auth()->id();
        $data['submitted_at'] = now();

        $type = $data['request_type'] ?? 'direct';

        $user = auth()->user();
        $canDirect   = $user?->hasAnyRole(['sales','factory_manager','admin','super-admin']);
        $canIndirect = $user?->hasAnyRole(['showroom_manager','admin','super-admin']);

        // منع غير المخوّل
        if ($type === 'direct' && ! $canDirect) {
            abort(403, 'ليست لديك صلاحية إنشاء طلب مباشر.');
        }
        if ($type === 'indirect' && ! $canIndirect) {
            abort(403, 'ليست لديك صلاحية إنشاء طلب غير مباشر.');
        }

        // لو مباشر، لا نحتاج معرضًا
        if ($type === 'direct') {
            $data['showroom_id'] = null;
        }

        // حقول الـ workflow تُملأ لاحقًا بواسطة الخدمة
        unset($data['current_phase'], $data['phase_status'], $data['current_owner_role'], $data['sent_to_owner_at'], $data['received_by_owner_at']);

        return $data;
    }

    /** بعد الإنشاء: ابدأ السير حسب نوع الطلب */
    protected function afterCreate(): void
    {
        /** @var ProductionRequest $record */
        $record = $this->record->fresh();

        app(ProductionRequestWorkflow::class)->start($record);
    }
}
