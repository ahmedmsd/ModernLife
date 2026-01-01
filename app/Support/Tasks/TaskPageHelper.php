<?php

namespace App\Support\Tasks;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Carbon;

class TaskPageHelper
{
    /* ========================================================================
     |  Status helpers
     |=========================================================================*/

    public function normalizeStatus(?string $status): string
    {
        $status = strtolower((string) $status);

        return match ($status) {
            'waiting_production', 'waiting-production', 'waitingproduction' => 'waiting_production',
            'in_progress', 'in-progress', 'inprogress'                      => 'in_progress',
            'under_review', 'under-review', 'underreview'                   => 'under_review',
            'approved'                                                      => 'approved',
            'rejected'                                                      => 'rejected',
            'rework', 're_work'                                             => 'rework',
            'on_hold', 'hold'                                               => 'on_hold',
            'closed', 'done', 'finished'                                    => 'closed',
            default                                                         => $status,
        };
    }

    /**
     * إرجاع قيمة الـ status الموحّدة سواء استلمنا Task أو نص.
     */
    public function statusVal(ProductionTask|string|null $taskOrStatus): string
    {
        if ($taskOrStatus instanceof ProductionTask) {
            $raw = $taskOrStatus->status ?? '';
        } else {
            $raw = (string) $taskOrStatus;
        }

        return $this->normalizeStatus($raw);
    }


    public function statusAr(ProductionTask|string|null $taskOrStatus): string
    {
        $status = $this->statusVal($taskOrStatus);

        return match ($status) {
            'pending'            => 'بالانتظار',
            'waiting_production' => 'في انتظار بدء التصنيع',
            'in_progress'        => 'جاري التنفيذ',
            'under_review'       => 'قيد المراجعة',
            'approved'           => 'معتمد',
            'rejected'           => 'مرفوض',
            'rework'             => 'إعادة عمل',
            'on_hold'            => 'موقوف مؤقتاً',
            'materials_wait'     => 'بانتظار الخامات',
            'materials_prep'     => 'تجهيز الخامات',
            'materials_done'     => 'الخامات جاهزة',
            'closed'             => 'مغلقة',
            default              => $status !== '' ? $status : '-',
        };
    }

    /**
     * لون Filament للحالة – يقبل Task أو سترينج.
     */
    public function statusColor(ProductionTask|string|null $taskOrStatus): string
    {
        $status = $this->statusVal($taskOrStatus);

        return match ($status) {
            'pending'            => 'warning',
            'waiting_production' => 'warning',
            'in_progress'        => 'primary',
            'under_review'       => 'info',
            'approved'           => 'success',
            'rejected'           => 'danger',
            'rework'             => 'danger',
            'on_hold'            => 'gray',
            'closed'             => 'secondary',
            default              => 'secondary',
        };
    }

    /**
     * اللون الهكس للحالة – يقبل Task أو سترينج.
     */
    public function statusHex(ProductionTask|string|null $taskOrStatus): string
    {
        $status = $this->statusVal($taskOrStatus);

        return match ($status) {
            'pending'            => '#f59e0b', // amber
            'waiting_production' => '#f59e0b', // amber
            'in_progress'        => '#3b82f6', // blue
            'under_review'       => '#0ea5e9', // sky
            'approved'           => '#22c55e', // green
            'rejected'           => '#ef4444', // red
            'rework'             => '#dc2626', // red-700
            'on_hold'            => '#6b7280', // gray
            'closed'             => '#4b5563', // gray-700
            default              => '#9ca3af', // gray-400
        };
    }

    /* ========================================================================
     |  Ownership & Roles helpers
     |=========================================================================*/

    public function userHasAnyRole(?Authenticatable $user, array $roles): bool
    {
        if (! $user) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return $user->hasAnyRole($roles);
        }

        if (method_exists($user, 'hasRole')) {
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    return true;
                }
            }
        }

        return false;
    }

    public function ownerIs(ProductionTask $task, ?string $role): bool
    {
        return ($task->current_owner_role ?? null) === $role;
    }

    public function isOwnerUser(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user) {
            return false;
        }

        // 1. If user is Super Admin or Factory Manager, they can override ownership for actions
        if ($this->userHasAnyRole($user, ['admin', 'super-admin', 'factory_manager'])) {
            return true;
        }

        $ownerRole = $task->current_owner_role;
        $ownerId   = $task->current_owner_user_id;

        // 2. Exact match if owner ID is set (legacy/direct assignment)
        if ($ownerId && (int) $ownerId === (int) $user->id) {
            return true;
        }

        // 3. Role-based match if owner ID is NOT set or mismatching but role matches
        if ($ownerRole) {
            if ($this->userHasAnyRole($user, [$ownerRole])) {
                // For specific roles, we might want additional context checks
                if ($ownerRole === 'department_manager') {
                    // Does the user manage the department of the task?
                    $isManager = $user->managedDepartments()->where('dept_id', $task->department_id)->exists();
                    $isEmployee = $user->employee?->department_id == $task->department_id;
                    return $isManager || $isEmployee;
                }

                return true;
            }
        }

        // 4. Default Filament behavior or blank owner logic
        if (blank($ownerId)) {
            return true;
        }

        return false;
    }

    /* ========================================================================
     |  Actions visibility – القسم / المواد / التصنيع
     |=========================================================================*/

    public function canDeptAcknowledge(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        if (! in_array($status, ['pending', 'rework'], true)) {
            return false;
        }

        $anchor = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'assigned_to_dept',
                'assigned_to_dept_manager',
                'sent_to_department',
                'sent_back_to_manufacturing',
                'qa_rejected_manufacturing', // Added
                'assign_to_dept_manager', // Added
            ])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $anchor) {
            return false;
        }

        $t  = $anchor->happened_at ?? $anchor->created_at;
        $id = $anchor->id;

        $ackAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'dept_acknowledged')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $ackAfter;
    }

    public function canDeptReject(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        
        // Allow rejection if task is pending, rework, received, or waiting for production
        // This covers "Before Acknowledgment" (pending) and "After Acknowledgment" (received/waiting)
        return in_array($status, ['pending', 'rework', 'received', 'waiting_production'], true);
    }

    public function canRequestMaterials(ProductionTask $task, ?Authenticatable $user): bool
    {
        // Check Roles first
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        $status = $this->statusVal($task);

        // Define valid statuses
        // We add 'materials_wait', 'materials_prep', 'materials_done' to allow supplementary requests
        $validStatuses = [
            'waiting_production', 'rework', 'received',
            'materials_wait', 'materials_prep', 'materials_done'
        ];

        if (! in_array($status, $validStatuses, true)) {
            return false;
        }

        // Ownership logic:
        // 1. If currently with Department Manager -> OK
        if ($this->ownerIs($task, 'department_manager')) {
            return true;
        }

        // 2. If it is with Purchasing Manager (and in materials phase) -> OK (Supplementary)
        if ($this->ownerIs($task, 'purchasing_manager') && in_array($status, ['materials_wait', 'materials_prep', 'materials_done'], true)) {
            return true;
        }

        return false;
    }

    public function canPurchasingReceive(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['purchasing_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'purchasing_manager')) {
            return false;
        }

        if (! $this->hasOpenMaterialsRequest($task)) {
            return false;
        }

        return true;
    }

    public function canMaterialsProvided(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['purchasing_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'purchasing_manager')) {
            return false;
        }

        if (! $this->hasOpenMaterialsRequest($task)) {
            return false;
        }

        return true;
    }

    public function canMaterialsReceivedOk(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        $allowedStatuses = ['materials_wait', 'materials_prep','materials_done', 'waiting_production', 'rework', 'in_progress', 'on_hold'];
        if (! in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $hasOpenMr = $this->hasOpenMaterialsRequest($task);
        $materialsState = $task->materials_state ?? 'none';

        if (! $hasOpenMr && $materialsState !== 'full' && $materialsState !== 'partial_allow') {
            return false;
        }

        return true;
    }


    public function canStartProduction(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        if (! in_array($status, ['waiting_production', 'rework'], true)) {
            return false;
        }

        // منطق الـ anchor كما في ViewTask (ack_rework أو materials_received أو planning_hint)
        $anchor = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_ack_rework')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $anchor) {
            $anchor = TaskLog::query()
                ->where('task_id', $task->id)
                ->where(function ($q) {
                    $q->where('type', 'materials_received_ok')
                        ->orWhere(function ($q2) {
                            $q2->where('type', 'materials_received_partial')
                                ->where('data->allow_start', true);
                        })
                        ->orWhere('type', 'planning_hint_set');
                })
                ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                ->first();
        }

        if (! $anchor) {
            return false;
        }

        $anchorTime = $anchor->happened_at ?? $anchor->created_at;
        $anchorId   = $anchor->id;

        $startedAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_started')
            ->where(function ($q) use ($anchorTime, $anchorId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                    ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                            ->where('id', '>', $anchorId);
                    });
            })
            ->exists();

        return ! $startedAfter;
    }

    public function canFinishManufacturing(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['department_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        if (! in_array($status, ['waiting_production', 'rework', 'in_progress'], true)) {
            return false;
        }

        $lastStart = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_started')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastStart) {
            return false;
        }

        $t  = $lastStart->happened_at ?? $lastStart->created_at;
        $id = $lastStart->id;

        // لم يتم الإرسال للجودة بعد هذا الـ start
        $sentAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_sent_to_qa')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $sentAfter;
    }

    /* ========================================================================
     |  Actions visibility – QA بعد التصنيع
     |=========================================================================*/

    public function canQaAcknowledgeManufacturing(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        $lastHandoff = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['manufacturing_sent_to_qa', 'installation_sent_to_qa', 'sent_to_quality'])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') {
            return false;
        }

        $t  = $lastHandoff->happened_at ?? $lastHandoff->created_at;
        $id = $lastHandoff->id;

        $ackExistsAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'qa_ack_manufacturing')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $ackExistsAfter;
    }

    public function canApproveManufacturingQA(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        $lastHandoff = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['manufacturing_sent_to_qa', 'installation_sent_to_qa', 'sent_to_quality'])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') {
            return false;
        }

        $t  = $lastHandoff->happened_at ?? $lastHandoff->created_at;
        $id = $lastHandoff->id;

        $ackAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'qa_ack_manufacturing')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        if (! $ackAfter) {
            return false;
        }

        $decisionExistsAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['qa_approved_manufacturing', 'qa_rejected_manufacturing'])
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $decisionExistsAfter;
    }

    public function canRejectManufacturingQA(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        $lastHandoff = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['manufacturing_sent_to_qa', 'installation_sent_to_qa', 'sent_to_quality'])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') {
            return false;
        }

        $t  = $lastHandoff->happened_at ?? $lastHandoff->created_at;
        $id = $lastHandoff->id;

        $ackAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'qa_ack_manufacturing')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        if (! $ackAfter) {
            return false;
        }

        $decisionAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['qa_approved_manufacturing', 'qa_rejected_manufacturing'])
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $decisionAfter;
    }

    public function canManufacturingAcknowledgeRework(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->ownerIs($task, 'department_manager')) {
            return false;
        }

        $lastBack = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'sent_back_to_manufacturing')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastBack) {
            return false;
        }

        $t  = $lastBack->happened_at ?? $lastBack->created_at;
        $id = $lastBack->id;

        $ackReworkAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['manufacturing_ack_rework', 'dept_acknowledged']) // Added dept_acknowledged as alternative ack
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $ackReworkAfter;
    }

    /* ========================================================================
     |  Actions visibility – التركيب و QA بعد التركيب
     |=========================================================================*/

    public function canInstallationAcknowledgeAfterQAApprove(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! ($this->ownerIs($task, 'installation_manager') || $this->ownerIs($task, 'department_manager'))) {
            return false;
        }

        $lastApprove = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'qa_approved_manufacturing')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastApprove) {
            return false;
        }

        $t  = $lastApprove->happened_at ?? $lastApprove->created_at;
        $id = $lastApprove->id;

        $ackInstallAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'install_acknowledged')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $ackInstallAfter;
    }

    public function canStartInstallation(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user || ! $this->userHasAnyRole($user, ['installation_manager'])) {
            return false;
        }

        if (! ($this->ownerIs($task, 'installation_manager') || $this->ownerIs($task, 'department_manager')) || ! $this->isOwnerUser($task, $user)) {
            return false;
        }

        $lastAck = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['install_acknowledged', 'install_ack_rework'])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastAck) {
            return false;
        }

        $t  = $lastAck->happened_at ?? $lastAck->created_at;
        $id = $lastAck->id;

        $startedAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'installation_started')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $startedAfter;
    }

    public function canFinishInstallationToQA(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user || ! $this->userHasAnyRole($user, ['installation_manager'])) {
            return false;
        }

        if (! ($this->ownerIs($task, 'installation_manager') || $this->ownerIs($task, 'department_manager')) || ! $this->isOwnerUser($task, $user)) {
            return false;
        }

        // آخر استلام تركيب (قديم + جديد)
        $lastAck = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'install_acknowledged',
                'installation_acknowledge',
                'install_ack_rework',
                'installation_acknowledge_rework',
            ])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastAck) return false;

        $t = $lastAck->happened_at ?? $lastAck->created_at;
        $id = $lastAck->id;

        $startedAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'installation_started',
                'start_installation',
            ])
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        if (! $startedAfter) return false;

        $sentAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'installation_sent_to_qa',
                'finish_installation_to_qa',
            ])
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $sentAfter;
    }


    public function canQaAcknowledgeInstallation(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user || ! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        if ($this->statusVal($task) !== 'under_review') {
            return false;
        }

        // آخر ارسال (جديد + قديم)
        $lastInstall = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'installation_sent_to_qa',
                'finish_installation_to_qa',
            ])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastInstall) return false;

        $iAt = $lastInstall->happened_at ?? $lastInstall->created_at;
        $iId = $lastInstall->id;

        // منع التكرار (قديم + جديد)
        $ackExists = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'qa_ack_installation',
                'qa_acknowledge_installation',
            ])
            ->where(function ($q) use ($iAt, $iId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$iAt])
                    ->orWhere(function ($q2) use ($iAt, $iId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$iAt])
                            ->where('id', '>', $iId);
                    });
            })
            ->exists();

        return ! $ackExists;
    }


    public function canApproveInstallationQA(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user || ! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        if ($this->statusVal($task) !== 'under_review') {
            return false;
        }

        // إرسال (قديم + جديد)
        $lastInstall = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'installation_sent_to_qa',
                'finish_installation_to_qa',
            ])
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastInstall) return false;

        $iAt = $lastInstall->happened_at ?? $lastInstall->created_at;
        $iId = $lastInstall->id;

        // استلام الجودة (قديم + جديد)
        $ackAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'qa_ack_installation',
                'qa_acknowledge_installation',
            ])
            ->where(function ($q) use ($iAt, $iId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$iAt])
                    ->orWhere(function ($q2) use ($iAt, $iId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$iAt])
                            ->where('id', '>', $iId);
                    });
            })
            ->exists();

        if (! $ackAfter) return false;

        // عدم وجود قرار سابق
        $decisionAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', [
                'qa_approved_installation',
                'qa_rejected_installation',
            ])
            ->where(function ($q) use ($iAt, $iId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$iAt])
                    ->orWhere(function ($q2) use ($iAt, $iId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$iAt])
                            ->where('id', '>', $iId);
                    });
            })
            ->exists();

        return ! $decisionAfter;
    }


    public function canRejectInstallationQA(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $user || ! $this->userHasAnyRole($user, ['quality_manager'])) {
            return false;
        }

        if (! $this->ownerIs($task, 'quality_manager')) {
            return false;
        }

        $status = $this->statusVal($task);
        if ($status !== 'under_review') {
            return false;
        }

        $lastInstall = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'installation_sent_to_qa')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastInstall) {
            return false;
        }

        $lastMfg = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_sent_to_qa')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        $iAt = $lastInstall->happened_at ?? $lastInstall->created_at;
        $mAt = $lastMfg?->happened_at ?? $lastMfg?->created_at;

        $installIsLatest = ! $lastMfg
            || ($iAt > $mAt)
            || ($iAt == $mAt && $lastInstall->id > $lastMfg->id);

        if (! $installIsLatest) {
            return false;
        }

        $ackAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'qa_ack_installation')
            ->where(function ($q) use ($iAt, $lastInstall) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$iAt])
                    ->orWhere(function ($q2) use ($iAt, $lastInstall) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$iAt])
                            ->where('id', '>', $lastInstall->id);
                    });
            })
            ->exists();

        if (! $ackAfter) {
            return false;
        }

        $decisionAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['qa_approved_installation', 'qa_rejected_installation'])
            ->where(function ($q) use ($iAt, $lastInstall) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$iAt])
                    ->orWhere(function ($q2) use ($iAt, $lastInstall) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$iAt])
                            ->where('id', '>', $lastInstall->id);
                    });
            })
            ->exists();

        return ! $decisionAfter;
    }

    public function canInstallationAcknowledgeRework(ProductionTask $task, ?Authenticatable $user): bool
    {
        if (! $this->ownerIs($task, 'installation_manager')) {
            return false;
        }

        $lastBack = TaskLog::query()
            ->where('task_id', $task->id)
            ->whereIn('type', ['sent_back_to_install', 'qa_rejected_installation']) // Added qa_rejected_installation
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        if (! $lastBack) {
            return false;
        }

        $t  = $lastBack->happened_at ?? $lastBack->created_at;
        $id = $lastBack->id;

        $ackReworkAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'install_ack_rework')
            ->where(function ($q) use ($t, $id) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                    ->orWhere(function ($q2) use ($t, $id) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                            ->where('id', '>', $id);
                    });
            })
            ->exists();

        return ! $ackReworkAfter;
    }

    /* ========================================================================
     |  Logs / Requests helpers
     |=========================================================================*/

    public function hasOpenMaterialsRequest(ProductionTask $task): bool
    {
        return \App\Models\MaterialRequest::query()
            ->where('task_id', $task->id)
            ->whereNotIn('status', ['cancelled', 'closed'])
            ->exists();
    }

    public function hasLog(ProductionTask $task, string $type): bool
    {
        return TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', $type)
            ->exists();
    }

    public function lastLogTime(ProductionTask $task, string $type): ?Carbon
    {
        $log = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', $type)
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();

        return $log ? ($log->happened_at ?? $log->created_at) : null;
    }

    /* ========================================================================
     |  Durations / Timeline helpers
     |=========================================================================*/

    public function computeStageDurations(ProductionTask $task): array
    {
        $logs = TaskLog::query()
            ->where('task_id', $task->id)
            ->orderByRaw('COALESCE(happened_at, created_at), id')
            ->get();

        $result = [];

        foreach ($logs as $log) {
            $time = $log->happened_at ?? $log->created_at;
            $type = $log->type;

            $result[] = [
                'type' => $type,
                'at'   => $time,
            ];
        }

        return $result;
    }

    public function renderStageDurationsHtml(ProductionTask $task): string
    {
        $statusHex = $this->statusHex($task);
        $durations = $this->computeStageDurations($task);

        if (empty($durations)) {
            return '<span class="text-muted">لا توجد بيانات زمنية كافية</span>';
        }

        $items = [];
        foreach ($durations as $d) {
            $timeStr = $d['at'] instanceof Carbon
                ? $d['at']->format('Y-m-d H:i')
                : (string) $d['at'];

            $items[] = sprintf(
                '<li><strong>%s</strong> - <span class="text-xs text-gray-500">%s</span></li>',
                e($d['type']),
                e($timeStr)
            );
        }

        return <<<HTML
<div style="border-radius: .5rem; border: 1px solid {$statusHex}33; padding: .55rem .75rem; background: {$statusHex}08;">
    <div style="font-size: .8rem; font-weight: 600; margin-bottom: .25rem; color: {$statusHex};">
        التسلسل الزمني للمهمة
    </div>
    <ul style="padding-left: 1.1rem; margin: 0; font-size: .78rem; line-height: 1.25;">
        {implode('', $items)}
    </ul>
</div>
HTML;
    }

    /* ========================================================================
     |  Navigation helpers
     |=========================================================================*/

    public function parentTasksUrl($a = null, $b = null): string
    {
        $u = $a instanceof \Illuminate\Contracts\Auth\Authenticatable ? $a : null;
        $t = $a instanceof \App\Models\ProductionTask ? $a : ($b instanceof \App\Models\ProductionTask ? $b : null);

        if (! $t) {
            // fallback آمن
            return url('/admin/tasks');
        }

        if ($u && method_exists($u, 'hasAnyRole') && $u->hasAnyRole(['super-admin','admin','project_manager'])) {
            return $t->project_id
                ? url("/admin/projects/{$t->project_id}/manage-tasks")
                : url('/admin/tasks');
        }

        return url('/admin/tasks/active');
    }

    public function parentTasksLabel($a = null, $b = null): string
    {
        // تحديد الكائنات بشكل مرن مثل parentTasksUrl()
        $u = $a instanceof \Illuminate\Contracts\Auth\Authenticatable ? $a : null;
        $t = $a instanceof \App\Models\ProductionTask ? $a : ($b instanceof \App\Models\ProductionTask ? $b : null);

        if (! $t) {
            return 'جميع المهام';
        }

        if ($t->project_id) {
            return 'مهام المشروع';
        }

        return 'جميع المهام';
    }

    public function isClosedOrCompleted(ProductionTask $task): bool
    {
        return in_array($this->statusVal($task), ['approved', 'rejected', 'closed'], true);
    }
}
