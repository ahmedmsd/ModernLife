<?php

namespace App\Services\Tasks\Workflow;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ManufacturingWorkflowService
{
    use HasTaskWorkflowHelpers;

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

    public function manufacturingAcknowledgeRework(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام العمل المُعاد في التصنيع' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'manufacturing_ack_rework', [
                'note' => $note,
            ]);
        });
    }
}
