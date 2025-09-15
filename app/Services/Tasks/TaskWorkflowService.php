<?php

namespace App\Services\Tasks;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Models\MaterialRequest;
use App\Models\Employee;
use App\Services\ProductionRequestWorkflow;
use App\Services\Notifications\TaskNotifier;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TaskWorkflowService
{
    public function __construct(
        protected TaskNotifier $notifier,
    ) {}

    /* =========================================================================
     |                           إجراءات التدفق الرئيسية
     |=========================================================================*/

    /** إسناد المهمة لمدير القسم وتعيين المالك */
    public function assignToDeptManager(ProductionTask $task, int $employeeId, string $dueDate): void
    {
        DB::transaction(function () use ($task, $employeeId, $dueDate) {
            $emp = Employee::with('user:id')->findOrFail($employeeId);
            $ownerUserId = $emp?->user?->id;

            $fromStatus = $task->status;

            $task->forceFill([
                'assigned_to_employee_id' => $employeeId,
                'status'                  => 'pending',
                'assigned_at'             => now(),
                'due_date'                => $dueDate,
            ])->save();

            // تحويل الملكية لمدير القسم + تنبيه
            $this->setOwner($task, 'department_manager', $ownerUserId, touchSent: true, note: 'إسناد من المصنع');

            // لوج
            $this->log($task, 'assigned_changed', [
                'from' => $task->getOriginal('assigned_to_employee_id'),
                'to'   => $employeeId,
            ]);
            if ($fromStatus !== 'pending') {
                $this->log($task, 'status_changed', ['from' => $fromStatus, 'to' => 'pending']);
            }

            // بلّغ المُسنِد (المستخدم الحالي) مع رابط المهمة
            $this->notifier->notifyActor('تم إسناد المهمة لمدير القسم', "رقم المهمة #{$task->id}", $task);
        });
    }

    /** مدير القسم يؤكد استلام المهمة */
    public function deptAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $from = $task->status;

            $task->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);

            $this->markOwnerReceived($task, $note ?: 'تأكيد استلام المهمة (مدير القسم)');
            $this->log($task, 'status_changed', ['from' => $from, 'to' => 'received']);

            $this->notifier->notifyActor('تم تأكيد استلام المهمة', "رقم المهمة #{$task->id}", $task);
        });
    }

    /** مدير القسم يطلب خامات (يرسل للمشتريات) */
    public function requestMaterials(ProductionTask $task, string $note, string $poFilePath): void
    {
        DB::transaction(function () use ($task, $note, $poFilePath) {
            $from = $task->status;

            MaterialRequest::create([
                'task_id'       => $task->id,
                'department_id' => $task->department_id,
                'requested_by'  => Auth::id(),
                'requested_at'  => now(),
                'status'        => 'requested',
                'note'          => $note,
                'po_file'       => $poFilePath,
            ]);

            $task->update(['status' => 'materials_wait']);

            // تحويل الملكية للمشتريات + إشعار
            $this->setOwner($task, 'purchasing_manager', userId: null, touchSent: true, note: 'طلب خامات');

            $this->log($task, 'status_changed', ['from' => $from, 'to' => 'materials_wait']);

            $this->notifier->notifyActor('تم إرسال طلب الخامات', "المهمة #{$task->id} بانتظار المشتريات", $task);
        });
    }

    /** المشتريات تؤكد استلام الطلب وتحدد موعد توريد */
    public function purchasingReceive(ProductionTask $task, array $data): void
    {
        DB::transaction(function () use ($task, $data) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            $mr->update([
                'po_number'            => $data['po_number'] ?? $mr->po_number,
                'estimated_cost'       => $data['estimated_cost'] ?? $mr->estimated_cost,
                'expected_delivery_at' => $data['expected_delivery_at'],
                'note'                 => trim(($mr->note ? $mr->note . "\n" : '') . ($data['note'] ?? '')),
                'status'               => 'approved',
            ]);

            $task->update(['status' => 'materials_prep']);

            $this->markOwnerReceived($task, 'تأكيد استلام طلب الخامات (المشتريات)');
            $this->log($task, 'purchasing_ack', ['by' => Auth::id()]);
            $this->log($task, 'status_changed', ['from' => 'materials_wait', 'to' => 'materials_prep']);

            $this->notifier->notifyActor('تم تسجيل استلام طلب الخامات', "المهمة #{$task->id} في التحضير للتوريد", $task);
        });
    }

    /** المشتريات تؤكد توريد/توفّر الخامات وتسلمها للقسم */
    public function materialsProvided(ProductionTask $task, float $actualCost, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $actualCost, $note) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            $mr->update([
                'actual_cost' => $actualCost,
                'note'        => trim(($mr->note ? $mr->note . "\n" : '') . ($note ?? '')),
                'provided_by' => Auth::id(),
                'provided_at' => now(),
                'status'      => 'fulfilled',
            ]);

            $task->update(['status' => 'materials_done']);

            // سلّم لمدير القسم (المستخدم في نفس القسم إن وُجد)
            $deptManager = Employee::whereHas('roles', fn ($q) => $q->where('name', 'department_manager'))
                ->where('department_id', $task->department_id)
                ->first();

            $this->setOwner($task, 'department_manager', $deptManager?->user_id, touchSent: true, note: 'توفير الخامات');

            $this->log($task, 'status_changed', ['from' => 'materials_prep', 'to' => 'materials_done']);

            $this->notifier->notifyActor('تم تأكيد توفر الخامات', "المهمة #{$task->id} جاهزة لاستلام القسم", $task);
        });
    }

    /** مدير القسم: تأكيد استلام الخامات + تحديد مواعيد، ثم تحويل للتصنيع */
    public function materialsReceivedOk(ProductionTask $task, string $start, string $end, string $install): void
    {
        DB::transaction(function () use ($task, $start, $end, $install) {
            $startAt   = Carbon::parse($start);
            $endAt     = Carbon::parse($end);
            $installAt = Carbon::parse($install);

            $task->update([
                'status'             => 'waiting_production',
                'planned_start_at'   => $startAt,
                'planned_end_at'     => $endAt,
                'planned_install_at' => $installAt,
            ]);

            $this->markOwnerReceived($task, 'استلام الخامات وتحديد المواعيد');

            // تحويل للتصنيع
            $this->setOwner($task, 'factory_manager', userId: null, touchSent: true, note: 'جاهز للتصنيع');

            $this->log($task, 'planning_set', [
                'planned_start_at'   => $startAt->toDateString(),
                'planned_end_at'     => $endAt->toDateString(),
                'planned_install_at' => $installAt->toDateString(),
                'by'                 => Auth::id(),
            ]);
            $this->log($task, 'status_changed', ['from' => 'materials_done', 'to' => 'waiting_production']);

            $this->notifier->notifyActor('تم تحديد المواعيد وإرسال للتصنيع', "المهمة #{$task->id}", $task);
        });
    }

    /** التصنيع يؤكد الاستلام قبل البدء */
    public function productionAcknowledge(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->markOwnerReceived($task, 'تأكيد استلام التصنيع');
            $this->log($task, 'prod_ack_initial', ['by' => Auth::id()]);
            $this->notifier->notifyActor('تم تأكيد استلام التصنيع', "المهمة #{$task->id}", $task);
        });
    }

    /** بدء التصنيع */
    public function startProduction(ProductionTask $task, string $startedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $task->update(['status' => 'in_progress']);

            $this->log($task, 'manufacturing_started', [
                'by'         => Auth::id(),
                'started_at' => $startedAt,
                'note'       => trim((string) ($note ?? '')),
            ]);

            $this->notifier->notifyActor('بدأ التصنيع', "المهمة #{$task->id}", $task);
        });
    }

    /** إنهاء التصنيع وإرساله للجودة (handoff) */
    public function finishManufacturingToQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->log($task, 'manufacturing_sent_to_qa', ['by' => Auth::id(), 'note' => trim((string) ($note ?? ''))]);

            $task->update([
                'status'                => 'under_review',
                'current_owner_role'    => 'quality_manager',
                'current_owner_user_id' => null,
                'sent_to_owner_at'      => now(),
                'received_by_owner_at'  => null,
            ]);

            // handoff للجودة (مع رابط)
            $this->notifier->handoffToOwner(
                $task,
                toRole: 'quality_manager',
                toUserId: null,
                title: 'مهمة جديدة بانتظار فحص الجودة',
                body: $this->notifier->defaultHandoffBody($note)
            );

            $this->notifier->notifyActor('تم إرسال التصنيع للجودة', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تؤكد الاستلام بعد التصنيع */
    public function qaAcknowledgeManufacturing(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->log($task, 'qa_ack_manufacturing', ['by' => Auth::id()]);
            $task->update(['received_by_owner_at' => now()]);
            $this->notifier->notifyActor('تم تأكيد استلام الجودة (بعد التصنيع)', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تعتمد بعد التصنيع وتحوّل للتركيب */
    public function approveManufacturingQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->log($task, 'qa_approved_manufacturing', ['by' => Auth::id(), 'note' => trim((string) ($note ?? ''))]);

            // handoff للتركيب
            $this->log($task, 'sent_to_install', ['by' => Auth::id()]);
            $task->update([
                'status'                 => 'approved',
                'current_owner_role'     => 'installation_manager',
                'current_owner_user_id'  => null,
                'sent_to_owner_at'       => now(),
                'received_by_owner_at'   => null,
            ]);

            $this->notifier->handoffToOwner(
                $task,
                toRole: 'installation_manager',
                toUserId: null,
                title: 'مهمة جاهزة للتركيب',
                body: $this->notifier->defaultHandoffBody($note)
            );

            $this->notifier->notifyActor('تم اعتماد الجودة وتحويل المهمة للتركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة ترفض بعد التصنيع وتعيد للتصنيع */
    public function rejectManufacturingQA(ProductionTask $task, string $reason): void
    {
        DB::transaction(function () use ($task, $reason) {
            $this->log($task, 'qa_rejected_manufacturing', ['by' => Auth::id(), 'reason' => trim($reason)]);
            $this->log($task, 'sent_back_to_manufacturing', ['by' => Auth::id()]);

            $task->update([
                'status'                 => 'rework',
                'current_owner_role'     => 'factory_manager',
                'current_owner_user_id'  => null,
                'sent_to_owner_at'       => now(),
                'received_by_owner_at'   => null,
            ]);

            $this->notifier->handoffToOwner(
                $task,
                toRole: 'factory_manager',
                toUserId: null,
                title: 'مهمة مُعادة للتصنيع (رفض جودة)',
                body: $this->notifier->defaultHandoffBody("سبب الرفض: {$reason}")
            );

            $this->notifier->notifyActor('تم رفض الجودة وإرجاع المهمة للتصنيع', "المهمة #{$task->id}", $task);
        });
    }

    /** التركيب يؤكد الاستلام */
    public function installationAcknowledge(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->log($task, 'install_acknowledged', ['by' => Auth::id()]);
            $task->update(['received_by_owner_at' => now()]);
            $this->notifier->notifyActor('تم تأكيد استلام قسم التركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** بدء التركيب */
    public function startInstallation(ProductionTask $task, string $startedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $this->log($task, 'installation_started', [
                'by'         => Auth::id(),
                'started_at' => $startedAt,
                'note'       => trim((string) ($note ?? '')),
            ]);

            $task->update(['status' => 'in_progress']);
            $this->notifier->notifyActor('تم بدء التركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** إنهاء التركيب وإرساله للجودة (handoff) */
    public function finishInstallationToQA(ProductionTask $task, string $finishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $finishedAt, $note) {
            $this->log($task, 'installation_sent_to_qa', [
                'by'          => Auth::id(),
                'finished_at' => $finishedAt,
                'note'        => trim((string) ($note ?? '')),
            ]);

            $task->update([
                'status'                => 'under_review',
                'current_owner_role'    => 'quality_manager',
                'current_owner_user_id' => null,
                'sent_to_owner_at'      => now(),
                'received_by_owner_at'  => null,
            ]);

            // handoff للجودة
            $this->notifier->handoffToOwner(
                $task,
                toRole: 'quality_manager',
                toUserId: null,
                title: 'مهمة تركيب بانتظار فحص الجودة',
                body: $this->notifier->defaultHandoffBody($note)
            );

            $this->notifier->notifyActor('تم إرسال التركيب للجودة', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تؤكد الاستلام بعد التركيب */
    public function qaAcknowledgeInstallation(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->log($task, 'qa_ack_installation', ['by' => Auth::id()]);
            $task->update(['received_by_owner_at' => now()]);
            $this->notifier->notifyActor('تم تأكيد استلام الجودة (بعد التركيب)', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تعتمد بعد التركيب (تغلق مرحلة التركيب) */
    public function approveInstallationQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->log($task, 'qa_approved_installation', ['by' => Auth::id(), 'note' => trim((string) ($note ?? ''))]);

            $task->update([
                'status'                => 'approved',
                'current_owner_role'    => null,
                'current_owner_user_id' => null,
                'received_by_owner_at'  => now(),
            ]);

            $this->notifier->notifyActor('تم اعتماد الجودة لما بعد التركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة ترفض بعد التركيب وتعيد للتركيب */
    public function rejectInstallationQA(ProductionTask $task, string $reason): void
    {
        DB::transaction(function () use ($task, $reason) {
            $this->log($task, 'qa_rejected_installation', ['by' => Auth::id(), 'reason' => trim($reason)]);
            $this->log($task, 'sent_back_to_install', ['by' => Auth::id()]);

            $task->update([
                'status'                => 'rework',
                'current_owner_role'    => 'installation_manager',
                'current_owner_user_id' => null,
                'sent_to_owner_at'      => now(),
                'received_by_owner_at'  => null,
            ]);

            $this->notifier->handoffToOwner(
                $task,
                toRole: 'installation_manager',
                toUserId: null,
                title: 'مهمة مُعادة للتركيب (رفض جودة)',
                body: $this->notifier->defaultHandoffBody("سبب الرفض: {$reason}")
            );

            $this->notifier->notifyActor('تم رفض الجودة وإرجاع المهمة للتركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** التركيب يؤكد استلامه بعد الرفض */
    public function installationAcknowledgeRework(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->log($task, 'install_ack_rework', ['by' => Auth::id()]);
            $task->update(['received_by_owner_at' => now()]);
            $this->notifier->notifyActor('تم تأكيد استلام التركيب (إعادة عمل)', "المهمة #{$task->id}", $task);
        });
    }

    /** رفع سند استلام العميل وإغلاق المهمة */
    public function uploadClientReceiptAndComplete(ProductionTask $task, string $path): void
    {
        DB::transaction(function () use ($task, $path) {
            $task->update([
                'client_receipt' => $path,
                'status'         => 'completed',
                'completed_at'   => now(),
            ]);

            $this->log($task, 'client_receipt_uploaded', ['by' => Auth::id(), 'path' => $path]);

            $this->notifier->notifyActor('اكتملت المهمة', "المهمة #{$task->id} أُغلقت بنجاح", $task);

            $this->finalizeIfProjectDone($task);
        });
    }

    /* =========================================================================
     |                              أدوات داخلية
     |=========================================================================*/

    /** تغيير المالك (الدور/المستخدم) + إشعار handoff */
    public function setOwner(
        ProductionTask $task,
        ?string $role,
        ?int $userId = null,
        bool $touchSent = true,
        ?string $note = null
    ): void {
        $payload = [
            'current_owner_role'    => $role,
            'current_owner_user_id' => $userId,
        ];

        if ($touchSent) {
            $payload['sent_to_owner_at']     = now();
            $payload['received_by_owner_at'] = null;
        }

        $task->forceFill($payload)->save();

        $this->log($task, 'owner_changed', [
            'owner_role'     => $role,
            'owner_user_id'  => $userId,
            'note'           => $note,
        ]);

        // handoff إشعار للمالك الجديد مع زر "عرض المهمة"
        $this->notifier->handoffToOwner(
            $task,
            toRole: $role,
            toUserId: $userId,
            title: 'لديك مهمة بانتظار الإجراء',
            body: $this->notifier->defaultHandoffBody($note)
        );
    }

    /** تسجيل استلام المالك الحالي */
    public function markOwnerReceived(ProductionTask $task, ?string $note = null): void
    {
        $task->update(['received_by_owner_at' => now()]);

        $this->log($task, 'owner_received', [
            'owner_role'     => $task->current_owner_role,
            'owner_user_id'  => $task->current_owner_user_id,
            'note'           => $note,
        ]);
    }

    /** هل يوجد طلب خامات مفتوح (requested/approved بدون provided_at) */
    public function hasOpenMaterialsRequest(ProductionTask $task): bool
    {
        return $task->materialRequests()
            ->whereNull('provided_at')
            ->whereIn('status', ['requested', 'approved'])
            ->exists();
    }

    /** إغلاق المشروع/الطلب إذا اكتملت جميع المهام */
    public function finalizeIfProjectDone(ProductionTask $task): void
    {
        $proj = $task->project;
        if (! $proj) return;

        $hasOpen = $proj->tasks()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if (! $hasOpen) {
            if (Schema::hasColumn('projects', 'status')) {
                $proj->update(['status' => 'completed']);
            }

            try {
                if (method_exists($proj, 'productionRequest') && $proj->productionRequest) {
                    app(ProductionRequestWorkflow::class)->finalizeRequestAfterProjectDone($proj->productionRequest);
                }
            } catch (\Throwable) {
                // تجاهل أي خطأ خارجي
            }
        }
    }

    /** إنشاء سجل حدث (مع happened_at) وتحديث علاقة الـlogs لو كانت مُحمّلة */
    public function log(ProductionTask $task, string $type, array $data = []): void
    {
        TaskLog::create([
            'task_id'     => $task->getKey(),
            'type'        => $type,
            'data'        => $data,
            'happened_at' => now(),
        ]);

        if ($task->relationLoaded('logs')) {
            $task->unsetRelation('logs');
            $task->load('logs');
        }
    }
}
