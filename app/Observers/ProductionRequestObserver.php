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

    public function updated(ProductionRequest $pr): void
    {
        // 1) سجّل transition إن تغيرت حقول سير العمل (كما عندك مسبقًا)
        $workflowFields = [
            'current_phase', 'phase_status',
            'current_owner_role', 'current_owner_user_id',
            'sent_to_owner_at', 'received_by_owner_at',
        ];

        $hasTransition = collect($workflowFields)->some(fn ($f) => $pr->wasChanged($f));
        if ($hasTransition) {
            \App\Models\ProductionRequestLog::create([
                'production_request_id' => $pr->id,
                'type'        => 'transition',
                'data'        => [
                    'from' => [
                        'phase'      => $pr->getOriginal('current_phase'),
                        'status'     => $pr->getOriginal('phase_status'),
                        'owner_role' => $pr->getOriginal('current_owner_role'),
                        'owner_user' => $pr->getOriginal('current_owner_user_id'),
                        'sent_at'    => $pr->getOriginal('sent_to_owner_at'),
                        'recv_at'    => $pr->getOriginal('received_by_owner_at'),
                    ],
                    'to' => [
                        'phase'      => $pr->current_phase,
                        'status'     => $pr->phase_status,
                        'owner_role' => $pr->current_owner_role,
                        'owner_user' => $pr->current_owner_user_id,
                        'sent_at'    => $pr->sent_to_owner_at,
                        'recv_at'    => $pr->received_by_owner_at,
                    ],
                ],
                'note'        => sprintf(
                    'Phase: %s → %s | Status: %s → %s | Owner: %s → %s',
                    $pr->getOriginal('current_phase') ?? '—',
                    $pr->current_phase ?? '—',
                    $pr->getOriginal('phase_status') ?? '—',
                    $pr->phase_status ?? '—',
                    $pr->getOriginal('current_owner_role') ?? '—',
                    $pr->current_owner_role ?? '—',
                ),
                'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
                'happened_at' => now(),
            ]);
        }

        // 2) لو تغيّرت حقول "المحتوى" الحقيقي للطلب → أعده للمراجعة
        $contentFields = [
            'client_id',
            'project_name',
            'request_type',
            'showroom_id',
            'agreement_file',
            // أضف أي حقول وصفية أخرى تخص الطلب نفسه
            'project_description',
            'description',
            // إن كان عندك تسعير/سقف ميزانية ضمن الطلب:
            'price',
            'budget_cap',
        ];

        $contentChanged = collect($contentFields)->some(fn ($f) => $pr->wasChanged($f));

        // مهم: لا نعيد التوجيه إذا كان التغيير الوحيد ضمن حقول سير العمل أعلاه
        if ($contentChanged) {
            // ملاحظة: routeForReReview نفسها ستسجّل لوج وتعمل move
            app(\App\Services\ProductionRequestWorkflow::class)->routeForReReview($pr);
            return; // نخرج لتجنّب أي لوج إضافي هنا
        }

        // 3) حالتك السابقة الخاصة باعتماد المصنع وإنشاء مشروع (اتركها كما هي)
        $becameApprovedByFactory =
            $pr->wasChanged('phase_status')
            && ((string) $pr->phase_status === 'approved')
            && ((string) $pr->current_owner_role === 'factory_manager');

        if ($becameApprovedByFactory) {
            // بناء المشروع + مهام + لوج
            app(\App\Services\ProductionRequestWorkflow::class)->approve($pr);
            return;
        }
    }


    /** لوج حذف */
    public function deleting(\App\Models\ProductionRequest $pr): void
    {
        \App\Models\ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'deleted',
            'data'        => null,
            'note'        => 'تم حذف الطلب',
            'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
            'happened_at' => now(),
        ]);
    }
}
