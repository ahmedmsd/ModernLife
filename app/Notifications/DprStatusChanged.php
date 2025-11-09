<?php

namespace App\Notifications;

use App\Models\DepartmentPurchaseRequest;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DprStatusChanged extends Notification
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(
        public DepartmentPurchaseRequest $dpr,
        public string $action,
        public ?string $note = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): array
    {
        $url = url("/admin/department-purchase-requests/{$this->dpr->id}");

        $title = "DPR {$this->dpr->request_number} • {$this->action}";
        $body  = "العنوان: {$this->dpr->title} — الحالة: {$this->dpr->status}" .
            ($this->note ? " — ملاحظة: {$this->note}" : '');

        return $this->filamentDbMessage(
            $title,
            $body,
            [
                'action'         => $this->action,
                'request_id'     => $this->dpr->id,
                'request_number' => $this->dpr->request_number,
                'title'          => $this->dpr->title,
                'status'         => $this->dpr->status,
                'note'           => $this->note,
                'url'            => $url,
            ]
        );
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("DPR {$this->dpr->request_number} • {$this->action}")
            ->line("العنوان: {$this->dpr->title}")
            ->line("الحالة: {$this->dpr->status}")
            ->when($this->note, fn (MailMessage $m) => $m->line("ملاحظة: {$this->note}"))
            ->action('فتح الطلب', url("/admin/department-purchase-requests/{$this->dpr->id}"));
    }
}
