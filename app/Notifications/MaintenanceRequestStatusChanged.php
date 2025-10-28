<?php


namespace App\Notifications;

use App\Models\MaintenanceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;

class MaintenanceRequestStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public MaintenanceRequest $request,
        public string             $action,   // new_request|started|completed|note_added|custom
        public ?string            $note = null,
    )
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toDatabase($notifiable): DatabaseMessage
    {
        return new DatabaseMessage([
            'type' => 'maintenance',
            'action' => $this->action,
            'request_id' => $this->request->id,
            'status' => $this->request->status,
            'title' => 'طلب صيانة #' . $this->request->id,
            'message' => $this->messageLine(),
            'note' => $this->note,
            'url' => url(\App\Filament\Resources\MaintenanceRequestResource::getUrl('view', ['record' => $this->request])),
        ]);
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->mailSubject())
            ->greeting('مرحبًا،')
            ->line($this->messageLine())
            ->when($this->note, fn(MailMessage $m) => $m->line('ملاحظة: ' . $this->note))
            ->action('فتح طلب الصيانة', url(\App\Filament\Resources\MaintenanceRequestResource::getUrl('view', ['record' => $this->request])));
    }

    protected function mailSubject(): string
    {
        return match ($this->action) {
            'new_request' => "طلب صيانة جديد #{$this->request->id}",
            'started' => "بدء صيانة لطلب #{$this->request->id}",
            'completed' => "إغلاق طلب صيانة #{$this->request->id}",
            'note_added' => "إضافة ملاحظة لطلب صيانة #{$this->request->id}",
            default => "تحديث طلب صيانة #{$this->request->id}",
        };
    }

    protected function messageLine(): string
    {
        return match ($this->action) {
            'new_request' => "تم إنشاء طلب صيانة جديد.",
            'started' => 'تم بدء أعمال الصيانة.',
            'completed' => 'تم إغلاق طلب الصيانة (مكتمل).',
            'note_added' => 'تمت إضافة ملاحظة جديدة.',
            default => 'تم تحديث حالة طلب الصيانة.',
        };
    }
}
