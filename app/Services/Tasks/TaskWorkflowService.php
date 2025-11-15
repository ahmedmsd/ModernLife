<?php

namespace App\Services\Tasks;

use App\Models\ProductionTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class TaskWorkflowService
{
    /*
     * ======================= حركات رئيسية في دورة المهمة =======================
     */

    /**
     * إسناد المهمة لمدير القسم لأول مرة.
     */
    public function assignToDeptManager(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $task->status      = 'pending';
            $task->assigned_at = $task->assigned_at ?: now();
            $task->save();

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: true,
                note: 'إسناد المهمة لمدير القسم' . ($note ? ' - ' . $note : '')
            );

            $this->log($task, 'assign_to_dept_manager', [
                'note' => $note,
            ]);
        });
    }

    /**
     * تأكيد استلام مدير القسم للمهمة.
     */
    public function deptAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'received';
            $task->save();

            $this->markOwnerReceived($task, 'استلام مدير القسم للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'dept_acknowledge', [
                'note' => $note,
            ]);
        });
    }

    public function deptRejectToFactory(ProductionTask $task, string $reason): void
    {
        DB::transaction(function () use ($task, $reason) {
            $task->status = 'returned_to_factory';
            $task->save();

            $this->markOwnerReceived($task, 'رفض مدير القسم للمهمة وإعادتها للمصنع');

            $factoryUserId = $this->resolveFactoryManagerUserId();
            $this->setOwner(
                task: $task,
                role: 'factory_manager',
                userId: $factoryUserId,
                touchSent: true,
                note: 'إعادة من مدير القسم للمصنع'
            );

            $this->log($task, 'dept_reject_to_factory', [
                'reason' => $reason,
            ]);
        });
    }


    public function requestMaterials(
        ProductionTask $task,
        ?string $note = null,
        ?string $poFilePath = null,
    ): void {
        DB::transaction(function () use ($task, $note, $poFilePath) {

            $task->status = 'materials_wait';
            $task->save();

            $purchasingUserId = $this->resolvePurchasingManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'purchasing_manager',
                userId: $purchasingUserId,
                touchSent: true,
                note: 'فتح طلب خامات - تحويل للمشتريات' . ($note ? ' - ' . $note : '')
            );

            $this->log($task, 'request_materials', [
                'note'     => $note,
                'po_file'  => $poFilePath,
            ]);
        });
    }


    public function purchasingReceive(ProductionTask $task, array $data): void
    {
        DB::transaction(function () use ($task, $data) {
            $note = $data['note'] ?? null;

            $this->markOwnerReceived($task, 'استلام المشتريات للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'purchasing_receive', [
                'note'                => $note,
                'po_number'           => $data['po_number']          ?? null,
                'expected_delivery_at'=> $data['expected_delivery_at'] ?? null,
                'estimated_cost'      => $data['estimated_cost']     ?? null,
            ]);
        });
    }

    public function materialsProvided(
        ProductionTask $task,
        ?float $actualCost = null,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($task, $actualCost, $note) {
            $task->status = 'materials_prep';

            if ($actualCost !== null && property_exists($task, 'estimated_cost')) {
                $task->estimated_cost = $actualCost;
            }

            $task->save();

            $this->log($task, 'materials_provided', [
                'note'        => $note,
                'actual_cost' => $actualCost,
            ]);
        });
    }


    public function materialsReceivedOk(
        ProductionTask $task,
        ?string $plannedStart = null,
        ?string $plannedEnd = null,
        ?string $plannedInstall = null,
        ?string $note = null,
    ): void {
        DB::transaction(function () use ($task, $plannedStart, $plannedEnd, $plannedInstall, $note) {
            $task->status = 'waiting_production';

            if ($plannedStart !== null) {
                $task->planned_start_at = $plannedStart;
            }
            if ($plannedEnd !== null) {
                $task->planned_end_at = $plannedEnd;
            }
            if ($plannedInstall !== null) {
                $task->planned_install_at = $plannedInstall;
            }

            $task->save();

            $this->markOwnerReceived($task, 'استلام الخامات بالكامل - جاهز لبدء التصنيع');

            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: false,
                note: 'جاهز لبدء التصنيع بعد استلام الخامات'
            );

            $this->log($task, 'materials_received_ok', [
                'note'           => $note,
                'planned_start'  => $plannedStart,
                'planned_end'    => $plannedEnd,
                'planned_install'=> $plannedInstall,
            ]);
        });
    }


    public function materialsReceivedPartialAllowStart(
        ProductionTask $task,
        ?string $plannedStart = null,
        ?string $plannedEnd = null,
        ?string $plannedInstall = null,
        ?string $note = null,
        ?string $missingItemsNote = null,
    ): void {
        DB::transaction(function () use ($task, $plannedStart, $plannedEnd, $plannedInstall, $note, $missingItemsNote) {
            $task->status = 'waiting_production';

            if ($plannedStart !== null) {
                $task->planned_start_at = $plannedStart;
            }
            if ($plannedEnd !== null) {
                $task->planned_end_at = $plannedEnd;
            }
            if ($plannedInstall !== null) {
                $task->planned_install_at = $plannedInstall;
            }

            $task->save();

            $this->markOwnerReceived($task, 'استلام جزئي مع السماح بالبدء');

            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: false,
                note: 'استلام جزئي للخامات - يسمح ببدء التصنيع ضمن المتاح'
            );

            $this->log($task, 'materials_received_partial_allow_start', [
                'note'           => $note,
                'missing'        => $missingItemsNote,
                'planned_start'  => $plannedStart,
                'planned_end'    => $plannedEnd,
                'planned_install'=> $plannedInstall,
                'allow_start'    => true,
            ]);
        });
    }

    public function materialsReceivedPartialHold(
        ProductionTask $task,
        ?string $note = null,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $note, $missingItemsNote) {
            $task->status = 'materials_wait';
            $task->save();

            $this->markOwnerReceived($task, 'استلام جزئي مع إيقاف البدء');

            $purchasingUserId = $this->resolvePurchasingManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'purchasing_manager',
                userId: $purchasingUserId,
                touchSent: false,
                note: 'نواقص خامات - انتظار توريد تكميلي'
            );

            $this->log($task, 'materials_received_partial_hold', [
                'note'    => $note,
                'missing' => $missingItemsNote,
            ]);
        });
    }

    public function materialsReceivedIssue(
        ProductionTask $task,
        ?string $note = null,
        ?string $issueDetails = null,
    ): void {
        DB::transaction(function () use ($task, $note, $issueDetails) {
            $task->status = 'materials_issue';
            $task->save();

            $this->markOwnerReceived($task, 'مشكلة في الخامات');

            $purchasingUserId = $this->resolvePurchasingManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'purchasing_manager',
                userId: $purchasingUserId,
                touchSent: false,
                note: 'مشكلة في الخامات - انتظار المعالجة'
            );

            $this->log($task, 'materials_issue', [
                'note'          => $note,
                'issue_details' => $issueDetails,
            ]);
        });
    }

    /**
     * بدء التصنيع.
     */
    public function startProduction(
        ProductionTask $task,
        ?string $startedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $task->status         = 'in_progress';
            $task->actual_start_at = $task->actual_start_at ?: (
            $startedAt ? Carbon::parse($startedAt) : now()
            );
            $task->save();

            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: false,
                note: 'بدء التصنيع' . ($note ? ' - ' . $note : '')
            );

            $this->log($task, 'manufacturing_started', [
                'note'       => $note,
                'started_at' => $task->actual_start_at?->toDateTimeString(),
            ]);
        });
    }

    /**
     * إنهاء التصنيع وإرسال المهمة للجودة بعد التصنيع.
     */
    public function finishManufacturingAndSendToQA(
        ProductionTask $task,
        ?string $finishedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $finishedAt, $note) {
            $task->status        = 'under_review';
            $task->actual_end_at = $task->actual_end_at ?: (
            $finishedAt ? Carbon::parse($finishedAt) : now()
            );
            $task->save();

            $qaUserId = $this->resolveQualityManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'quality_manager',
                userId: $qaUserId,
                touchSent: true,
                note: 'إرسال للجودة بعد التصنيع'
            );

            $this->log($task, 'manufacturing_sent_to_qa', [
                'note'        => $note,
                'finished_at' => $task->actual_end_at?->toDateTimeString(),
            ]);
        });
    }

    public function qaAcknowledgeManufacturing(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام الجودة بعد التصنيع' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'qa_ack_manufacturing', [
                'note' => $note,
            ]);
        });
    }

    public function approveManufacturingQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'approved';
            $task->save();

            $installUserId = $this->resolveInstallationManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'installation_manager',
                userId: $installUserId,
                touchSent: true,
                note: 'اعتماد الجودة بعد التصنيع - تحويل للتركيب'
            );

            $this->log($task, 'qa_approved_manufacturing', [
                'note' => $note,
            ]);
        });
    }

    public function rejectManufacturingQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'rework';
            $task->save();

            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: true,
                note: 'رفض الجودة بعد التصنيع - إعادة للتصنيع'
            );

            $this->log($task, 'qa_rejected_manufacturing', [
                'note' => $note,
            ]);
        });
    }
    /**
     * استلام مسؤول التركيب للمهمة.
     */
    public function installationAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام مسؤول التركيب للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'install_acknowledged', [
                'note' => $note,
            ]);
        });
    }

    /**
     * بدء التركيب.
     */
    public function startInstallation(
        ProductionTask $task,
        ?string $startedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $task->status = 'in_progress';
            $task->save();

            $installUserId = $this->resolveInstallationManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'installation_manager',
                userId: $installUserId,
                touchSent: false,
                note: 'بدء أعمال التركيب' . ($note ? ' - ' . $note : '')
            );

            $this->log($task, 'installation_started', [
                'note'       => $note,
                'started_at' => $startedAt,
            ]);
        });
    }

    /**
     * إنهاء التركيب وإرسال للجودة بعد التركيب.
     */
    public function finishInstallationToQA(
        ProductionTask $task,
        ?string $finishedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $finishedAt, $note) {
            $task->status = 'under_review';
            $task->save();

            $qaUserId = $this->resolveQualityManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'quality_manager',
                userId: $qaUserId,
                touchSent: true,
                note: 'إنهاء التركيب - تحويل للجودة بعد التركيب'
            );

            $this->log($task, 'installation_sent_to_qa', [
                'note'        => $note,
                'finished_at' => $finishedAt,
            ]);
        });
    }

    /**
     * استلام الجودة للمهمة بعد التركيب.
     */
    public function qaAcknowledgeInstallation(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام الجودة بعد التركيب' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'qa_ack_installation', [
                'note' => $note,
            ]);
        });
    }

    public function approveInstallationQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status                = 'qa_approved';
            $task->current_owner_role    = 'quality_manager';
            $task->current_owner_user_id = null;
            $task->received_by_owner_at  = now();
            $task->save();

            $qaUserId = $this->resolveQualityManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'quality_manager',
                userId: $qaUserId,
                touchSent: true,
                note: 'اعتماد الجودة للتركيب'
            );

            $this->log($task, 'qa_approved_installation', [
                'note' => $note,
            ]);
        });
    }

    public function rejectInstallationQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'rework';
            $task->save();

            $installUserId = $this->resolveInstallationManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'installation_manager',
                userId: $installUserId,
                touchSent: true,
                note: 'رفض الجودة بعد التركيب - إعادة للتركيب'
            );

            $this->log($task, 'qa_rejected_installation', [
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام عمل مُعاد في التصنيع.
     */
    public function manufacturingAcknowledgeRework(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام العمل المُعاد في التصنيع' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'manufacturing_ack_rework', [
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام عمل مُعاد في التركيب.
     */
    public function installationAcknowledgeRework(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام العمل المُعاد في التركيب' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'install_ack_rework', [
                'note' => $note,
            ]);
        });
    }

    /**
     * إكمال المهمة بعد رفع مستند تأكيد العميل.
     */
    public function uploadClientReceiptAndComplete(
        ProductionTask $task,
        ?string $clientReceiptPath = null,
        ?string $completedAt = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $clientReceiptPath, $completedAt, $note) {
            if (! empty($clientReceiptPath)) {
                $task->client_receipt = $clientReceiptPath;
            }

            if ($completedAt) {
                $task->completed_at = Carbon::parse($completedAt);
            } elseif (! $task->completed_at) {
                $task->completed_at = now();
            }

            $task->status                = 'completed';
            $task->current_owner_role    = null;
            $task->current_owner_user_id = null;
            $task->save();

            $this->log($task, 'upload_client_receipt_and_complete', [
                'note'           => $note,
                'client_receipt' => $clientReceiptPath,
                'completed_at'   => $task->completed_at?->toDateTimeString(),
            ]);

            // إقفال المشروع/الطلب إن لم تبقَ مهام مفتوحة
            $finalStatuses = ['completed', 'cancelled', 'closed'];

            $project = $task->project()
                ->withCount(['tasks as open_tasks_count' => function ($q) use ($finalStatuses) {
                    $q->whereNotIn('status', $finalStatuses);
                }])
                ->first();

            if ($project && (int) $project->open_tasks_count === 0) {
                $projUpdate = ['status' => 'completed'];
                if (Schema::hasColumn($project->getTable(), 'completed_at')) {
                    $projUpdate['completed_at'] = now();
                } elseif (Schema::hasColumn($project->getTable(), 'closed_at')) {
                    $projUpdate['closed_at'] = now();
                }
                $project->update($projUpdate);

                $this->log($task, 'project_completed', [
                    'project_id' => $project->id,
                    'by'         => Auth::id(),
                ]);

                $pr = $project->productionRequest ?? null;
                if ($pr) {
                    $prUpdate = [];
                    if (Schema::hasColumn($pr->getTable(), 'current_phase')) {
                        $prUpdate['current_phase'] = 'closed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'phase_status')) {
                        $prUpdate['phase_status'] = 'completed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'status')) {
                        $prUpdate['status'] = 'completed';
                    }
                    if (Schema::hasColumn($pr->getTable(), 'closed_at')) {
                        $prUpdate['closed_at'] = now();
                    } elseif (Schema::hasColumn($pr->getTable(), 'completed_at')) {
                        $prUpdate['completed_at'] = now();
                    }
                    if (!empty($prUpdate)) {
                        $pr->update($prUpdate);
                    }

                    $this->log($task, 'production_request_closed', [
                        'production_request_id' => $pr->id,
                        'by'                    => Auth::id(),
                    ]);
                }


            }

        });
    }

    /*
     * ======================= Helpers: المالك واللوج =======================
     */

    protected function setOwner(
        ProductionTask $task,
        ?string $role,
        ?int $userId,
        bool $touchSent = false,
        ?string $note = null
    ): void {
        $task->current_owner_role    = $role;
        $task->current_owner_user_id = $userId;

        if ($touchSent) {
            $task->sent_to_owner_at     = now();
            $task->received_by_owner_at = null;
        }

        $task->save();

        $this->log($task, 'owner_changed', [
            'role'    => $role,
            'user_id' => $userId,
            'note'    => $note,
        ]);
    }

    protected function markOwnerReceived(ProductionTask $task, ?string $reason = null): void
    {
        $task->received_by_owner_at = now();
        $task->save();

        $this->log($task, 'owner_received', [
            'reason' => $reason,
        ]);
    }

    protected function resolveDeptManagerUserId(ProductionTask $task): ?int
    {
        $dept = $task->department;

        if (! $dept || ! $dept->managerUser) {
            return null;
        }

        return (int) $dept->managerUser->id;
    }


    protected function resolveInstallationManagerUserId(ProductionTask $task): ?int
    {
        return $this->resolveDeptManagerUserId($task);
    }


    protected function resolvePurchasingManagerUserId(): ?int
    {
        return User::role('purchasing_manager')->value('id');
    }

    protected function resolveFactoryManagerUserId(): ?int
    {
        return User::role('factory_manager')->value('id');
    }

    protected function resolveQualityManagerUserId(): ?int
    {
        return User::role('quality_manager')->value('id');
    }

    protected function log(ProductionTask $task, string $type, array $data = []): void
    {
        $payload = [
            'type'      => $type,
            'data'      => $data,
            'causer_id' => Auth::id(),
        ];

        if (method_exists($task, 'logs')) {
            $task->logs()->create($payload);
        } elseif (method_exists($task, 'taskLogs')) {
            $task->taskLogs()->create($payload);
        }
    }
}
