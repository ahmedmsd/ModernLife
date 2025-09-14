<?php
namespace App\Notifications;

use App\Models\ProductionTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DepartmentTaskMail extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductionTask $task, public string $reason) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $t = $this->task;
        $title = match ($this->reason) {
            'created'     => 'مهمة جديدة لقسمك',
            'reassigned'  => 'تم تحويل مهمة إلى قسمك',
            'ownership'   => 'تم إسناد مهمة لك كمدير قسم',
            default       => 'تنبيه مهمة',
        };
        $url = \App\Filament\Resources\ProjectResource::getUrl('view', ['record' => $t->project_id]);

        return (new MailMessage)
            ->subject($title . " (#{$t->id})")
            ->greeting('مرحبًا')
            ->line("المهمة: {$t->task_name} (رقم: #{$t->id})")
            ->lineIf($t->department?->dept_name, 'القسم: ' . $t->department->dept_name)
            ->action('عرض المشروع', $url);
    }
}
