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
            $emp        = Employee::with('user:id')->findOrFail($employeeId);
            $ownerUserId= $emp?->user?->id;

            $fromStatus = $task->status;

            // ضبط الإسناد والمواعيد العامة
            $task->forceFill([
                'assigned_to_employee_id' => $employeeId,
                'status'                  => 'pending',
                'assigned_at'             => now(),
                'due_date'                => $dueDate,
            ])->save();

            // تحويل الملكية لمدير القسم + إشعار (يسجل sent_to_department تلقائيًا إن تغيّر الدور)
            $this->setOwner($task, 'department_manager', $ownerUserId, touchSent: true, note: 'إسناد من المصنع');

            // لوج الإسناد + تغيير الحالة إن لزم
            $this->log($task, 'assigned_changed', [
                'from' => $task->getOriginal('assigned_to_employee_id'),
                'to'   => $employeeId,
            ]);
            if ($fromStatus !== 'pending') {
                $this->log($task, 'status_changed', ['from' => $fromStatus, 'to' => 'pending']);
            }

            // تنبيه المُسنِد (المستخدم الحالي)
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

            // توثيق استلام المالك الحالي
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

            // إنشاء طلب خامات جديد مرتبط بالمهمة
            MaterialRequest::create([
                'task_id'       => $task->id,
                'department_id' => $task->department_id,
                'requested_by'  => Auth::id(),
                'requested_at'  => now(),
                'status'        => 'requested',
                'note'          => $note,
                'po_file'       => $poFilePath,
            ]);

            // نقل حالة المهمة وبعثها للمشتريات
            $task->update(['status' => 'materials_wait']);

            // تحويل الملكية إلى المشتريات (يسجل sent_to_purchasing تلقائيًا)
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
                'po_number'            => $data['po_number']            ?? $mr->po_number,
                'estimated_cost'       => $data['estimated_cost']       ?? $mr->estimated_cost,
                'expected_delivery_at' => $data['expected_delivery_at'],
                'note'                 => trim(($mr->note ? $mr->note . "\n" : '') . ($data['note'] ?? '')),
                'status'               => 'approved',
                'approved_at'          => now(),            // ↑ تتبع واضح للاعتماد
                'approved_by'          => Auth::id(),
            ]);

            $task->update(['status' => 'materials_prep']);

            // توثيق استلام المشتريات وتهيئة التوريد
            $this->markOwnerReceived($task, 'تأكيد استلام طلب الخامات (المشتريات)');
            $this->log($task, 'purchasing_ack', ['by' => Auth::id()]);
            $this->log($task, 'status_changed', ['from' => 'materials_wait', 'to' => 'materials_prep']);

            $this->notifier->notifyActor('تم تسجيل استلام طلب الخامات', "المهمة #{$task->id} في التحضير للتوريد", $task);
        });
    }

    /**
     * المشتريات تؤكد توريد/توفّر الخامات وتسلمها للقسم
     *
     * @param ProductionTask $task
     * @param float          $actualCost  التكلفة الفعلية للتوريد
     * @param string|null    $note        ملاحظة إضافية
     * @param array|null     $invoice     بيانات الفاتورة ['invoice_no','invoice_date','invoice_file'] (اختياري)
     */
    public function materialsProvided(ProductionTask $task, float $actualCost, ?string $note = null, ?array $invoice = null): void
    {
        DB::transaction(function () use ($task, $actualCost, $note, $invoice) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            // تجهيز قيم الفاتورة الاختيارية
            $invoiceNo   = $invoice['invoice_no']   ?? null;
            $invoiceDate = isset($invoice['invoice_date']) && $invoice['invoice_date']
                ? Carbon::parse($invoice['invoice_date'])
                : null;
            $invoiceFile = $invoice['invoice_file'] ?? null;

            // تحديث طلب الخامات بالقيم الفعلية + الفاتورة (إن وجدت)
            $mr->update([
                'actual_cost'  => $actualCost,
                'invoice_no'   => $invoiceNo ?? $mr->invoice_no,
                'invoice_date' => $invoiceDate ?? $mr->invoice_date,
                'invoice_file' => $invoiceFile ?? $mr->invoice_file,
                'note'         => trim(($mr->note ? $mr->note . "\n" : '') . ($note ?? '')),
                'provided_by'  => Auth::id(),
                'provided_at'  => now(),
                'status'       => 'fulfilled',
            ]);

            // نقل حالة المهمة إلى "تم توفير الخامات"
            $task->update(['status' => 'materials_done']);

            // تعيين المالك: مدير القسم (لو لم يُعرف، نسقط لرئيس القسم أو المستخدم الحالي)
            $deptManager = Employee::whereHas('roles', fn ($q) => $q->where('name', 'department_manager'))
                ->where('department_id', $task->department_id)
                ->first();

            $this->setOwner(
                $task,
                'department_manager',
                $deptManager?->user_id,
                touchSent: true,
                note: 'توفير الخامات'
            );

            $this->log($task, 'status_changed', ['from' => 'materials_prep', 'to' => 'materials_done']);

            $this->notifier->notifyActor('تم تأكيد توفر الخامات', "المهمة #{$task->id} جاهزة لاستلام القسم", $task);
        });
    }

    /** استلام القسم للخامات (اختياري: تحديد مخطط) وتحويل المهمة لانتظار بدء التصنيع */
    public function materialsReceivedOk(ProductionTask $task, ?string $start = null, ?string $end = null, ?string $install = null): void
    {
        DB::transaction(function () use ($task, $start, $end, $install) {
            $fromStatus = $task->status;

            // بناء الحقول المحدثة (مخطط)
            $payload = ['status' => 'waiting_production'];
            if ($start)   { $payload['planned_start_at']   = Carbon::parse($start); }
            if ($end)     { $payload['planned_end_at']     = Carbon::parse($end); }
            if ($install) { $payload['planned_install_at'] = Carbon::parse($install); }

            $task->update($payload);

            // توثيق استلام القسم
            $this->markOwnerReceived($task, 'استلام الخامات — جاهز لبدء التصنيع');

            // الإبقاء على المالك مدير القسم (يسجل sent_to_department إن تغيّر من قبل)
            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? Auth::id();

            $this->setOwner(
                $task,
                'department_manager',
                userId: $deptManagerId,
                touchSent: true,
                note: 'جاهز لبدء التصنيع'
            );

            $this->log($task, 'status_changed', [
                'from' => $fromStatus ?: 'materials_done',
                'to'   => 'waiting_production',
            ]);

            $this->log($task, 'planning_hint_set', [
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'by'                 => Auth::id(),
            ]);

            $this->notifier->notifyActor(
                'تم استلام الخامات — المهمة بانتظار بدء التصنيع',
                "المهمة #{$task->id}",
                $task
            );
        });
    }

    /** بدء التصنيع (تاريخ فعلي فقط) */
    public function startProduction(ProductionTask $task, string $startedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $fromStatus = $task->status;
            $start      = Carbon::parse($startedAt);

            // حفظ البداية الفعلية (مع تحقّق أسماء الأعمدة الشائعة)
            $update = ['status' => 'in_progress'];
            if (Schema::hasColumn($task->getTable(), 'actual_start_at')) {
                $update['actual_start_at'] = $start;
            } elseif (Schema::hasColumn($task->getTable(), 'started_at')) {
                $update['started_at'] = $start;
            }
            $task->update($update);

            // تأكيد المالك مدير القسم (يسجل sent_to_department إن تغيّر)
            $deptManagerId = $task->department?->manager_user_id
                ?? $task->department?->head_user_id
                ?? Auth::id();

            $this->setOwner(
                $task,
                'department_manager',
                userId: $deptManagerId,
                touchSent: true,
                note: 'بدء التصنيع (تاريخ فعلي)'
            );

            // سجلات تتبع
            $this->log($task, 'manufacturing_started', [
                'by'         => Auth::id(),
                'started_at' => $start->toDateTimeString(),
                'note'       => trim((string) ($note ?? '')),
            ]);
            $this->log($task, 'status_changed', [
                'from' => $fromStatus ?: 'waiting_production',
                'to'   => 'in_progress',
            ]);

            $this->notifier->notifyActor('بدأ التصنيع (تاريخ فعلي)', "المهمة #{$task->id}", $task);
        });
    }

    /** إنهاء التصنيع وإرساله للجودة (handoff) */
    public function finishManufacturingAndSendToQA(ProductionTask $task, string $actualFinishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $actualFinishedAt, $note) {
            $fromStatus = $task->status;
            $end        = Carbon::parse($actualFinishedAt);

            // حفظ تاريخ الانتهاء الفعلي (مع التحقق من أسماء الأعمدة)
            $update = [];
            if (Schema::hasColumn($task->getTable(), 'actual_end_at')) {
                $update['actual_end_at'] = $end;
            } elseif (Schema::hasColumn($task->getTable(), 'finished_at')) {
                $update['finished_at'] = $end;
            }

            // نقل الحالة لمراجعة الجودة بعد التصنيع
            $update['status'] = 'under_review';
            $task->update($update);

            // تعيين الملكية للجودة (يسجل sent_to_quality تلقائيًا)
            $qaManagerId = $task->department?->quality_manager_user_id
                ?? $task->department?->qa_head_user_id
                ?? null;

            $this->setOwner(
                $task,
                'quality_manager',
                userId: $qaManagerId,
                touchSent: true,
                note: 'استلام للمراجعة بعد التصنيع'
            );

            // لوجات واضحة
            $this->log($task, 'manufacturing_finished', [
                'by'           => Auth::id(),
                'finished_at'  => $end->toDateTimeString(),
                'note'         => trim((string) ($note ?? '')),
            ]);
            $this->log($task, 'manufacturing_sent_to_qa', ['by' => Auth::id()]);

            if ($fromStatus !== 'under_review') {
                $this->log($task, 'status_changed', ['from' => $fromStatus, 'to' => 'under_review']);
            }

            $this->notifier->notifyActor('إنهاء التصنيع — تحويل للجودة', "المهمة #{$task->id}", $task);
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
            $this->log($task, 'qa_approved_manufacturing', [
                'by'   => Auth::id(),
                'note' => trim((string) ($note ?? '')),
            ]);

            // handoff للتركيب (يسجل sent_to_install داخل setOwner)
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

    /**
     * رفع سند استلام العميل وإغلاق المهمة + إغلاق المشروع/الطلب إن لا توجد مهام مفتوحة
     *
     * @param ProductionTask $task
     * @param string|null    $receiptPath       مسار السند (اختياري)
     * @param string|null    $actualFinishedAt  تاريخ الانتهاء الفعلي للمهمة (اختياري)
     * @param string|null    $note              ملاحظة (اختياري)
     */
    public function uploadClientReceiptAndComplete(ProductionTask $task, ?string $receiptPath, ?string $actualFinishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $receiptPath, $actualFinishedAt, $note) {
            $fromStatus = $task->status;

            // تحديث المهمة إلى "completed" مع حفظ السند والتاريخ الفعلي إن توفر
            $update = ['status' => 'completed'];

            if (!empty($receiptPath) && Schema::hasColumn($task->getTable(), 'client_receipt')) {
                $update['client_receipt'] = $receiptPath;
            }

            if (!empty($actualFinishedAt)) {
                $end = Carbon::parse($actualFinishedAt);
                if (Schema::hasColumn($task->getTable(), 'actual_end_at')) {
                    $update['actual_end_at'] = $end;
                } elseif (Schema::hasColumn($task->getTable(), 'finished_at')) {
                    $update['finished_at'] = $end;
                }
            }

            $task->update($update);

            // لوجات إنهاء المهمة + السند
            $this->log($task, 'client_receipt_uploaded', [
                'by'   => Auth::id(),
                'path' => $receiptPath,
            ]);

            if (!empty($actualFinishedAt)) {
                $this->log($task, 'task_completed', [
                    'by'          => Auth::id(),
                    'finished_at' => Carbon::parse($actualFinishedAt)->toDateTimeString(),
                    'note'        => trim((string) ($note ?? '')),
                ]);
            }

            if ($fromStatus !== 'completed') {
                $this->log($task, 'status_changed', [
                    'from' => $fromStatus,
                    'to'   => 'completed',
                ]);
            }

            // إقفال المشروع والطلب إن لم تبقَ مهام مفتوحة
            $finalStatuses = ['completed', 'cancelled', 'closed'];

            $project = $task->project()
                ->withCount(['tasks as open_tasks_count' => function ($q) use ($finalStatuses) {
                    $q->whereNotIn('status', $finalStatuses);
                }])
                ->first();

            if ($project && (int) $project->open_tasks_count === 0) {
                // إكمال المشروع
                $projUpdate = ['status' => 'completed'];
                if (Schema::hasColumn($project->getTable(), 'completed_at')) {
                    $projUpdate['completed_at'] = now();
                } elseif (Schema::hasColumn($project->getTable(), 'closed_at')) {
                    $projUpdate['closed_at'] = now();
                }
                $project->update($projUpdate);

                // لوج توثيقي لإكمال المشروع
                $this->log($task, 'project_completed', [
                    'project_id' => $project->id,
                    'by'         => Auth::id(),
                ]);

                // إقفال طلب الإنتاج التابع (إن وجد)
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

                $this->notifier->notifyActor(
                    'اكتمل المشروع والطلب — لا توجد مهام مفتوحة',
                    "المشروع #{$project->id}" . ($pr ? " — الطلب #{$pr->id}" : ''),
                    $task
                );
            }

            $this->notifier->notifyActor('تم إكمال المهمة واستلام العميل', "المهمة #{$task->id}", $task);
        });
    }

    /* =========================================================================
     |                              أدوات داخلية
     |=========================================================================*/

    /**
     * تغيير المالك (الدور/المستخدم) + إشعار handoff
     * - يسجّل ownership_changed (من/إلى الدور) و owner_changed (من/إلى المستخدم)
     * - يسجّل حدث إرسال صريح حسب الدور المستلِم (مرة واحدة عند تغيّر الدور):
     *   sent_to_showroom / sent_to_factory / sent_to_department / sent_to_purchasing / sent_to_quality / sent_to_install
     */
    public function setOwner(
        ProductionTask $task,
        ?string $role,
        ?int $userId = null,
        bool $touchSent = true,
        ?string $note = null
    ): void {
        // نحتفظ بالقيم القديمة قبل التحديث لتسجيل from/to
        $fromRole   = $task->current_owner_role;
        $fromUserId = $task->current_owner_user_id;

        $payload = [
            'current_owner_role'    => $role,
            'current_owner_user_id' => $userId,
        ];

        if ($touchSent) {
            $payload['sent_to_owner_at']     = now();
            $payload['received_by_owner_at'] = null;
        }

        $task->forceFill($payload)->save();

        // لوج صريح لإرسال المهمة حسب الدور المستلِم (للاستهلاك في الملخص الزمني)
        $explicitByRole = [
            'showroom_manager'   => 'sent_to_showroom',
            'factory_manager'    => 'sent_to_factory',
            'department_manager' => 'sent_to_department',
            'purchasing_manager' => 'sent_to_purchasing',
            'quality_manager'    => 'sent_to_quality',
            'installation_manager'=> 'sent_to_install',
        ];
        if ($fromRole !== $role && $role && isset($explicitByRole[$role])) {
            $this->log($task, $explicitByRole[$role], [
                'from_owner_role' => $fromRole,
                'to_owner_role'   => $role,
                'note'            => $note,
            ]);
        }

        // لوج تغيّر ملكية الدور
        if ($fromRole !== $role) {
            $this->log($task, 'ownership_changed', [
                'from' => ['owner_role' => $fromRole],
                'to'   => ['owner_role' => $role],
                'note' => $note,
            ]);
        }

        // لوج تغيّر المالك (المستخدم)
        if ($fromUserId !== $userId) {
            $this->log($task, 'owner_changed', [
                'from_user_id' => $fromUserId,
                'to_user_id'   => $userId,
                'owner_role'   => $role,
                'note'         => $note,
            ]);
        }

        // handoff إشعار للمالك الجديد مع زر "عرض المهمة"
        $this->notifier->handoffToOwner(
            $task,
            toRole: $role,
            toUserId: $userId,
            title: 'لديك مهمة بانتظار الإجراء',
            body: $this->notifier->defaultHandoffBody($note)
        );
    }

    /** تسجيل استلام المالك الحالي (يوثّق وصول المهمة فعليًا لصاحبها) */
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
        if (!$proj) return;

        $hasOpen = $proj->tasks()
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if (!$hasOpen) {
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

    /** إنشاء سجل حدث (مع happened_at) وتحديث علاقة الـ logs لو كانت مُحمّلة */
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
