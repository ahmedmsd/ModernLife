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
    public function assignToDeptManager(\App\Models\ProductionTask $task, ?string $note = null, string $dueDate): void
    {
        DB::transaction(function () use ($task, $dueDate , $note) {
            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $task->forceFill([
                'assigned_to_user_id' => $deptManagerId,
                'status'                  => 'pending',
                'assigned_at'             => now(),
                'due_date'                => $dueDate,
            ])->save();

            $changed = $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: true,
                note: 'إسناد المهمة لمدير القسم' . ($note ? ' - ' . $note : '')
            );

            if ($changed) {
                $this->log($task, 'assign_to_dept_manager', [
                    'note' => $note,
                ]);
            } else {
                $this->log($task, 'assign_to_dept_manager_noop', [
                    'note' => $note,
                ]);
            }
        });
    }


    public function deptAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);
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


    public function requestMaterials(ProductionTask $task, string $note, string $poFilePath): void
    {
        DB::transaction(function () use ($task, $note, $poFilePath) {
            \App\Models\MaterialRequest::create([
                'task_id'       => $task->id,
                'department_id' => $task->department_id,
                'requested_by'  => Auth::id(),
                'requested_at'  => now(),
                'status'        => 'requested',
                'note'          => $note,
                'po_file'       => $poFilePath,
            ]);

            $task->update(['status' => 'materials_wait']);
            $pmUserId = $this->resolvePurchasingManagerUserId($task);

            if ($pmUserId) {
                $this->setOwner(
                    $task,
                    'purchasing_manager',
                    userId: $pmUserId,
                    touchSent: true,
                    note: 'فتح طلب خامات — تحويل للمشتريات'
                );
            }
            $this->log($task, 'materials_request_opened', ['by' => Auth::id()]);
        });
    }

    public function purchasingReceive(ProductionTask $task, array $data): void
    {
        DB::transaction(function () use ($task, $data) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            $mr->update([
                'po_number'            => $data['po_number']            ?? $mr->po_number,
                'estimated_cost'       => $data['estimated_cost']       ?? $mr->estimated_cost,
                'expected_delivery_at' => $data['expected_delivery_at'],
                'note'                 => trim(($mr->note ? $mr->note . "\n" : '') . ($data['note'] ?? '')),
                'status'               => 'approved',
                'approved_at'          => now(),
                'approved_by'          => Auth::id(),
            ]);

            $task->update(['status' => 'materials_prep']);

            $this->log($task, 'purchasing_ack', ['by' => Auth::id()]);
        });
    }

    public function materialsProvided(ProductionTask $task, float $actualCost, ?string $note = null, ?array $invoice = null): void
    {
        DB::transaction(function () use ($task, $actualCost, $note, $invoice) {
            $mr = $task->materialRequests()
                ->whereIn('status', ['approved','supplying']) // فقط الطلب “القابل للتوريد”
                ->orderByDesc('id')
                ->lockForUpdate()
                ->firstOrFail();

            $invoiceNo   = $invoice['invoice_no']   ?? null;
            $invoiceDate = isset($invoice['invoice_date']) && $invoice['invoice_date']
                ? \Illuminate\Support\Carbon::parse($invoice['invoice_date'])
                : null;
            $invoiceFile = $invoice['invoice_file'] ?? null;

            $mr->update([
                'actual_cost'  => $actualCost,
                'invoice_no'   => $invoiceNo   ?? $mr->invoice_no,
                'invoice_date' => $invoiceDate ?? $mr->invoice_date,
                'invoice_file' => $invoiceFile ?? $mr->invoice_file,
                'note'         => trim(($mr->note ? $mr->note . "\n" : '') . ($note ?? '')),
                'provided_by'  => Auth::id(),
                'provided_at'  => now(),
                'status'       => 'fulfilled',
            ]);

            $task->update(['status' => 'materials_done']);


            $this->log($task, 'materials_provided_note', [
                'mr_id'       => $mr->id,
                'actual_cost' => $actualCost,
                'note'        => $note ? trim($note) : null,
            ]);

        });
    }


    public function materialsReceivedOk(
        ProductionTask $task,
        ?string $start = null,
        ?string $end = null,
        ?string $install = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $start, $end, $install, $note) {
            $payload = ['status' => 'waiting_production'];
            if ($start)   $payload['planned_start_at']   = \Illuminate\Support\Carbon::parse($start);
            if ($end)     $payload['planned_end_at']     = \Illuminate\Support\Carbon::parse($end);
            if ($install) $payload['planned_install_at'] = \Illuminate\Support\Carbon::parse($install);
            $task->update($payload);

            $this->log($task, 'materials_received_ok', [
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
                'by'                 => Auth::id(),
            ]);

            $this->markOwnerReceived($task, 'استلام الخامات — جاهز لبدء التصنيع');

            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? $task->assigned_to_employee?->user_id
                ?? null;

            if ($deptManagerId) {
                $this->setOwner(
                    $task,
                    'department_manager',
                    userId: $deptManagerId,
                    touchSent: false,
                    note: 'جاهز لبدء التصنيع بعد استلام الخامات'
                );
            }
        });
    }

    public function materialsReceivedPartialAllowStart(
        ProductionTask $task,
        ?string $start,
        ?string $end,
        ?string $install,
        ?string $note,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $start, $end, $install, $note, $missingItemsNote) {
            $this->openFollowupMaterialsRequest($task, $missingItemsNote);

            $payload = ['status' => 'waiting_production'];
            if ($start)   $payload['planned_start_at']   = \Illuminate\Support\Carbon::parse($start);
            if ($end)     $payload['planned_end_at']     = \Illuminate\Support\Carbon::parse($end);
            if ($install) $payload['planned_install_at'] = \Illuminate\Support\Carbon::parse($install);
            $task->update($payload);

            $this->log($task, 'materials_received_partial', [
                'allow_start'        => true,
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
                'missing'            => $missingItemsNote ? trim($missingItemsNote) : null,
                'by'                 => Auth::id(),
            ]);

            $this->markOwnerReceived($task, 'استلام جزئي (مع السماح بالبدء)');

            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? $task->assigned_to_employee?->user_id
                ?? null;

            if ($deptManagerId) {
                $this->setOwner(
                    $task,
                    'department_manager',
                    userId: $deptManagerId,
                    touchSent: false,
                    note: 'استلام جزئي للخامات — يمكن بدء التصنيع'
                );
            }
        });
    }

    public function materialsReceivedPartialHold(
        ProductionTask $task,
        ?string $note,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $note, $missingItemsNote) {
            $this->openFollowupMaterialsRequest($task, $missingItemsNote);

            $task->update(['status' => 'on_hold']);

            $this->log($task, 'materials_received_partial', [
                'allow_start' => false,
                'note'        => $note ? trim($note) : null,
                'missing'     => $missingItemsNote ? trim($missingItemsNote) : null,
                'by'          => Auth::id(),
            ]);

            $this->setOwner($task, 'purchasing_manager', userId: $this->resolvePurchasingManagerUserId($task), touchSent: true, note: 'نواقص خامات — انتظار توريد تكميلي');
        });
    }

    public function materialsReceivedIssue(ProductionTask $task, ?string $note, ?string $issueDetails = null): void
    {
        DB::transaction(function () use ($task, $note, $issueDetails) {
            $mr = $task->materialRequests()
                ->whereIn('status', ['fulfilled','supplying','approved'])
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if ($mr) {
                $mr->update(['status' => 'issue_reported']);
            }

            $task->update(['status' => 'on_hold']);
            $this->setOwner($task, 'purchasing_manager', userId: $this->resolvePurchasingManagerUserId($task), touchSent: true, note: 'مشكلة في الخامات — انتظار المعالجة');

            $this->log($task, 'materials_received_issue', [
                'mr_id'  => $mr?->id,
                'note'   => $note ? trim($note) : null,
                'issues' => $issueDetails ? trim($issueDetails) : null,
                'by'     => Auth::id(),
            ]);
        });
    }


    protected function openFollowupMaterialsRequest(ProductionTask $task, ?string $missingItemsNote): void
    {
        $prevMr = $task->materialRequests()->latest()->first();

        $mr = \App\Models\MaterialRequest::create([
            'task_id'       => $task->id,
            'department_id' => $task->department_id,
            'requested_by'  => Auth::id(),
            'requested_at'  => now(),
            'status'        => 'requested',
            'note'          => 'طلب تكميلي لبنود ناقصة.' . ($missingItemsNote ? ("\n\nالبنود الناقصة:\n" . trim($missingItemsNote)) : ''),
            'po_file'       => $prevMr?->po_file,
            'parent_id'     => $prevMr?->id, // يعتمد على الحقل الذي أضفته يدويًا
        ]);

        $this->log($task, 'materials_followup_opened', [
            'by'    => Auth::id(),
            'mr_id' => $mr->id,
        ]);
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

    protected function ensureOwner(
        \App\Models\ProductionTask $task,
        ?string $role,
        ?int $userId,
        bool $touchSent = false,
        ?string $note = null,
        bool $force = false
    ): bool {
        // Normalize values for reliable comparison
        $currRole = $task->current_owner_role ?? null;
        $currUser = isset($task->current_owner_user_id) ? (int)$task->current_owner_user_id : null;
        $newUser  = $userId !== null ? (int)$userId : null;

        $sameRole = ($currRole === $role);
        $sameUser = ($currUser === $newUser);

        if ($sameRole && $sameUser && ! $force) {
            if ($note) {
                $this->log($task, 'owner_note_updated', [
                    'role'    => $role,
                    'user_id' => $userId,
                    'note'    => $note,
                ]);
            }
            return false;
        }

        $task->current_owner_role    = $role;
        $task->current_owner_user_id = $userId;

        if ($touchSent) {

            if (blank($task->sent_to_owner_at) || ! ($sameRole && $sameUser)) {
                $task->sent_to_owner_at = now();
            }
            // reset received flag on re-send
            $task->received_by_owner_at = null;
        }

        $task->save();

        // Log the ownership change (only once per actual change)
        $this->log($task, 'ownership_changed', [
            'role'    => $role,
            'user_id' => $userId,
            'note'    => $note,
        ]);

        return true;
    }

    public function setOwner(
        \App\Models\ProductionTask $task,
        ?string $role,
        ?int $userId,
        bool $touchSent = false,
        ?string $note = null,
        bool $force = false
    ): bool {
        return $this->ensureOwner($task, $role, $userId, $touchSent, $note, $force);
    }

    protected function markOwnerReceived(ProductionTask $task, ?string $reason = null): void
    {
        $task->received_by_owner_at = now();
        $task->save();

        $this->log($task, 'owner_received', [
            'reason' => $reason,
        ]);
    }

    public function resolveDeptManagerUserId(ProductionTask $task): ?int
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
