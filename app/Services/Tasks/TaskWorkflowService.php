<?php

namespace App\Services\Tasks;

use App\Models\ProductionTask;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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

            // تحويل الملكية لمدير المصنع
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

    /**
     * فتح طلب خامات: تتحول المهمة لحالة materials_wait والمالك يصبح المشتريات.
     */
    public function requestMaterials(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            // هنا يبقى منطق إنشاء سجلات طلب الخامات كما هو في مشروعك

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
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام المشتريات للمهمة.
     */
    public function purchasingReceive(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام المشتريات للمهمة' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'purchasing_receive', [
                'note' => $note,
            ]);
        });
    }

    /**
     * تجهيز الخامات بالكامل (ما زالت عند المشتريات حتى استلام القسم).
     */
    public function materialsProvided(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'materials_prep';
            $task->save();

            $this->log($task, 'materials_provided', [
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام الخامات بالكامل من القسم - يسمح بالبدء في التصنيع.
     * المالك ينتقل من المشتريات إلى مدير القسم.
     */
    public function materialsReceivedOk(
        ProductionTask $task,
        ?string $note = null
    ): void {
        DB::transaction(function () use ($task, $note) {
            $task->status = 'waiting_production';
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
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام جزئي مع السماح بالبدء (جزء من الخامات متوفر) - يسلّم للقسم.
     */
    public function materialsReceivedPartialAllowStart(
        ProductionTask $task,
        ?string $note = null,
        ?string $missingItemsNote = null
    ): void {
        DB::transaction(function () use ($task, $note, $missingItemsNote) {
            $task->status = 'waiting_production';
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
                'note'    => $note,
                'missing' => $missingItemsNote,
            ]);
        });
    }

    /**
     * استلام جزئي مع إيقاف البدء - تبقى عند المشتريات حتى استكمال الناقص.
     */
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

    /**
     * مشكلة في الخامات - تبقى تحت مسؤولية المشتريات حتى المعالجة.
     */
    public function materialsReceivedIssue(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
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
                'note' => $note,
            ]);
        });
    }

    /**
     * بدء التصنيع.
     */
    public function startProduction(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status          = 'in_progress';
            $task->actual_start_at = $task->actual_start_at ?: now();
            $task->save();

            $deptManagerId = $this->resolveDeptManagerUserId($task);

            $this->setOwner(
                task: $task,
                role: 'department_manager',
                userId: $deptManagerId,
                touchSent: false,
                note: 'بدء التصنيع' . ($note ? ' - ' . $note : '')
            );

            $this->log($task, 'start_production', [
                'note' => $note,
            ]);
        });
    }

    /**
     * إنهاء التصنيع وإرسال المهمة للجودة بعد التصنيع.
     */
    public function finishManufacturingAndSendToQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status       = 'under_review';
            $task->actual_end_at = $task->actual_end_at ?: now();
            $task->save();

            $qaUserId = $this->resolveQualityManagerUserId();

            $this->setOwner(
                task: $task,
                role: 'quality_manager',
                userId: $qaUserId,
                touchSent: true,
                note: 'إرسال للجودة بعد التصنيع'
            );

            $this->log($task, 'finish_manufacturing_to_qa', [
                'note' => $note,
            ]);
        });
    }

    /**
     * استلام الجودة لمهمة ما بعد التصنيع.
     */
    public function qaAcknowledgeManufacturing(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $this->markOwnerReceived($task, 'استلام الجودة بعد التصنيع' . ($note ? ' - ' . $note : ''));

            $this->log($task, 'qa_acknowledge_manufacturing', [
                'note' => $note,
            ]);
        });
    }

    /**
     * اعتماد جودة التصنيع - تحويل للتركيب (مدير التركيب = نفس مدير القسم).
     */
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

            $this->log($task, 'approve_manufacturing_qa', [
                'note' => $note,
            ]);
        });
    }

    /**
     * رفض جودة التصنيع - إعادة المهمة للتصنيع.
     */
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

            $this->log($task, 'reject_manufacturing_qa', [
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

            $this->log($task, 'installation_acknowledge', [
                'note' => $note,
            ]);
        });
    }

    /**
     * بدء التركيب.
     */
    public function startInstallation(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
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

            $this->log($task, 'start_installation', [
                'note' => $note,
            ]);
        });
    }

    /**
     * إنهاء التركيب وإرسال للجودة بعد التركيب.
     */
    public function finishInstallationToQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
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

            $this->log($task, 'finish_installation_to_qa', [
                'note' => $note,
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

            $this->log($task, 'qa_acknowledge_installation', [
                'note' => $note,
            ]);
        });
    }

    /**
     * اعتماد الجودة بعد التركيب.
     */
    public function approveInstallationQA(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            $task->status                = 'qa_approved';
            $task->current_owner_role    = null;
            $task->current_owner_user_id = null;
            $task->received_by_owner_at  = now();
            $task->save();

            $this->log($task, 'approve_installation_qa', [
                'note' => $note,
            ]);
        });
    }

    /**
     * رفض الجودة بعد التركيب - إعادة للتركيب (نفس مدير القسم).
     */
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

            $this->log($task, 'reject_installation_qa', [
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

            $this->log($task, 'manufacturing_acknowledge_rework', [
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

            $this->log($task, 'installation_acknowledge_rework', [
                'note' => $note,
            ]);
        });
    }

    /**
     * إكمال المهمة بعد رفع مستند تأكيد العميل.
     */
    public function uploadClientReceiptAndComplete(ProductionTask $task, ?string $note = null): void
    {
        DB::transaction(function () use ($task, $note) {
            // منطق حفظ ملف تأكيد العميل تبقيه كما هو في مشروعك

            $task->status                = 'completed';
            $task->completed_at          = $task->completed_at ?: now();
            $task->current_owner_role    = null;
            $task->current_owner_user_id = null;
            $task->save();

            $this->log($task, 'upload_client_receipt_and_complete', [
                'note' => $note,
            ]);

            // منطق إغلاق المشروع/طلب الإنتاج عند اكتمال كل المهام يضاف هنا عند الحاجة
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

        if (! $dept) {
            return null;
        }

        if (method_exists($dept, 'managerUser') && $dept->managerUser) {
            $manager = $dept->managerUser;

            if (property_exists($manager, 'user_id') && $manager->user_id) {
                return $manager->user_id;
            }

            if ($manager instanceof User && $manager->id) {
                return $manager->id;
            }
        }

        if (! empty($dept->manager_id)) {
            return (int) $dept->manager_id;
        }

        return null;
    }

    /**
     * مدير التركيب = نفس مدير القسم.
     */
    protected function resolveInstallationManagerUserId(ProductionTask $task): ?int
    {
        return $this->resolveDeptManagerUserId($task);
    }

    /**
     * مدير المشتريات: أول مستخدم له دور purchasing_manager.
     */
    protected function resolvePurchasingManagerUserId(): ?int
    {
        $user = User::role('purchasing_manager')->first();

        return $user?->id;
    }

    protected function resolveFactoryManagerUserId(): ?int
    {
        $u = User::role('factory_manager')->first();
        return $u?->id;
    }

    protected function resolveQualityManagerUserId(): ?int
    {
        $user = User::role('quality_manager')->first();

        return $user?->id;
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
