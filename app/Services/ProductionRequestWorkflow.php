<?php

namespace App\Services;

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S, RequestType};
use App\Models\ProductionRequest;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductionRequestWorkflow
{
    /** تهيئة المسار الأولي للطلب */
    public function start(ProductionRequest $pr): ProductionRequest
    {
        $type = $pr->request_type ?? RequestType::Direct->value;

        if ($type === RequestType::Direct->value) {
            return $this->move($pr, Phase::FactoryIntake, S::Pending, 'factory_manager', true);
        }

        return $this->move($pr, Phase::ShowroomReview, S::Pending, 'showroom_manager', true);
    }

    /** انتقال عام مع Log + حارس إديمبوتنسي */
    public function move(
        ProductionRequest $pr,
        Phase $toPhase,
        S $toStatus,
        ?string $ownerRole = null,
        bool $touchSent = false
    ): ProductionRequest {
        return DB::transaction(function () use ($pr, $toPhase, $toStatus, $ownerRole, $touchSent) {

            $role = $ownerRole ?? $this->defaultOwnerRole($toPhase);

            // حارس: لا تسجل لوج إذا لم يتغير شيء فعليًا
            $noChange =
                $pr->current_phase      === $toPhase->value &&
                $pr->phase_status       === $toStatus->value &&
                $pr->current_owner_role === $role &&
                ! $touchSent;

            if ($noChange) {
                return $pr->refresh(); // لا تسجيل
            }

            $from = [
                'phase'  => $pr->current_phase,
                'status' => $pr->phase_status,
                'owner'  => $pr->current_owner_role,
            ];

            // تحديث الحقول
            $pr->current_phase      = $toPhase->value;
            $pr->phase_status       = $toStatus->value;
            $pr->current_owner_role = $role;

            if ($touchSent) {
                $pr->sent_to_owner_at     = now();
                $pr->received_by_owner_at = null;
            }

            $pr->save();

            // Log انتقال واحد فقط من الخدمة
            $pr->logs()->create([
                'causer_id'   => Auth::id(),
                'type'        => 'transition',
                'data'        => [
                    'from'       => $from,
                    'to'         => ['phase' => $toPhase->value, 'status' => $toStatus->value],
                    'owner_role' => $role,
                ],
                'note'        => null,
                'happened_at' => now(),
            ]);

            return $pr->refresh();
        });
    }

    /** تأكيد استلام من المالك الحالي */
    public function markReceived(ProductionRequest $pr): ProductionRequest
    {
        $fromStatus = $pr->phase_status;
        $sentAt     = $pr->sent_to_owner_at;

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
            'note'        => null,
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    /** اعتماد المرحلة الحالية. إن كانت FactoryIntake → أنشئ مشروعًا ومهام/ملفات ثم انتقل للتعيين */
    public function approve(ProductionRequest $pr): ProductionRequest
    {
        $currentPhase = Phase::tryFrom($pr->current_phase) ?? Phase::FactoryIntake;

        // وسم المرحلة الحالية بالمُعتمد
        $pr = $this->move($pr, $currentPhase, S::Approved, $pr->current_owner_role, false);

        // عند اعتماد مدير المصنع: نُنشئ المشروع والمهام من الطلب (بدل Observer)
        if ($currentPhase === Phase::FactoryIntake) {
            $this->bootstrapProjectFromRequest($pr);

            // بعد البناء: انتقل لمرحلة إسناد الأقسام
            $pr = $this->move($pr, Phase::DepartmentAssignment, S::Pending, 'factory_manager', true);
        }

        return $pr;
    }

    /** رفض المرحلة الحالية */
    public function reject(ProductionRequest $pr, ?string $reason = null): ProductionRequest
    {
        $fromStatus = $pr->phase_status;

        $pr->phase_status = S::Rejected->value;
        $pr->save();

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'rejected',
            'data'        => [
                'phase'       => $pr->current_phase,
                'from_status' => $fromStatus,
                'to_status'   => S::Rejected->value,
            ],
            'note'        => $reason ?? 'تم الرفض',
            'happened_at' => now(),
        ]);

        return $pr->refresh();
    }

    /** بناء مشروع جديد + نسخ ملفات الطلب + إنشاء مهمة لكل ملف قسم (مستخلَص من Observer) */
    protected function bootstrapProjectFromRequest(ProductionRequest $pr): void
    {
        DB::transaction(function () use ($pr) {
            // 1) مشروع واحد لكل طلب
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

            // 2) ملفات المشروع من ملفات الطلب
            $pr->loadMissing('files');

            $filesCreated = 0;
            foreach ($pr->files as $reqFile) {
                $filePath = $reqFile->file_path;
                $fileName = basename($filePath);
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileSize = Storage::disk('public')->exists($filePath)
                    ? Storage::disk('public')->size($filePath)
                    : 0;

                $created = $project->files()->firstOrCreate(
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

                if ($created->wasRecentlyCreated) {
                    $filesCreated++;
                }
            }

            // 3) مهمة لكل ملف قسم
            $tasksCreated = 0;
            foreach ($pr->files as $reqFile) {
                $task = $project->tasks()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $reqFile->file_path,
                    ],
                    [
                        'assigned_to_employee_id' => null,
                        'assigned_budget'         => null,
                        'due_date'                => null,
                        'notes'                   => 'تم إنشاؤها تلقائيًا من ملفات الطلب.',
                        'status'                  => 'pending',
                        'current_owner_role'      => null,
                        'current_owner_user_id'   => null,
                        'sent_to_owner_at'        => null,
                        'received_by_owner_at'    => null,
                    ]
                );

                if ($task->wasRecentlyCreated) {
                    $tasksCreated++;
                }
            }

            // Log تلخيصي (اختياري)
            $pr->logs()->create([
                'type'        => 'project_bootstrap',
                'data'        => [
                    'project_id'    => $project->id,
                    'files_created' => $filesCreated,
                    'tasks_created' => $tasksCreated,
                ],
                'note'        => 'تم إنشاء مشروع ومهام الأقسام بعد اعتماد مدير المصنع.',
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        });
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
