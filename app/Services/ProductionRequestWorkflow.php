<?php

namespace App\Services;

use App\Models\User;
use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S, RequestType};
use App\Models\ProductionRequest;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Filament\Notifications\Notification as FNotification;
use Filament\Notifications\Actions\Action as FAction;

class ProductionRequestWorkflow
{
    public function start(ProductionRequest $pr): ProductionRequest
    {
        $type = $pr->request_type ?? RequestType::Direct->value;

        if ($type === RequestType::Direct->value) {
            return $this->move($pr, Phase::FactoryIntake, S::Pending, 'factory_manager', null, true);
        }

        return $this->move($pr, Phase::ShowroomReview, S::Pending, 'showroom_manager', null, true);
    }

    public function move(
        ProductionRequest $pr,
        Phase $toPhase,
        S $toStatus,
        ?string $ownerRole = null,
        ?int $ownerUserId = null,
        bool $touchSent = false
    ): ProductionRequest {
        return DB::transaction(function () use ($pr, $toPhase, $toStatus, $ownerRole, $ownerUserId, $touchSent) {
            $role = $ownerRole ?? $this->defaultOwnerRole($toPhase);

            $noChange =
                $pr->current_phase      === $toPhase->value &&
                $pr->phase_status       === $toStatus->value &&
                $pr->current_owner_role === $role &&
                ! $touchSent &&
                ($ownerUserId === null);

            if ($noChange) {
                return $pr->refresh();
            }

            $from = [
                'phase'  => $pr->current_phase,
                'status' => $pr->phase_status,
                'owner'  => $pr->current_owner_role,
            ];

            $pr->current_phase       = $toPhase->value;
            $pr->phase_status        = $toStatus->value;
            $pr->current_owner_role  = $role;

            if ($ownerUserId !== null) {
                $pr->current_owner_user_id = $ownerUserId;
            } else {
                $pr->current_owner_user_id = $this->resolveOwnerUserId($pr, $role);
            }

            if ($touchSent) {
                $pr->sent_to_owner_at     = now();
                $pr->received_by_owner_at = null;
            }

            $pr->save();

            $noteText = sprintf(
                'انتقال من مرحلة %s (%s) إلى مرحلة %s (%s)%s',
                $this->phaseLabel($from['phase'] ?? '—'),
                $this->statusLabel($from['status'] ?? '—'),
                $this->phaseLabel($toPhase->value),
                $this->statusLabel($toStatus->value),
                $role ? ' | المالك: '.$this->roleLabel($role) : ''
            );

            $pr->logs()->create([
                'causer_id'   => Auth::id(),
                'type'        => 'transition',
                'data'        => [
                    'from' => [
                        'phase'        => $from['phase'],
                        'status'       => $from['status'],
                        'owner'        => $from['owner'],
                        'actor_name'   => optional(Auth::user())->name,
                        'phase_label'  => $this->phaseLabel((string)($from['phase'] ?? '')),
                        'status_label' => $this->statusLabel((string)($from['status'] ?? '')),
                        'owner_label'  => $this->roleLabel((string)($from['owner'] ?? '')),
                    ],
                    'to' => [
                        'phase'        => $toPhase->value,
                        'status'       => $toStatus->value,
                        'phase_label'  => $this->phaseLabel($toPhase->value),
                        'status_label' => $this->statusLabel($toStatus->value),
                    ],
                    'owner_role'       => $role,
                    'owner_role_label' => $this->roleLabel($role),
                    'owner_user_id'    => $pr->current_owner_user_id,
                ],
                'note'        => $noteText,
                'happened_at' => now(),
            ]);

            event(new \App\Events\ProductionRequestPhaseEvent(
                type: 'transition',
                pr: $pr->fresh(),
                context: [
                    'from'        => $from,
                    'to'          => ['phase' => $toPhase->value, 'status' => $toStatus->value],
                    'owner_role'  => $role,
                    'owner_user_id' => $pr->current_owner_user_id,
                    'touch_sent'  => $touchSent,
                ],
            ));

            return $pr->refresh();
        });
    }

    public function markReceived(ProductionRequest $pr): ProductionRequest
    {
        $fromStatus = $pr->phase_status;
        $sentAt     = $pr->sent_to_owner_at instanceof \Carbon\Carbon
            ? $pr->sent_to_owner_at
            : ($pr->sent_to_owner_at ? \Carbon\Carbon::parse($pr->sent_to_owner_at) : null);

        $pr->phase_status         = S::Received->value;
        $pr->received_by_owner_at = now();
        $pr->current_owner_user_id = Auth::id();
        $pr->save();

        $waitSeconds = ($sentAt) ? $sentAt->diffInSeconds(now()) : null;

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'received',
            'data'        => [
                'phase'         => $pr->current_phase,
                'phase_label'   => $this->phaseLabel((string)$pr->current_phase),
                'from_status'   => $fromStatus,
                'from_label'    => $this->statusLabel((string)$fromStatus),
                'to_status'     => S::Received->value,
                'to_label'      => $this->statusLabel(S::Received->value),
                'actor_name'    => optional(Auth::user())->name,
                'wait_seconds'  => $waitSeconds,
            ],
            'note'        => 'تم تأكيد الاستلام في مرحلة '.$this->phaseLabel((string)$pr->current_phase),
            'happened_at' => now(),
        ]);

        event(new \App\Events\ProductionRequestPhaseEvent(
            type: 'received',
            pr: $pr->fresh(),
            context: [
                'phase'         => $pr->current_phase,
                'from_status'   => $fromStatus,
                'to_status'     => S::Received->value,
                'wait_seconds'  => $waitSeconds,
            ],
        ));

        return $pr->refresh();
    }

    public function approve(ProductionRequest $pr): ProductionRequest
    {
        $currentPhase = Phase::tryFrom($pr->current_phase) ?? Phase::FactoryIntake;

        $pr = $this->move($pr, $currentPhase, S::Approved, $pr->current_owner_role, null, false);

        if ($currentPhase === Phase::ShowroomReview) {
            return $this->move($pr, Phase::FactoryIntake, S::Pending, 'factory_manager', null, true);
        }

        if ($currentPhase === Phase::FactoryIntake) {
            $this->bootstrapProjectFromRequest($pr);
            return $this->move($pr, Phase::DepartmentAssignment, S::Pending, 'factory_manager', null, true);
        }

        return $pr;
    }

    public function reject(ProductionRequest $pr, ?string $reason = null): ProductionRequest
    {
        if (($pr->current_phase ?? null) === \App\Enums\ProductionRequestPhase::FactoryIntake->value) {
            return $this->factoryReject($pr, $reason);
        }

        $fromStatus = $pr->phase_status;

        $pr->phase_status = \App\Enums\PhaseStatus::Rejected->value;
        $pr->save();

        $baseNote = 'تم رفض الطلب في مرحلة ' . $this->phaseLabel((string) $pr->current_phase);
        $noteText = $reason ? "{$baseNote} — السبب: {$reason}" : $baseNote;

        $pr->logs()->create([
            'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
            'type'        => 'rejected',
            'data'        => [
                'phase'        => $pr->current_phase,
                'phase_label'  => $this->phaseLabel((string) $pr->current_phase),
                'from_status'  => $fromStatus,
                'from_label'   => $this->statusLabel((string) $fromStatus),
                'to_status'    => \App\Enums\PhaseStatus::Rejected->value,
                'actor_name'   => optional(\Illuminate\Support\Facades\Auth::user())->name,
                'to_label'     => $this->statusLabel(\App\Enums\PhaseStatus::Rejected->value),
            ],
            'note'        => $noteText,
            'happened_at' => now(),
        ]);

        event(new \App\Events\ProductionRequestPhaseEvent(
            type: 'rejected',
            pr: $pr->fresh(),
            context: [
                'phase'       => $pr->current_phase,
                'from_status' => $fromStatus,
                'to_status'   => \App\Enums\PhaseStatus::Rejected->value,
                'reason'      => $reason,
            ],
        ));

        return $pr->refresh();
    }

    public function factoryReject(ProductionRequest $pr, ?string $reason = null): ProductionRequest
    {
        if ($pr->current_phase !== Phase::FactoryIntake->value) {
            throw new \LogicException('Cannot factoryReject() unless current phase is FactoryIntake.');
        }

        $isDirect = ($pr->request_type ?? RequestType::Direct->value) === RequestType::Direct->value;

        $toPhase   = $isDirect ? Phase::SalesIntake     : Phase::ShowroomReview;
        $ownerRole = $isDirect ? 'sales'        : 'showroom_manager';
        $ownerUser = $this->resolveReturnOwnerUserId($pr, $isDirect); // << النقطة الأهم

        $pr = $this->move($pr, $toPhase, S::Pending, $ownerRole, $ownerUser, true);

        $pr->logs()->create([
            'causer_id'   => Auth::id(),
            'type'        => 'factory_rejected',
            'data'        => [
                'from_phase'     => Phase::FactoryIntake->value,
                'to_phase'       => $toPhase->value,
                'owner_role'     => $ownerRole,
                'owner_user_id'  => $ownerUser,
                'reason'         => $reason,
            ],
            'note'        => 'رفض مدير المصنع الطلب وإعادته إلى '.($isDirect ? 'المبيعات' : 'مدير المعرض'),
            'happened_at' => now(),
        ]);

        event(new \App\Events\ProductionRequestPhaseEvent(
            type: 'factory_rejected',
            pr: $pr->fresh(),
            context: [
                'to_phase'      => $toPhase->value,
                'owner_role'    => $ownerRole,
                'owner_user_id' => $ownerUser,
                'reason'        => $reason,
            ],
        ));

        return $pr->refresh();
    }

    public function routeForReReview(ProductionRequest $pr, ?string $note = null): ProductionRequest
    {
        $type = $pr->request_type ?? RequestType::Direct->value;

        $from = [
            'phase'  => $pr->current_phase,
            'status' => $pr->phase_status,
            'owner'  => $pr->current_owner_role,
        ];

        if ($type === RequestType::Indirect->value) {
            $toPhase = Phase::ShowroomReview;
            $owner   = 'showroom_manager';
        } else {
            $toPhase = Phase::FactoryIntake;
            $owner   = 'factory_manager';
        }

        return DB::transaction(function () use ($pr, $toPhase, $owner, $from, $note) {
            $pr->current_phase          = $toPhase->value;
            $pr->phase_status           = S::Pending->value;
            $pr->current_owner_role     = $owner;
            $pr->current_owner_user_id  = $this->resolveOwnerUserId($pr, $owner);
            $pr->sent_to_owner_at       = now();
            $pr->received_by_owner_at   = null;

            $pr->saveQuietly();

            $defaultNote = 'إعادة توجيه الطلب للمراجعة مرة أخرى بعد تحديث المحتوى.';
            $pr->logs()->create([
                'type'        => 'transition',
                'data'        => [
                    'from' => [
                        'phase'        => $from['phase'],
                        'status'       => $from['status'],
                        'owner'        => $from['owner'],
                        'phase_label'  => $this->phaseLabel((string)($from['phase'] ?? '')),
                        'status_label' => $this->statusLabel((string)($from['status'] ?? '')),
                        'owner_label'  => $this->roleLabel((string)($from['owner'] ?? '')),
                    ],
                    'to'   => [
                        'phase'        => $toPhase->value,
                        'status'       => S::Pending->value,
                        'owner'        => $owner,
                        'phase_label'  => $this->phaseLabel($toPhase->value),
                        'status_label' => $this->statusLabel(S::Pending->value),
                        'owner_label'  => $this->roleLabel($owner),
                    ],
                    'owner_role'       => $owner,
                    'actor_name'       => optional(Auth::user())->name,
                    'owner_role_label' => $this->roleLabel($owner),
                ],
                'note'        => $note ?? $defaultNote,
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            return $pr->refresh();
        });
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

                $project->files()->updateOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $filePath,
                    ],
                    [
                        'file_name'      => $fileName,
                        'file_type'      => $fileType,
                        'file_size'      => $fileSize,
                        'estimated_cost' => (float) ($reqFile->estimated_cost ?? 0),
                        'uploaded_by'    => Auth::id() ?? 0,
                        'upload_date'    => now(),
                        'version'        => 1,
                        'is_current'     => true,
                    ]
                );

                $task = $project->tasks()->updateOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $reqFile->file_path,
                    ],
                    [
                        'notes'                   => 'تم إنشاؤها تلقائيًا من ملف الطلب.',
                        'status'                  => 'pending',
                        'assigned_to_user_id' => null,
                        'estimated_cost'          => (float) ($reqFile->estimated_cost ?? 0),
                        'due_date'                => null,
                    ]
                );

                $dept = $task->department()->with(['managerUser'])->first();
                $notifyUser = $dept?->manager?->user;

                if ($notifyUser) {
                    $url = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $project->id]);

                    FNotification::make()
                        ->title('مهمة جديدة لقسمك')
                        ->body("المهمة (#{$task->id}) على المشروع #{$project->id}")
                        ->icon('heroicon-o-clipboard-document-check')
                        ->success()
                        ->actions([
                            FAction::make('عرض المشروع')->button()->url($url),
                        ])
                        ->sendToDatabase($notifyUser);
                }
            }

            $pr->logs()->create([
                'type'        => 'project_bootstrap',
                'data'        => ['project_id' => $project->id],
                'note'        => "تم إنشاء مشروع #{$project->id} وربطه بالطلب.",
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            event(new \App\Events\ProductionRequestPhaseEvent(
                type: 'project_bootstrap',
                pr: $pr->fresh(),
                context: [
                    'project_id' => $project->id,
                ],
            ));
        });
    }

    public function finalizeRequestAfterProjectDone(ProductionRequest $pr): ProductionRequest
    {
        $pr->current_phase         = Phase::Closed->value;
        $pr->phase_status          = S::Completed->value;
        $pr->current_owner_role    = null;
        $pr->current_owner_user_id = null;
        $pr->sent_to_owner_at      = null;
        $pr->received_by_owner_at  = null;
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

    /* ======================= Helpers ======================= */


    protected function resolveOwnerUserId(ProductionRequest $pr, ?string $role): ?int
    {
        if (! $role) {
            return null;
        }

        $byRelation = match ($role) {
            'showroom_manager'      => optional($pr->showroom?->manager)->id,
            'factory_manager'       => optional($pr->factory?->manager)->id ?? null,
            'purchasing_manager'    => optional($pr->purchasingDepartment?->manager)->id ?? null,
            'department_manager'    => null,
            'quality_manager'       => null,
            'installation_manager'  => null,
            'sales'         => null,
            default                 => null,
        };

        if ($byRelation) {
            return $byRelation;
        }

        return match ($role) {
            'factory_manager'       => User::role('factory_manager')->value('id'),
            'purchasing_manager'    => User::role('purchasing_manager')->value('id'),
            'quality_manager'       => User::role('quality_manager')->value('id'),
            'installation_manager'  => User::role('installation_manager')->value('id'),
            'showroom_manager'      => User::role('showroom_manager')->value('id'),
            'sales'         => User::role('sales')->value('id'),
            default                 => null,
        };
    }


    protected function resolveReturnOwnerUserId(ProductionRequest $pr, bool $isDirect): ?int
    {
        if ($isDirect) {
            if (!empty($pr->created_by))   return (int) $pr->created_by;
            if (!empty($pr->submitted_by)) return (int) $pr->submitted_by;

            $created = $pr->logs()
                ->where('type', 'created')
                ->orderByDesc('happened_at')
                ->first();
            if ($created && $created->causer_id) {
                return (int) $created->causer_id;
            }

            return null;
        }

        $logs = $pr->logs()
            ->orderByDesc('happened_at')
            ->limit(50)
            ->get();

        foreach ($logs as $log) {
            $data = $log->data ?? [];
            $ownerRole = data_get($data, 'owner_role');
            $toPhase   = data_get($data, 'to.phase') ?? data_get($data, 'to_phase');
            $fromPhase = data_get($data, 'from.phase') ?? data_get($data, 'from_phase');

            $wentToFactory = ($toPhase === Phase::FactoryIntake->value)
                || ($fromPhase === Phase::ShowroomReview->value);

            if ($ownerRole === 'showroom_manager' && $wentToFactory) {
                return $log->causer_id ?: null;
            }
        }

        if (!empty($pr->last_showroom_manager_id)) {
            return (int) $pr->last_showroom_manager_id;
        }

        return null;
    }

    private function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'sales_intake'               => 'استلام المبيعات',
            'showroom_review'            => 'مراجعة المعرض',
            'factory_intake'             => 'استلام المصنع',
            'department_assignment'      => 'إسناد الأقسام',
            'purchasing'                 => 'المشتريات',
            'manufacturing'              => 'التصنيع',
            'quality_after_manufacture'  => 'جودة ما بعد التصنيع',
            'installation'               => 'التركيب',
            'quality_after_installation' => 'جودة ما بعد التركيب',
            'closed'                     => 'مغلق',
            default                      => $phase,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'        => 'قيد الانتظار',
            'received'       => 'تم الاستلام',
            'under_review'   => 'قيد المراجعة',
            'approved'       => 'معتمد',
            'rejected'       => 'مرفوض',
            'in_progress'    => 'قيد التنفيذ',
            'materials_prep' => 'تحضير الخامات',
            'materials_done' => 'تم توفير الخامات',
            'on_hold'        => 'معلق',
            'completed'      => 'مكتمل',
            'cancelled'      => 'ملغي',
            default          => $status,
        };
    }

    private function roleLabel(?string $role): ?string
    {
        if (!$role) return null;

        return match ($role) {
            'factory_manager'       => 'مدير المصنع',
            'showroom_manager'      => 'مدير المعرض',
            'purchasing_manager'    => 'مدير المشتريات',
            'department_manager'    => 'رئيس القسم',
            'quality_manager'       => 'مدير الجودة',
            'installation_manager'  => 'مدير التركيب',
            'manufacturing_manager' => 'مدير التصنيع',
            'sales'                 => 'المبيعات',
            default                 => $role,
        };
    }
}
