<?php

namespace App\Notifications;

use App\Models\ProductionTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;

class TaskSentForConfirmation extends Notification
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    protected ProductionTask $task;
    protected string $url;

    public function __construct(ProductionTask $task, string $url)
    {
        $this->task = $task;
        $this->url = $url;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return $this->filamentDbMessage(
            title: 'مهمة بحاجة لتأكيد الاستلام',
            body: "المهمة (#{$this->task->id}) تم تعديلها وتحتاج تأكيد استلامك.",
            extra: [
                'task_id'     => $this->task->id,
                'project_id'  => $this->task->project_id ?? null,
                'url'         => $this->url,
                'sent_by'     => auth()->id(),
                'sent_at'     => now(),
            ]
        );
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
