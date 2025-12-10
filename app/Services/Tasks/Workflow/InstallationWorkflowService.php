<?php

namespace App\Services\Tasks\Workflow;

use App\Models\ProductionTask;
use App\Services\Tasks\Workflow\Concerns\HasTaskWorkflowHelpers;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class InstallationWorkflowService
{
    use HasTaskWorkflowHelpers;

    public function installationAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام مسؤول التركيب للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'install_acknowledged', [
                'note' => $note,
            ]);
        });
    }

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

    public function installationAcknowledgeRework(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام العمل المُعاد في التركيب' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'install_ack_rework', [
                'note' => $note,
            ]);
        });
    }
}
