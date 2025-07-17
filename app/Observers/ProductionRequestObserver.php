<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use Illuminate\Support\Facades\Auth;

class ProductionRequestObserver
{
    public function created(ProductionRequest $productionRequest): void
    {
         
    }

    public function updated(ProductionRequest $productionRequest): void
    {
        if ($productionRequest->wasChanged('status')) {
            ProductionRequestLog::create([
                'production_request_id' => $productionRequest->id,
                'user_id' => Auth::id(), 
                'action' => $productionRequest->status,
                'note' => 'تم تغيير حالة الطلب إلى ' . $productionRequest->status,
                'action_at' => now(),
            ]);
        }
    }

    public function deleted(ProductionRequest $productionRequest): void {}
    public function restored(ProductionRequest $productionRequest): void {}
    public function forceDeleted(ProductionRequest $productionRequest): void {}
}
