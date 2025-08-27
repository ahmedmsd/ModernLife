<?php
// app/Notifications/NewTaskCommentNotification.php
namespace App\Notifications;

use App\Models\TaskComment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewTaskCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TaskComment $comment) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $task = $this->comment->task;
        return [
            'type'       => 'task_comment',
            'task_id'    => $task->id,
            'task_title' => $task->title ?? ("Task #{$task->id}"),
            'comment_id' => $this->comment->id,
            'author'     => $this->comment->author?->name,
            'excerpt'    => $this->comment->excerpt,
            'created_at' => $this->comment->created_at?->toDateTimeString(),
        ];
    }
}
