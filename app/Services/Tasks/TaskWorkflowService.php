<?php

namespace App\Services\Tasks;

use App\Enums\TaskStatus;
use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Models\MaterialRequest;
use App\Models\Employee;
use App\Services\ProductionRequestWorkflow;
use App\Services\Notifications\TaskNotifier;
use Illuminate\Support\Arr;
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
            $emp         = Employee::with('user:id')->findOrFail($employeeId);
            $ownerUserId = $emp?->user?->id;

            $task->forceFill([
                'assigned_to_employee_id' => $employeeId,
                'status'                  => 'pending',
                'assigned_at'             => now(),
                'due_date'                => $dueDate,
            ])->save();

            // تعيين المالك (لا نسجّل لوج هنا؛ الـ Observer سيتولى ownership_changed + sent_to_department)
            $this->setOwner($task, 'department_manager', $ownerUserId, touchSent: true, note: 'إسناد من المصنع');

            // تنبيه المُسنِد
            $this->notifier->notifyActor('تم إسناد المهمة لمدير القسم', "رقم المهمة #{$task->id}", $task);
        });
    }

    /** مدير القسم يؤكد استلام المهمة */
    public function deptAcknowledge(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->update([
                'status'      => 'received',
                'received_at' => now(),
            ]);

            $this->markOwnerReceived($task, $note ?: 'تأكيد استلام المهمة (مدير القسم)');

            $this->notifier->notifyActor('تم تأكيد استلام المهمة', "رقم المهمة #{$task->id}", $task);
        });
    }

    /** مدير القسم يطلب خامات (يرسل للمشتريات) */
    public function requestMaterials(ProductionTask $task, string $note, string $poFilePath): void
    {
        DB::transaction(function () use ($task, $note, $poFilePath) {
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

            // تحويل الملكية للمشتريات (الـ Observer سيكتب ownership_changed + sent_to_purchasing)
            $this->setOwner($task, 'purchasing_manager', userId: null, touchSent: true, note: 'طلب خامات');

            $this->log($task, 'purchasing_ack_hint', ['by' => Auth::id(), 'note' => 'طلب خامات مرفوع']); // دوميني خفيف اختياري
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
                'approved_at'          => now(),
                'approved_by'          => Auth::id(),
            ]);

            $task->update(['status' => 'materials_prep']);

            // لوج دوميني (لا نسجّل status_changed)
            $this->log($task, 'purchasing_ack', ['by' => Auth::id()]);

            $this->notifier->notifyActor('تم تسجيل استلام طلب الخامات', "المهمة #{$task->id} في التحضير للتوريد", $task);
        });
    }

    /**
     * المشتريات تؤكد توريد/توفّر الخامات وتسلمها للقسم
     */
    public function materialsProvided(ProductionTask $task, float $actualCost, ?string $note = null, ?array $invoice = null): void
    {
        DB::transaction(function () use ($task, $actualCost, $note, $invoice) {
            $mr = $task->materialRequests()->whereNull('provided_at')->latest()->firstOrFail();

            $invoiceNo   = $invoice['invoice_no']   ?? null;
            $invoiceDate = isset($invoice['invoice_date']) && $invoice['invoice_date']
                ? Carbon::parse($invoice['invoice_date'])
                : null;
            $invoiceFile = $invoice['invoice_file'] ?? null;

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

            $task->update(['status' => 'materials_done']);

            // تعيين المالك: مدير القسم (Observer سيسجّل ownership_changed + sent_to_department)
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

            // لوج دوميني فقط
            $this->log($task, 'materials_provided_note', [
                'actual_cost' => $actualCost,
                'note'        => $note ? trim($note) : null,
            ]);

            $this->notifier->notifyActor('تم تأكيد توفر الخامات', "المهمة #{$task->id} جاهزة لاستلام القسم", $task);
        });
    }

    /** استلام القسم للخامات وتحويل المهمة لانتظار بدء التصنيع */
    public function materialsReceivedOk(
        ProductionTask $task,
        ?string $start = null,
        ?string $end = null,
        ?string $install = null,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $start, $end, $install, $note) {
            // تحديث المهمة
            $payload = ['status' => 'waiting_production'];
            if ($start)   { $payload['planned_start_at']   = Carbon::parse($start); }
            if ($end)     { $payload['planned_end_at']     = Carbon::parse($end); }
            if ($install) { $payload['planned_install_at'] = Carbon::parse($install); }
            $task->update($payload);

            // لوج استلام واضح لهذه الخطوة + الملاحظة صريحة
            $this->log($task, 'materials_received_ok', [
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
                'by'                 => Auth::id(),
            ]);

            // (اختياري) احتفظ بـ ownership_received بدون إلصاق الملاحظة بالنص:
            $this->markOwnerReceived($task, 'استلام الخامات — جاهز لبدء التصنيع');

            // إعادة ضبط الملكية للقسم
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

            // (اختياري) أبقِ planning_hint_set لو تحب الفصل بين “تلميح التخطيط” و“تثبيت الاستلام”
            $this->log($task, 'planning_hint_set', [
                'planned_start_at'   => optional($task->planned_start_at)->toDateTimeString(),
                'planned_end_at'     => optional($task->planned_end_at)->toDateTimeString(),
                'planned_install_at' => optional($task->planned_install_at)->toDateTimeString(),
                'note'               => $note ? trim($note) : null,
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
            $start = Carbon::parse($startedAt);

            $update = ['status' => 'in_progress'];
            if (Schema::hasColumn($task->getTable(), 'actual_start_at')) {
                $update['actual_start_at'] = $start;
            } elseif (Schema::hasColumn($task->getTable(), 'started_at')) {
                $update['started_at'] = $start;
            }
            $task->update($update);

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

            $this->log($task, 'manufacturing_started', [
                'by'         => Auth::id(),
                'started_at' => $start->toDateTimeString(),
                'note'       => trim((string) ($note ?? '')),
            ]);

            $this->notifier->notifyActor('بدأ التصنيع (تاريخ فعلي)', "المهمة #{$task->id}", $task);
        });
    }

    public function finishManufacturingAndSendToQA(ProductionTask $task, string $actualFinishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $actualFinishedAt, $note) {
            $end = Carbon::parse($actualFinishedAt);

            $update = ['status' => 'under_review'];
            if (Schema::hasColumn($task->getTable(), 'actual_end_at')) {
                $update['actual_end_at'] = $end;
            } elseif (Schema::hasColumn($task->getTable(), 'finished_at')) {
                $update['finished_at'] = $end;
            }
            $task->update($update);

            // تعيين الملكية للجودة (Observer سيسجّل ownership_changed + sent_to_quality)
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

            $this->log($task, 'manufacturing_sent_to_qa', [
                'by'           => Auth::id(),
                'finished_at'  => $end->toDateTimeString(),
                'note'         => trim((string) ($note ?? '')),
            ]);

            // لا نسجل manufacturing_sent_to_qa هنا — الـ Observer سيولّد sent_to_quality تلقائيًا
            $this->notifier->notifyActor('إنهاء التصنيع — تحويل للجودة', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تؤكد الاستلام بعد التصنيع */
    public function qaAcknowledgeManufacturing(ProductionTask $task): void
    {
        DB::transaction(function () use ($task) {
            $this->markOwnerReceived($task, 'تأكيد استلام الجودة (بعد التصنيع)');

            $this->log($task, 'qa_ack_manufacturing', [
                'by'   => Auth::id(),
                'role' => 'quality_manager',
            ]);

            $this->notifier->notifyActor(
                'تم تأكيد استلام الجودة (بعد التصنيع)',
                "المهمة #{$task->id}",
                $task
            );
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

            // handoff للتركيب (Observer سيسجّل ownership_changed + sent_to_install)
            $task->update([
                'status'                 => 'approved',
                'current_owner_role'     => 'installation_manager',
                'current_owner_user_id'  => null,
                'sent_to_owner_at'       => now(),
                'received_by_owner_at'   => null,
            ]);

            // لا نسجل sent_to_install هنا — سيصدر من الـ Observer
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
            // أحداث دومينية فقط
            $this->log($task, 'qa_rejected_manufacturing', ['by' => Auth::id(), 'reason' => trim($reason)]);
            $this->log($task, 'sent_back_to_manufacturing', ['by' => Auth::id()]);

            $task->update([
                'status'                 => 'rework',
                'current_owner_role'     => 'department_manager',
                'current_owner_user_id'  => null,
                'sent_to_owner_at'       => now(),
                'received_by_owner_at'   => null,
            ]);

            $this->notifier->handoffToOwner(
                $task,
                toRole: 'department_manager',
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
            $task->update(['received_by_owner_at' => now()]);
            $this->markOwnerReceived($task, 'تأكيد استلام التركيب)');

            $this->log($task, 'install_acknowledged', [
                'by'   => Auth::id(),
                'role' => 'installation_manager',
            ]);

            $this->notifier->notifyActor(
                'تم تأكيد استلام التركيب',
                "المهمة #{$task->id}",
                $task
            );
        });

    }

    /** بدء التركيب */
    public function startInstallation(ProductionTask $task, string $startedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $startedAt, $note) {
            $task->update(['status' => 'in_progress']);

            $this->log($task, 'installation_started', [
                'by'         => Auth::id(),
                'started_at' => $startedAt,
                'note'       => trim((string) ($note ?? '')),
            ]);

            $this->notifier->notifyActor('تم بدء التركيب', "المهمة #{$task->id}", $task);
        });
    }

    /** إنهاء التركيب وإرساله للجودة */
    public function finishInstallationToQA(ProductionTask $task, string $finishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $finishedAt, $note) {
            // لوج دوميني (نحتفظ به فقط)
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
        $this->log($task, 'qa_ack_installation', [
            'by'          => Auth::id(),
            'note'        => trim((string) ($note ?? '')),
        ]);
        DB::transaction(function () use ($task) {
            $task->update(['received_by_owner_at' => now()]);
            $this->notifier->notifyActor('تم تأكيد استلام الجودة (بعد التركيب)', "المهمة #{$task->id}", $task);
        });
    }

    /** الجودة تعتمد بعد التركيب */
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

    /** التصنيع يؤكد استلامه بعد الرفض */
    public function manufacturingAcknowledgeRework(\App\Models\ProductionTask $task, ?string $note = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'تأكيد استلام التصنيع (إعادة عمل)');

            $this->log($task, 'manufacturing_ack_rework', [
                'by'   => \Illuminate\Support\Facades\Auth::id(),
                'note' => $note,
            ]);

            if ($task->status !== \App\Enums\TaskStatus::WaitingProduction->value) {
                $task->forceFill(['status' => TaskStatus::WaitingProduction->value])->save();
            }

        });
    }
    public function installationAcknowledgeRework(\App\Models\ProductionTask $task, ?string $note = null): void
    {
        \Illuminate\Support\Facades\DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'تأكيد استلام التركيب (إعادة عمل)');

            $this->log($task, 'install_ack_rework', [
                'by'   => \Illuminate\Support\Facades\Auth::id(),
                'note' => $note,
            ]);
        });
    }


    /**
     * رفع سند استلام العميل وإكمال المهمة + إغلاق المشروع/الطلب إن لا توجد مهام مفتوحة
     */
    public function uploadClientReceiptAndComplete(ProductionTask $task, ?string $receiptPath, ?string $actualFinishedAt, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $receiptPath, $actualFinishedAt, $note) {
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

            // لوجات دومينية فقط
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
     * ملاحظة: لا نسجّل أي لوج هنا؛ الـ Observer سيتولى:
     * - ownership_changed
     * - sent_to_* (حسب الدور)
     */
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

        // handoff إشعار للمالك الجديد مع زر "عرض المهمة"
        $this->notifier->handoffToOwner(
            $task,
            toRole: $role,
            toUserId: $userId,
            title: 'لديك مهمة بانتظار الإجراء',
            body: $this->notifier->defaultHandoffBody($note)
        );
    }

    /** تحديث الاستلام فقط (الـ Observer سيسجّل ownership_received) */
    public function markOwnerReceived(ProductionTask $task, ?string $note = null): void
    {
        $task->update(['received_by_owner_at' => now()]);
        // ملاحظة: إن رغبت بحفظ note هنا؛ ضعها ضمن لوج دوميني منفصل.
        if ($note && trim($note) !== '') {
            $this->log($task, 'owner_receive_note', ['note' => trim($note)]);
        }
    }

    /** هل يوجد طلب خامات مفتوح */
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
            } catch (\Throwable) {}
        }
    }

    /** إنشاء سجل حدث دوميني (مع Idempotency خفيفة) */
    public function log(
        ProductionTask $task,
        string $type,
        array $data = [],
        ?string $note = null,
        ?Carbon $at = null
    ): TaskLog {
        $note = $note ?? (is_string(Arr::get($data, 'note')) ? trim((string) $data['note']) : null);
        $causerId = Auth::id();
        $when     = $at ?: now();

        // منع التكرار خلال نافذة قصيرة
        $exists = TaskLog::query()
            ->where('task_id', $task->getKey())
            ->where('type', $type)
            ->where('causer_id', $causerId)
            ->where('happened_at', '>=', $when->copy()->subSeconds(2))
            ->exists();

        if ($exists) {
            return new TaskLog(); // تجاهل
        }

        $log = TaskLog::create([
            'task_id'     => $task->getKey(),
            'type'        => $type,
            'data'        => $data,
            'note'        => $note,
            'causer_id'   => $causerId,
            'happened_at' => $when,
        ]);

        if ($task->relationLoaded('logs')) {
            $task->unsetRelation('logs');
            $task->load('logs');
        }

        return $log;
    }
}
