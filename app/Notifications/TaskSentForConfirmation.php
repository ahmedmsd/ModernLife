<?php

namespace App\Notifications;

use App\Models\ProductionTask;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class TaskSentForConfirmation extends Notification
{
    use Queueable;

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
        return [
            'title'       => 'مهمة بحاجة لتأكيد الاستلام',
            'message'     => "المهمة (#{$this->task->id}) تم تعديلها وتحتاج تأكيد استلامك.",
            'task_id'     => $this->task->id,
            'project_id'  => $this->task->project_id ?? null,
            'url'         => $this->url,
            'sent_by'     => auth()->id(),
            'sent_at'     => now(),
        ];
    }

}
