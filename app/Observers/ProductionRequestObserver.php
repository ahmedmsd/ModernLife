<?php

namespace App\Observers;

use App\Enums\ProductionRequestStatus;
use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductionRequestObserver
{
    /**
     * عند إنشاء الطلب لأول مرة
     */
    public function created(ProductionRequest $productionRequest): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $productionRequest->id,
            'user_id'   => Auth::id() ?? 0,
            'action'    => 'created',
            'note'      => 'تم إنشاء الطلب',
            'action_at' => now(),
        ]);

    }

    public function updated(ProductionRequest $productionRequest): void
    {
        if (! $productionRequest->wasChanged('status')) {
            return;
        }

        $newStatusRaw = $productionRequest->status;
        $oldStatusRaw = $productionRequest->getOriginal('status');

        $newStatus = $this->toStatusEnum($newStatusRaw);
        $oldStatus = $this->toStatusEnum($oldStatusRaw);

        // سجل تغيير الحالة إن كانت صالحة
        if ($newStatus) {
            ProductionRequestLog::create([
                'production_request_id' => $productionRequest->id,
                'user_id'   => Auth::id() ?? 0,
                'action'    => $newStatus->value,
                'note'      => 'تم تغيير حالة الطلب إلى ' . (method_exists($newStatus, 'label') ? $newStatus->label() : $newStatus->value),
                'action_at' => now(),
            ]);
        } else {
            logger()->error('حالة غير معروفة أثناء تحديث الطلب', [
                'production_request_id' => $productionRequest->id,
                'new_status' => $newStatusRaw,
                'old_status' => $oldStatusRaw,
            ]);
            return;
        }

        // ننفّذ الإنشاء فقط عند الانتقال لأول مرة إلى APPROVED
        $becameApproved = ($newStatus === ProductionRequestStatus::APPROVED)
            && ($oldStatus !== ProductionRequestStatus::APPROVED);

        if (! $becameApproved) {
            return;
        }

        DB::transaction(function () use ($productionRequest) {
            // 1) إنشاء المشروع (idempotent)
            $project = Project::firstOrCreate(
                ['production_request_id' => $productionRequest->id],
                [
                    'client_id'    => $productionRequest->client_id,
                    'project_name' => $productionRequest->project_name ?? 'مشروع بدون اسم',
                    'description'  => $productionRequest->description,
                    'start_date'   => now(),
                    'status'       => 'in_progress',
                    'created_by'   => Auth::id() ?? 0,
                ]
            );

            // 2) نسخ ملفات الطلب إلى ملفات المشروع بدون تكرار
            $productionRequest->loadMissing('files');

            foreach ($productionRequest->files as $reqFile) {
                $filePath = $reqFile->file_path;
                $fileName = basename($filePath);
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileSize = Storage::disk('public')->exists($filePath)
                    ? Storage::disk('public')->size($filePath)
                    : 0;

                $project->files()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $filePath, // الاحتفاظ بنفس المسار
                    ],
                    [
                        'file_name'   => $fileName,
                        'file_type'   => $fileType,
                        'file_size'   => $fileSize,
                        'uploaded_by' => Auth::id() ?? 0,
                        'upload_date' => now(),
                        'version'     => 1,
                        'is_current'  => true,
                    ]
                );
            }

            foreach ($productionRequest->files as $reqFile) {
                $project->tasks()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $reqFile->file_path,
                    ],
                    [
                        'assigned_to_employee_id' => null,   // يحدَّد لاحقًا من واجهة إدارة المهام
                        'assigned_budget'         => null,
                        'due_date'                => null,   // عدّلها لقيمة افتراضية لو الحقل NOT NULL
                        'notes'                   => 'تم إنشاؤها تلقائيًا من ملفات الطلب.',
                        'status'                  => 'assigned',
                    ]
                );
            }
        });
    }

    /**
     * عند حذف الطلب
     */
    public function deleted(ProductionRequest $productionRequest): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $productionRequest->id,
            'user_id'   => Auth::id() ?? 0,
            'action'    => 'deleted',
            'note'      => 'تم حذف الطلب',
            'action_at' => now(),
        ]);
    }

    /**
     * تحويل قيمة الحالة إلى Enum بشكل آمن.
     */
    private function toStatusEnum(mixed $value): ?ProductionRequestStatus
    {
        try {
            if ($value instanceof ProductionRequestStatus) {
                return $value;
            }
            if ($value instanceof \BackedEnum) {
                return ProductionRequestStatus::from((string) $value->value);
            }
            return ProductionRequestStatus::from((string) $value);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
