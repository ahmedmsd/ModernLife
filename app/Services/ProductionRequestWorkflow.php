<?php

namespace App\Services;

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S, RequestType};
use App\Models\ProductionRequest;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ProductionRequestWorkflow
{
    public function start(ProductionRequest $pr): ProductionRequest
    {
        $type = $pr->request_type ?? RequestType::Direct->value;

        if ($type === RequestType::Direct->value) {
            return $this->move($pr, Phase::FactoryIntake, S::Pending, 'factory_manager', true);
        }

        return $this->move($pr, Phase::ShowroomReview, S::Pending, 'showroom_manager', true);
    }

    public function move(
        ProductionRequest $pr,
        Phase $toPhase,
        S $toStatus,
        ?string $ownerRole = null,
        bool $touchSent = false
    ): ProductionRequest {
        return DB::transaction(function () use ($pr, $toPhase, $toStatus, $ownerRole, $touchSent) {
            $role = $ownerRole ?? $this->defaultOwnerRole($toPhase);

            $noChange =
                $pr->current_phase      === $toPhase->value &&
                $pr->phase_status       === $toStatus->value &&
                $pr->current_owner_role === $role &&
                ! $touchSent;

            if ($noChange) {
                return $pr->refresh();
            }

            $from = [
                'phase'  => $pr->current_phase,
                'status' => $pr->phase_status,
                'owner'  => $pr->current_owner_role,
            ];

            $pr->current_phase      = $toPhase->value;
            $pr->phase_status       = $toStatus->value;
            $pr->current_owner_role = $role;

            if ($touchSent) {
                $pr->sent_to_owner_at     = now();
                $pr->received_by_owner_at = null;
            }

            $pr->save();

            $noteText = sprintf(
                'انتقال من مرحلة %s (%s) إلى مرحلة %s (%s)%s',
                $from['phase'] ?? '—',
                $from['status'] ?? '—',
                $toPhase->value,
                $toStatus->value,
                $role ? " | المالك: {$role}" : ''
            );

            $pr->logs()->create([
                'causer_id'   => Auth::id(),
                'type'        => 'transition',
                'data'        => [
                    'from'       => $from,
                    'to'         => ['phase' => $toPhase->value, 'status' => $toStatus->value],
                    'owner_role' => $role,
                ],
                'note'        => $noteText,
                'happened_at' => now(),
            ]);

            return $pr->refresh();
        });
    }

    public function markReceived(ProductionRequest $pr): ProductionRequest
    {
        $fromStatus = $pr->phase_status;
        $sentAt     = $pr->sent_to_owner_at;
        if ($sentAt && ! $sentAt instanceof Carbon) {
            $sentAt = Carbon::parse($sentAt);
        }

        $pr->phase_status         = S::Received->value;
        $pr->received_by_owner_at = now();
        $pr->save();

        $waitSeconds = ($sentAt) ? $sentAt->diffInSeconds(now()) : null;

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'received',
            'data'        => [
                'phase'        => $pr->current_phase,
                'from_status'  => $fromStatus,
                'to_status'    => S::Received->value,
                'wait_seconds' => $waitSeconds,
            ],
            'note'        => "تم تأكيد الاستلام في مرحلة {$pr->current_phase}",
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    /** اعتماد المصنع → إنشاء مشروع + الانتقال لتوزيع المهام */
    public function approve(ProductionRequest $pr): ProductionRequest
    {
        $currentPhase = Phase::tryFrom($pr->current_phase) ?? Phase::FactoryIntake;

        $pr = $this->move($pr, $currentPhase, S::Approved, $pr->current_owner_role, false);

        if ($currentPhase === Phase::FactoryIntake) {
            $this->bootstrapProjectFromRequest($pr);

            // الطلب يستمر لمسار المهام داخل المشروع → إسناد الأقسام
            $pr = $this->move($pr, Phase::DepartmentAssignment, S::Pending, 'factory_manager', true);
        }

        return $pr;
    }

    public function reject(ProductionRequest $pr, ?string $reason = null): ProductionRequest
    {
        $fromStatus = $pr->phase_status;

        $pr->phase_status = S::Rejected->value;
        $pr->save();

        $baseNote = "تم رفض الطلب في مرحلة {$pr->current_phase}";
        $noteText = $reason ? "{$baseNote} — السبب: {$reason}" : $baseNote;

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'rejected',
            'data'        => [
                'phase'       => $pr->current_phase,
                'from_status' => $fromStatus,
                'to_status'   => S::Rejected->value,
            ],
            'note'        => $noteText,
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    protected function bootstrapProjectFromRequest(ProductionRequest $pr): void
    {
        DB::transaction(function () use ($pr) {
            $project = Project::firstOrCreate(
                ['production_request_id' => $pr->id],
                [
                    'client_id'    => $pr->client_id,
                    'project_name' => $pr->project_name ?? 'مشروع بدون اسم',
                    'description'  => $pr->project_description ?? $pr->description,
                    'start_date'   => now(),
                    'status'       => 'in_progress',
                    'created_by'   => Auth::id() ?? 0,
                ]
            );

            $pr->loadMissing('files');

            foreach ($pr->files as $reqFile) {
                $filePath = $reqFile->file_path;
                $fileName = basename($filePath);
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileSize = Storage::disk('public')->exists($filePath)
                    ? Storage::disk('public')->size($filePath)
                    : 0;

                $project->files()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $filePath,
                    ],
                    [
                        'file_name'   => $fileName,
                        'file_type'   => $fileType,
                        'file_size'   => $fileSize,
                        'uploaded_by' => Auth::id() ?? 0,
                        'upload_date' => now(),
                        'version'     => 1,
                        'is_current'  => true,
                    ]
                );

                $project->tasks()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $reqFile->file_path,
                    ],
                    [
                        'notes'                   => 'تم إنشاؤها تلقائيًا من ملف الطلب.',
                        'status'                  => 'pending',
                        'assigned_to_employee_id' => null,
                        'assigned_budget'         => null,
                        'due_date'                => null,
                    ]
                );
            }

            $pr->logs()->create([
                'type'        => 'project_bootstrap',
                'data'        => ['project_id' => $project->id],
                'note'        => "تم إنشاء مشروع #{$project->id} وربطه بالطلب.",
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        });
    }

    /** يُستدعى عند اكتمال المشروع لإغلاق الطلب (مرحلة: closed، حالة: completed) */
    public function finalizeRequestAfterProjectDone(ProductionRequest $pr): ProductionRequest
    {
        $pr->current_phase        = Phase::Closed->value;
        $pr->phase_status         = S::Completed->value;
        $pr->current_owner_role   = null;
        $pr->current_owner_user_id= null;
        $pr->sent_to_owner_at     = null;
        $pr->received_by_owner_at = null;
        $pr->save();

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'request_finalized',
            'data'        => ['by' => 'project_completed'],
            'note'        => 'اكتمل المشروع وجميع المهام، تم إغلاق الطلب.',
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    protected function defaultOwnerRole(Phase $phase): ?string
    {
        return match ($phase) {
            Phase::ShowroomReview           => 'showroom_manager',
            Phase::FactoryIntake            => 'factory_manager',
            Phase::DepartmentAssignment     => 'factory_manager',
            Phase::Purchasing               => 'purchasing_manager',
            Phase::Manufacturing            => 'department_manager',
            Phase::QualityAfterManufacture  => 'quality_manager',
            Phase::Installation             => 'installation_manager',
            Phase::QualityAfterInstallation => 'quality_manager',
            Phase::Closed                   => null,
        };
    }
}
