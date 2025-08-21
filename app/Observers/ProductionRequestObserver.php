<?php

namespace App\Observers;

use App\Models\ProductionRequest;
use App\Models\ProductionRequestLog;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\ProductionRequestWorkflow;

class ProductionRequestObserver
{
    /**
     * عند إنشاء الطلب لأول مرة
     */
    public function created(ProductionRequest $pr): void
    {
        // Log أساسي
        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'created',
            'data'        => [
                'phase'      => $pr->current_phase,
                'status'     => $pr->phase_status,
                'owner_role' => $pr->current_owner_role,
                'owner_user' => $pr->current_owner_user_id,
            ],
            'note'        => 'تم إنشاء الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);

        // بدء سير العمل إن لم تُهيأ الحقول
        try {
            if (blank($pr->current_phase) || blank($pr->phase_status)) {
                app(ProductionRequestWorkflow::class)->start($pr);
            }
        } catch (\Throwable $e) {
            logger()->warning('Workflow start failed on created', [
                'production_request_id' => $pr->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * عند تحديث الطلب
     */
    public function updated(ProductionRequest $pr): void
    {
        // نسجّل انتقالًا إذا تغيّرت أي من هذه الحقول
        $watched = [
            'current_phase', 'phase_status',
            'current_owner_role', 'current_owner_user_id',
            'sent_to_owner_at', 'received_by_owner_at',
        ];

        $hasTransition = collect($watched)->some(fn ($f) => $pr->wasChanged($f));

        if ($hasTransition) {
            ProductionRequestLog::create([
                'production_request_id' => $pr->id,
                'type'        => 'transition',
                'data'        => [
                    'from' => [
                        'phase'      => $pr->getOriginal('current_phase'),
                        'status'     => $pr->getOriginal('phase_status'),
                        'owner_role' => $pr->getOriginal('current_owner_role'),
                        'owner_user' => $pr->getOriginal('current_owner_user_id'),
                        'sent_at'    => $pr->getOriginal('sent_to_owner_at'),
                        'recv_at'    => $pr->getOriginal('received_by_owner_at'),
                    ],
                    'to' => [
                        'phase'      => $pr->current_phase,
                        'status'     => $pr->phase_status,
                        'owner_role' => $pr->current_owner_role,
                        'owner_user' => $pr->current_owner_user_id,
                        'sent_at'    => $pr->sent_to_owner_at,
                        'recv_at'    => $pr->received_by_owner_at,
                    ],
                ],
                'note'        => sprintf(
                    'Phase: %s → %s | Status: %s → %s | Owner: %s → %s',
                    $pr->getOriginal('current_phase') ?? '—',
                    $pr->current_phase ?? '—',
                    $pr->getOriginal('phase_status') ?? '—',
                    $pr->phase_status ?? '—',
                    $pr->getOriginal('current_owner_role') ?? '—',
                    $pr->current_owner_role ?? '—',
                ),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        }

        // عند اعتماد مدير المصنع: أنشئ مشروعًا ومهام الأقسام
        $becameApprovedByFactory =
            $pr->wasChanged('phase_status')
            && ( (string) $pr->phase_status === 'approved' )
            && ( (string) $pr->current_owner_role === 'factory_manager' );

        if (! $becameApprovedByFactory) {
            return;
        }

        DB::transaction(function () use ($pr) {
            // 1) مشروع مرتبط (idempotent)
            $project = Project::firstOrCreate(
                ['production_request_id' => $pr->id],
                [
                    'client_id'    => $pr->client_id,
                    'project_name' => $pr->project_name ?? 'مشروع بدون اسم',
                    'description'  => $pr->project_description ?? $pr->description,
                    'start_date'   => now(),
                    'status'       => 'in_progress',
                    'created_by'   => Auth::id() ?? 0,
                ]
            );

            // 2) ملفات المشروع من ملفات الطلب
            $pr->loadMissing('files');

            $filesCreated = 0;
            foreach ($pr->files as $reqFile) {
                $filePath = $reqFile->file_path;
                $fileName = basename($filePath);
                $fileType = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileSize = Storage::disk('public')->exists($filePath)
                    ? Storage::disk('public')->size($filePath)
                    : 0;

                $created = $project->files()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $filePath,
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

                if ($created->wasRecentlyCreated) {
                    $filesCreated++;
                }
            }

            // 3) مهمة لكل ملف قسم
            $tasksCreated = 0;
            foreach ($pr->files as $reqFile) {
                $task = $project->tasks()->firstOrCreate(
                    [
                        'department_id' => $reqFile->department_id,
                        'file_path'     => $reqFile->file_path,
                    ],
                    [
                        'assigned_to_employee_id' => null,
                        'assigned_budget'         => null,
                        'due_date'                => null,
                        'notes'                   => 'تم إنشاؤها تلقائيًا من ملفات الطلب.',
                        'status'                  => 'pending',
                        'current_owner_role'      => null,
                        'current_owner_user_id'   => null,
                        'sent_to_owner_at'        => null,
                        'received_by_owner_at'    => null,
                    ]
                );

                if ($task->wasRecentlyCreated) {
                    $tasksCreated++;
                }
            }

            ProductionRequestLog::create([
                'production_request_id' => $pr->id,
                'type'        => 'project_bootstrap',
                'data'        => [
                    'project_id'    => $project->id,
                    'files_created' => $filesCreated,
                    'tasks_created' => $tasksCreated,
                ],
                'note'        => 'تم إنشاء مشروع ومهام الأقسام بعد اعتماد مدير المصنع.',
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);
        });
    }

    /**
     * عند حذف الطلب
     */
    public function deleted(ProductionRequest $pr): void
    {
        ProductionRequestLog::create([
            'production_request_id' => $pr->id,
            'type'        => 'deleted',
            'data'        => null,
            'note'        => 'تم حذف الطلب',
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);
    }
}
