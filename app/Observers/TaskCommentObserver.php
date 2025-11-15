<?php
// app/Observers/TaskCommentObserver.php
namespace App\Observers;

use App\Models\TaskComment;
use App\Notifications\NewTaskCommentNotification;
use Illuminate\Support\Facades\Notification;

class TaskCommentObserver
{
    public function created(TaskComment $comment): void
    {
        $task = $comment->task;

        $recipients = collect();

        if (method_exists($task, 'creator') && $task->creator) {
            $recipients->push($task->creator);
        }

        if (property_exists($task, 'assigned_to_user_id') && $task->assigned_to_user_id && $task->assignedToUser) {
            $recipients->push($task->assignedToUser);
        }

        if (method_exists($task, 'watchers')) {
            $recipients = $recipients->merge($task->watchers()->get());
        }

        $recipients = $recipients->filter(fn ($u) => $u && $u->id !== $comment->user_id)->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new NewTaskCommentNotification($comment));
        }
    }
}
