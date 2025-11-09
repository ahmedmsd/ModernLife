<?php

namespace App\Notifications;

use App\Models\ProductionTask;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TaskReviewResultNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(
        public ProductionTask $task,
        public bool $approved,
        public ?string $managerNote = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project  = $this->task->project;
        $title    = $this->approved ? 'تم اعتماد مهمتك' : 'تمت إعادتها للمراجعة (Rework)';
        $status   = $this->approved ? 'completed' : 'rework';
        $url      = url("/admin/projects/{$this->task->project_id}/manage-tasks");

        $mail = (new MailMessage)
            ->subject($title . ' - مشروع: ' . ($project->project_name ?? ('#' . $project->id)))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line($title)
            ->line('المشروع: ' . ($project->project_name ?? ('#' . $project->id)))
            ->line('الحالة الجديدة: ' . $status);

        if ($this->managerNote) {
            $mail->line('ملاحظة المدير: ' . $this->managerNote);
        }

        return $mail->action('عرض المهمة', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $project = $this->task->project;
        $status  = $this->approved ? 'completed' : 'rework';
        $title   = $this->approved ? 'تم اعتماد المهمة' : 'إعادة عمل للمهمة';

        $body = 'المشروع: ' . ($project->project_name ?? ('#' . $project->id)) .
            ' — الحالة الجديدة: ' . $status .
            ($this->managerNote ? ' — ملاحظة: ' . $this->managerNote : '');

        $url = url("/admin/projects/{$this->task->project_id}/manage-tasks");

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
}
