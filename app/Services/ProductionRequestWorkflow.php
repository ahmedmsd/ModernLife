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
                'phase'         => $pr->current_phase,
                'status'        => $pr->phase_status,
                'owner'         => $pr->current_owner_role,
                'owner_user_id' => $pr->current_owner_user_id,
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
                        'owner_user_id'=> $from['owner_user_id'],
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
                    'touch_sent'       => $touchSent,
                ],
                'note'        => $noteText,
                'happened_at' => now(),
            ]);

            event(new \App\Events\ProductionRequestPhaseEvent(
                type: 'transition',
                pr: $pr->fresh(),
                context: [
                    'from'            => $from,
                    'to'              => [
                        'phase'         => $toPhase->value,
                        'status'        => $toStatus->value,
                        'owner_user_id' => $pr->current_owner_user_id,
                        'owner_role'    => $role,
                    ],
                    'owner_role'      => $role,
                    'owner_user_id'   => $pr->current_owner_user_id,
                    'prev_owner_id'   => $from['owner_user_id'] ?? null,
                    'prev_owner_role' => $from['owner'] ?? null,
                    'creator_id'      => $pr->created_by,
                    'causer_id'       => Auth::id(),
                    'touch_sent'      => $touchSent,
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

        $pr->phase_status          = S::Received->value;
        $pr->received_by_owner_at  = now();
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
                'owner_user_id' => $pr->current_owner_user_id,
                'owner_role'    => $pr->current_owner_role,
                'creator_id'    => $pr->created_by,
                'causer_id'     => Auth::id(),
            ],
        ));

        return $pr->refresh();
    }

    public function approve(ProductionRequest $pr): ProductionRequest
    {
        $currentPhase = Phase::tryFrom($pr->current_phase) ?? Phase::FactoryIntake;

        $pr = $this->move($pr, $currentPhase, S::Approved);

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
                'phase'         => $pr->current_phase,
                'from_status'   => $fromStatus,
                'to_status'     => \App\Enums\PhaseStatus::Rejected->value,
                'reason'        => $reason,
                'owner_user_id' => $pr->current_owner_user_id,
                'owner_role'    => $pr->current_owner_role,
                'creator_id'    => $pr->created_by,
                'causer_id'     => Auth::id(),
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

        $toPhase   =
            $isDirect
                ? Phase::Closed
                : Phase::ShowroomReview;

        $toStatus  = \App\Enums\PhaseStatus::Rejected;

        $fromPhase   = $pr->current_phase;
        $fromStatus  = $pr->phase_status;
        $fromRole    = $pr->current_owner_role;
        $fromOwnerId = $pr->current_owner_user_id;

        $pr->current_phase         = $toPhase->value;
        $pr->phase_status          = $toStatus->value;
        $pr->current_owner_role    = $isDirect ? null : 'showroom_manager';
        $pr->current_owner_user_id = $isDirect ? null : $this->resolveOwnerUserId($pr, 'showroom_manager');
        $pr->sent_to_owner_at      = $isDirect ? null : now();
        $pr->received_by_owner_at  = null;
        $pr->save();

        $baseNote = 'تم رفض الطلب في مرحلة الاستلام بالمصنع.';
        $noteText = $reason ? "{$baseNote} — السبب: {$reason}" : $baseNote;

        $pr->logs()->create([
            'causer_id'   => \Illuminate\Support\Facades\Auth::id(),
            'type'        => 'factory_rejected',
            'data'        => [
                'from_phase'      => $fromPhase,
                'from_phase_label'=> $this->phaseLabel((string)$fromPhase),
                'from_status'     => $fromStatus,
                'from_status_label'=> $this->statusLabel((string)$fromStatus),
                'to_phase'        => $toPhase->value,
                'to_phase_label'  => $this->phaseLabel($toPhase->value),
                'to_status'       => $toStatus->value,
                'to_status_label' => $this->statusLabel($toStatus->value),
                'from_owner_role' => $fromRole,
                'from_owner_user_id' => $fromOwnerId,
                'to_owner_role'   => $pr->current_owner_role,
                'to_owner_user_id'=> $pr->current_owner_user_id,
                'actor_name'      => optional(\Illuminate\Support\Facades\Auth::user())->name,
            ],
            'note'        => $noteText,
            'happened_at' => now(),
        ]);

        event(new \App\Events\ProductionRequestPhaseEvent(
            type: 'rejected',
            pr: $pr->fresh(),
            context: [
                'phase'         => $pr->current_phase,
                'from_status'   => $fromStatus,
                'to_status'     => $toStatus->value,
                'reason'        => $reason,
                'owner_user_id' => $pr->current_owner_user_id,
                'owner_role'    => $pr->current_owner_role,
                'creator_id'    => $pr->created_by,
                'causer_id'     => Auth::id(),
            ],
        ));

        return $pr->refresh();
    }

    public function bootstrapProject(ProductionRequest $pr): Project
    {
        return DB::transaction(function () use ($pr) {
            if ($pr->project_id) {
                throw new \LogicException('Request already has project_id set.');
            }

            $project = new Project();
            $project->client_id   = $pr->client_id;
            $project->showroom_id = $pr->showroom_id;
            $project->name        = 'مشروع من طلب تصنيع #' . $pr->id;
            $project->status      = 'active';
            $project->save();

            $pr->project_id = $project->id;
            $pr->save();

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
                    'project_id'    => $project->id,
                    'owner_user_id' => $pr->current_owner_user_id,
                    'owner_role'    => $pr->current_owner_role,
                    'creator_id'    => $pr->created_by,
                    'causer_id'     => Auth::id(),
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

    protected function roleLabel(?string $role): string
    {
        return match ($role) {
            'showroom_manager'    => 'مدير معرض',
            'factory_manager'     => 'مدير مصنع',
            'purchasing_manager'  => 'مدير المشتريات',
            'department_manager'  => 'مدير قسم',
            'quality_manager'     => 'مسؤول الجودة',
            'installation_manager'=> 'مسؤول التركيب',
            default               => (string)$role,
        };
    }

    protected function phaseLabel(string $phase): string
    {
        return match ($phase) {
            Phase::ShowroomReview->value           => 'مراجعة المعرض',
            Phase::FactoryIntake->value            => 'استلام المصنع',
            Phase::DepartmentAssignment->value     => 'توزيع الأقسام',
            Phase::Purchasing->value               => 'المشتريات',
            Phase::Manufacturing->value            => 'التصنيع',
            Phase::QualityAfterManufacture->value  => 'جودة بعد التصنيع',
            Phase::Installation->value             => 'التركيب',
            Phase::QualityAfterInstallation->value => 'جودة بعد التركيب',
            Phase::Closed->value                   => 'مغلق',
            default                                => $phase,
        };
    }

    protected function statusLabel(string $status): string
    {
        return match ($status) {
            S::Pending->value   => 'معلق',
            S::Approved->value  => 'معتمد',
            S::Rejected->value  => 'مرفوض',
            S::Received->value  => 'مستلم',
            S::Completed->value => 'مكتمل',
            default             => $status,
        };
    }

    protected function resolveOwnerUserId(ProductionRequest $pr, ?string $role): ?int
    {
        if (! $role) {
            return null;
        }

        if ($role === 'showroom_manager' && $pr->showroom_id) {
            $showroomManager = User::whereHas('employee', function ($q) use ($pr) {
                $q->whereHas('managedShowrooms', function ($qq) use ($pr) {
                    $qq->where('id', $pr->showroom_id);
                });
            })->first();

            return $showroomManager?->id;
        }

        $user = User::role($role)->first();

        return $user?->id;
    }

    public function notifyPhaseChange(ProductionRequest $pr, string $title, string $body): void
    {
        $timelineUrl = route('filament.admin.resources.production-requests.timeline', ['record' => $pr]);

        FNotification::make()
            ->title($title)
            ->body($body)
            ->actions([
                FAction::make('viewTimeline')
                    ->label('عرض سجل المراحل')
                    ->url($timelineUrl)
                    ->openUrlInNewTab(),
            ])
            ->sendToDatabase(User::whereIn('id', [$pr->current_owner_user_id, $pr->created_by])->get());
    }

    public function attachFile(ProductionRequest $pr, string $path, ?string $disk = null): void
    {
        $disk = $disk ?: config('filesystems.default', 'public');

        if (! Storage::disk($disk)->exists($path)) {
            throw new \RuntimeException("File [{$path}] does not exist on disk [{$disk}].");
        }

        $pr->files()->create([
            'path'       => $path,
            'disk'       => $disk,
            'created_by' => Auth::id(),
        ]);
    }
}
