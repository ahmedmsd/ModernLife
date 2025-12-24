<?php

namespace App\Notifications;

use App\Models\MaintenanceRequest;
use App\Models\User;
use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MaintenanceRequestStatusChanged extends Notification
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(
        public MaintenanceRequest $request,
        public string $action,
        public ?User $actor = null,
        public array $extra = []
    ) {
    }

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        // Only send email for new requests or final completion
        $criticalActions = ['new_request', 'completed'];
        if (in_array($this->action, $criticalActions)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase(object $notifiable): array
    {
        $requestId  = $this->request->getKey();
        $clientName = optional($this->request->client)->client_name ?? 'عميل';

        $url = url(
            route(
                'filament.admin.resources.maintenance-requests.view',
                ['record' => $requestId],
                false
            )
        );

        return $this->filamentDbMessage(
            "طلب صيانة #{$requestId} - {$clientName}",
            $this->messageLine(),
            [
                'request_id'   => $requestId,
                'client_id'    => $this->request->client_id,
                'action'       => $this->action,
                'message'      => $this->messageLine(),
                'note'         => $this->extra['note'] ?? null,
                'actor_id'     => $this->actor?->getKey(),
                'actor_name'   => $this->actor?->name,
                'status'       => $this->request->status,
                'url'          => $url,
            ]
        );
    }

    public function toMail(object $notifiable): MailMessage
    {
        $requestId   = $this->request->getKey();
        $clientName  = optional($this->request->client)->client_name ?? 'عميل';
        $subjectBase = "طلب صيانة #{$requestId} - {$clientName}";

        $url = url(
            route(
                'filament.admin.resources.maintenance-requests.view',
                ['record' => $requestId],
                false
            )
        );

        $mail = (new MailMessage)
            ->subject($subjectBase)
            ->greeting('مرحباً،')
            ->line($this->messageLine())
            ->line("رقم الطلب: {$requestId}")
            ->line("اسم العميل: {$clientName}");

        if (! empty($this->extra['note'])) {
            $mail->line('الملاحظة:')
                ->line($this->extra['note']);
        }

        $mail->action('عرض طلب الصيانة', $url)
            ->line('هذه الرسالة أُرسلت آلياً من نظام طلبات الصيانة.');

        return $mail;
    }

    protected function messageLine(): string
    {
        return match ($this->action) {
            'new_request'        => 'تم إنشاء طلب صيانة جديد.',
            'receipt_confirmed'  => 'تم تأكيد استلام طلب الصيانة من مدير المصنع.',
            'started'            => 'تم بدء أعمال الصيانة.',
            'completed'          => 'تم إنهاء أعمال الصيانة وإغلاق الطلب.',
            'note_added'         => 'تمت إضافة ملاحظة جديدة على طلب الصيانة.',
            default              => 'تم تحديث حالة طلب الصيانة.',
        };
    }
}
