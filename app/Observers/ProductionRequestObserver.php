<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use Illuminate\Support\Facades\Auth;
use App\Enums\ProductionRequestStatus;

class ProductionRequestObserver
{
    public function created(ProductionRequest $productionRequest): void
    {
        $status = $productionRequest->status instanceof \BackedEnum
            ? $productionRequest->status->value
            : (string) $productionRequest->status;

        

        ProductionRequestLog::create([
            'production_request_id' => $productionRequest->id,
            'user_id' => Auth::id() ?? 0,
            'action' => 'created',
            'note' => 'تم إنشاء الطلب',
            'action_at' => now(),
        ]);
    }

    public function updating(ProductionRequest $productionRequest): void
    {
        if ($productionRequest->isDirty('status')) {
            $newStatus = $productionRequest->status;

            try {
                $statusEnum = $newStatus instanceof ProductionRequestStatus
                    ? $newStatus
                    : ProductionRequestStatus::from((string) $newStatus);
            } catch (\ValueError $e) {
                logger()->error('حالة غير معروفة أثناء التحديث', [
                    'production_request_id' => $productionRequest->id,
                    'new_status' => $newStatus,
                    'error' => $e->getMessage(),
                ]);
                return;
            }

            ProductionRequestLog::create([
                'production_request_id' => $productionRequest->id,
                'user_id' => Auth::id() ?? 0,
                'action' => $statusEnum->value,
                'note' => 'تم تغيير حالة الطلب إلى ' . $statusEnum->label(),
                'action_at' => now(),
            ]);
        }
    }

    public function deleted(ProductionRequest $productionRequest): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $productionRequest->id,
            'user_id' => Auth::id() ?? 0,
            'action' => 'deleted',
            'note' => 'تم حذف الطلب',
            'action_at' => now(),
        ]);
    }
}
