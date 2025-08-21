<?php

namespace App\Services;

use App\Enums\{ProductionRequestPhase, PhaseStatus, RequestType};
use App\Models\ProductionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\CreateTasksFromRequest;

class ProductionRequestWorkflow
{
    /** بداية السير حسب نوع الطلب */
    public function start(ProductionRequest $pr): ProductionRequest
    {
        $type = $pr->request_type ?? RequestType::Direct->value;

        if ($type === RequestType::Direct->value) {
            // مباشر: إلى المصنع
            return $this->move(
                $pr,
                ProductionRequestPhase::FactoryIntake,
                PhaseStatus::Pending,
                'factory_manager',
                true
            );
        }

        // غير مباشر: إلى مراجعة المعرض
        return $this->move(
            $pr,
            ProductionRequestPhase::ShowroomReview,
            PhaseStatus::Pending,
            'showroom_manager',
            true
        );
    }

    /**
     * انتقال (تحديث المرحلة/الحالة/المالك) + تسجيل لوج
     * $touchSent: يحدّث sent_to_owner_at ويفرغ received_by_owner_at
     */
    public function move(
        ProductionRequest $pr,
        ProductionRequestPhase $phase,
        PhaseStatus $status,
        ?string $ownerRole = null,
        bool $touchSent = false
    ): ProductionRequest {
        return DB::transaction(function () use ($pr, $phase, $status, $ownerRole, $touchSent) {
            $fromPhase  = $pr->current_phase;
            $fromStatus = $pr->phase_status;

            $role = $ownerRole ?? $this->defaultOwnerRole($phase);

            $pr->current_phase      = $phase->value;
            $pr->phase_status       = $status->value;
            $pr->current_owner_role = $role;

            if ($touchSent) {
                $pr->sent_to_owner_at     = now();
                $pr->received_by_owner_at = null;
            }

            $pr->save();

            // لوج انتقال
            $pr->logs()->create([
                'causer_id'   => Auth::id(),
                'type'      => 'transition',
                'data'      => [
                    'from' => ['phase' => $fromPhase, 'status' => $fromStatus],
                    'to'   => ['phase' => $phase->value, 'status' => $status->value],
                    'owner_role' => $role,
                ],
                'note'      => null,
                'happened_at' => now(),
            ]);

            return $pr->refresh();
        });
    }

    /** استلام من المالك الحالي */
    public function markReceived(ProductionRequest $pr): ProductionRequest
    {
        $fromStatus = $pr->phase_status;
        $sentAt     = $pr->sent_to_owner_at;

        $pr->phase_status         = PhaseStatus::Received->value;
        $pr->received_by_owner_at = now();
        $pr->save();

        $waitSeconds = ($sentAt) ? $sentAt->diffInSeconds(now()) : null;

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'      => 'received',
            'data'      => [
                'phase'                  => $pr->current_phase,
                'from_status'            => $fromStatus,
                'to_status'              => PhaseStatus::Received->value,
                'waited_seconds_since_sent' => $waitSeconds,
            ],
            'note'      => null,
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    /**
     * اعتماد المرحلة الحالية
     * - لو المرحلة FactoryIntake: ننشئ مهام الأقسام ثم ننتقل إلى DepartmentAssignment → Pending
     */
    public function approve(ProductionRequest $pr): ProductionRequest
    {
        $currentPhase  = ProductionRequestPhase::tryFrom($pr->current_phase)
            ?? ProductionRequestPhase::FactoryIntake;

        // اعتماد الحالة في نفس المرحلة
        $pr = $this->move(
            $pr,
            $currentPhase,
            PhaseStatus::Approved,
            $pr->current_owner_role,
            false
        );

        // حالة خاصة: اعتماد المصنع ⇒ إنشاء مهام ثم تحويل للـ DepartmentAssignment
        if ($currentPhase === ProductionRequestPhase::FactoryIntake) {
            app(CreateTasksFromRequest::class)->handle($pr);

            // إرسال مهمة التوزيع (DepartmentAssignment) لمدير المصنع
            $pr = $this->move(
                $pr,
                ProductionRequestPhase::DepartmentAssignment,
                PhaseStatus::Pending,
                'factory_manager',
                true
            );
        }

        return $pr;
    }

    /** رفض المرحلة الحالية مع سبب */
    public function reject(ProductionRequest $pr, ?string $reason = null): ProductionRequest
    {
        $fromStatus = $pr->phase_status;

        $pr->phase_status = PhaseStatus::Rejected->value;
        $pr->save();

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'      => 'rejected',
            'data'      => [
                'phase'       => $pr->current_phase,
                'from_status' => $fromStatus,
                'to_status'   => PhaseStatus::Rejected->value,
            ],
            'note'      => $reason ?? 'تم الرفض',
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    /** المالك الافتراضي لكل مرحلة */
    protected function defaultOwnerRole(ProductionRequestPhase $phase): ?string
    {
        return match ($phase) {
            ProductionRequestPhase::ShowroomReview            => 'showroom_manager',
            ProductionRequestPhase::FactoryIntake             => 'factory_manager',
            ProductionRequestPhase::DepartmentAssignment      => 'factory_manager',
            ProductionRequestPhase::Purchasing                => 'purchasing_manager',
            ProductionRequestPhase::Manufacturing             => 'department_manager',
            ProductionRequestPhase::QualityAfterManufacture   => 'quality_manager',
            ProductionRequestPhase::Installation              => 'installation_manager',
            ProductionRequestPhase::QualityAfterInstallation  => 'quality_manager',
            ProductionRequestPhase::Closed                    => null,
        };
    }
}
