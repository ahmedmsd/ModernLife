<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use Illuminate\Support\Facades\Auth;
use App\Enums\ProductionRequestStatus;
use App\Models\Project;

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

            if ($statusEnum === ProductionRequestStatus::APPROVED && $productionRequest->project()->doesntExist()) {
                Project::create([
                    'production_request_id' => $productionRequest->id,
                    'client_id' => $productionRequest->client_id,
                    'project_name' => $productionRequest->project_name ?? 'مشروع بدون اسم',
                    'description' => $productionRequest->description,
                    'start_date' => now(),
                    'status' => 'in_progress',
                    'created_by' => Auth::id() ?? 0,
                ]);
            }
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
