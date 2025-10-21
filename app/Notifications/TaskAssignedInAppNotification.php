<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ProductionTask;

class TaskAssignedInAppNotification extends Notification
{
    use Queueable;

    public function __construct(
        public ProductionTask $task,
        public bool $isReassignment = false
    ) {}

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
        $url       = url("/admin/projects/{$this->task->project_id}/manage-tasks");

        return [
            'title'       => $title,
            'body'        => "المشروع: " . ($project->project_name ?? "#{$project->id}") .
                " — القسم: {$deptName} — التسليم: {$dueDate}",
            'project_id'  => $project->id,
            'task_id'     => $this->task->id,
            'url'         => $url,
            'action_text' => 'فتح المهمة',
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
