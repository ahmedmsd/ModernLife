<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\ProductionTask;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductionTask $task,
        public bool $isReassignment = false
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $project   = $this->task->project;
        $deptName  = optional($this->task->department)->dept_name;
        $dueDate   = $this->task->due_date?->format('Y-m-d');
        $budget    = $this->task->assigned_budget ? number_format($this->task->assigned_budget, 2) . ' SAR' : '—';
        $status    = $this->task->status;
        $notes     = $this->task->notes ?: '—';

        $url = url("/admin/projects/{$this->task->project_id}/manage-tasks");

        $title = $this->isReassignment ? 'تم إعادة إسناد مهمة تصنيع لك' : 'تم إسناد مهمة تصنيع جديدة لك';

        $mail = (new MailMessage)
            ->subject($title . ' - مشروع: ' . ($project->project_name ?? ('#' . $project->id)))
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line($title)
            ->line('**المشروع:** ' . ($project->project_name ?? ('#' . $project->id)))
            ->line('**القسم:** ' . ($deptName ?: 'غير محدد'))
            ->line('**الحالة:** ' . $status)
            ->line('**تاريخ التسليم المتوقع:** ' . ($dueDate ?: 'غير محدد'))
            ->line('**الميزانية المتوقعة:** ' . $budget)
            ->line('**ملاحظات:** ' . $notes);

        if ($url) {
            $mail->action('فتح المهمة', $url);
        }

        return $mail
            ->line('يمكنك تعديل تفاصيل المهمة أو بدء التنفيذ من خلال النظام.');
    }
}
