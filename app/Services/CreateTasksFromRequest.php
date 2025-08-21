<?php

namespace App\Services;

use App\Models\{ProductionRequest, Project, ProductionTask, Department, ProductionTasksLog};
use Illuminate\Support\Facades\DB;

class CreateTasksFromRequest
{
    public function handle(ProductionRequest $pr): void
    {
        DB::transaction(function () use ($pr) {
            // 1) أنشئ المشروع إن لم يوجد
            $project = $pr->project;
            if (! $project) {
                $project = Project::create([
                    'production_request_id' => $pr->id,
                    'client_id'             => $pr->client_id,
                    'project_name'          => $pr->project_name,
                    'description'           => $pr->project_description,
                    'status'                => 'active',
                    'created_by'            => $pr->created_by,
                ]);
            }

            // 2) أنشئ مهمة لكل ملف قسم
            $pr->loadMissing('files.department');

            foreach ($pr->files as $file) {
                /** @var Department $dept */
                $dept = $file->department;
                $managerEmployeeId = $dept?->manager_id;

                $task = ProductionTask::create([
                    'project_id'              => $project->id,
                    'department_id'           => $file->department_id,
                    'assigned_to_employee_id' => $managerEmployeeId, // يُسند لمدير القسم إن وُجد
                    'assigned_budget'         => $pr->total_price ?: 0, // يمكن لاحقًا تخصيص ميزانية لكل مهمة
                    'file_path'               => $file->file_path,
                    'status'                  => $managerEmployeeId ? 'assigned' : 'pending',
                    'assigned_at'             => $managerEmployeeId ? now() : null,
                ]);

                // لوج
                ProductionTasksLog::create([
                    'task_id'     => $task->id,
                    'type'        => 'created',
                    'data'        => ['status' => $task->status, 'department_id' => $file->department_id],
                    'causer_id'   => auth()->id(),
                    'happened_at' => now(),
                ]);
            }
        });
    }
}
