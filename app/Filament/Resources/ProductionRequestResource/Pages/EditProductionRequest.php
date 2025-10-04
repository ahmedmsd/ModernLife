<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use Filament\Resources\Pages\EditRecord;
use App\Services\ProductionRequestWorkflow;
use Filament\Notifications\Notification;

class EditProductionRequest extends EditRecord
{
    protected static string $resource = ProductionRequestResource::class;


    protected function afterSave(): void
    {
        $record = $this->record;

        if (in_array((string) $record->phase_status, ['rejected', 'مرفوض'], true)) {
            app(\App\Services\ProductionRequestWorkflow::class)
                ->routeForReReview(
                    $record,
                    'تم تعديل بيانات الطلب بواسطة المبيعات وإعادته للمراجعة.'
                );

            \Filament\Notifications\Notification::make()
                ->title('تم إعادة توجيه الطلب')
                ->body('أُعيدت مرحلة الطلب إلى "بانتظار المعرض/الوجهة" بعد التعديل.')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return ProductionRequestResource::getUrl('view', ['record' => $this->record]);
    }
}
