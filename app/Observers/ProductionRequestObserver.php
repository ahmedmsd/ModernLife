<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use Illuminate\Support\Facades\Auth;
// استخدمه فقط إذا أردت تشغيل start() من هنا
use App\Services\ProductionRequestWorkflow;

class ProductionRequestObserver
{
    /** لوج إنشاء بسيط، بلا انتقالات */
    public function created(ProductionRequest $pr): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'created',
            'data'        => [
                'phase'      => $pr->current_phase,
                'status'     => $pr->phase_status,
                'owner_role' => $pr->current_owner_role,
                'owner_user' => $pr->current_owner_user_id,
            ],
            'note'        => 'تم إنشاء الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        // إن أردت التهيئة الأولية من هنا ولا تستدعي start() من أي مكان آخر:
        // if (blank($pr->current_phase) || blank($pr->phase_status)) {
        //     app(ProductionRequestWorkflow::class)->start($pr);
        // }
    }

    /** لوج حذف */
    public function deleted(ProductionRequest $pr): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'deleted',
            'data'        => null,
            'note'        => 'تم حذف الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);
    }

    // ملاحظة: أزلنا updated() بالكامل لمنع transition المكرر
}
