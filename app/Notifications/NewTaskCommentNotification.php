<?php

namespace App\Notifications;

use App\Models\TaskComment;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewTaskCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(public TaskComment $comment)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        $task  = $this->comment->task;
        $title = 'تعليق جديد على مهمة';
        $body  = 'مهمة: ' . ($task->title ?? "Task #{$task->id}") .
            ' — بواسطة: ' . ($this->comment->author?->name ?? 'مستخدم') .
            ' — ' . $this->comment->excerpt;

        return $this->filamentDbMessage(
            $title,
            $body,
            [
                'type'       => 'task_comment',
                'task_id'    => $task->id,
                'task_title' => $task->title ?? "Task #{$task->id}",
                'comment_id' => $this->comment->id,
                'author'     => $this->comment->author?->name,
                'excerpt'    => $this->comment->excerpt,
                'created_at' => $this->comment->created_at?->toDateTimeString(),
            ]
        );
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
