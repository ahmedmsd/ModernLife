<?php

namespace App\Notifications;

use App\Models\ProductionTask;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TaskAssignedInAppNotification extends Notification
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(
        public ProductionTask $task,
        public bool $isReassignment = false
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $project   = $this->task->project;
        $deptName  = $this->task->department?->dept_name ?? 'غير محدد';
        $dueDate   = optional($this->task->due_date)?->format('Y-m-d') ?? 'غير محدد';
        $title     = $this->isReassignment ? 'إعادة إسناد مهمة تصنيع' : 'مهمة تصنيع جديدة أُسندت إليك';
        $body      = 'المشروع: ' . ($project->project_name ?? "#{$project->id}") .
            ' — القسم: ' . $deptName .
            ' — التسليم: ' . $dueDate;
        $url       = url("/admin/projects/{$this->task->project_id}/manage-tasks");

        return $this->filamentDbMessage(
            $title,
            $body,
            [
                'project_id'  => $project->id,
                'task_id'     => $this->task->id,
                'url'         => $url,
                'action_text' => 'فتح المهمة',
            ]
        );
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
