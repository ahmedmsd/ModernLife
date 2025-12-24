<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ActionHandoffNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public ?string $body = null,
        public ?string $url  = null,
        public bool $sendMail = true,
        public string $action = '', // Added 'action' property
    ) {}

    public function via($notifiable): array
    {
        $channels = ['database'];
        if ($this->sendMail && config('notify.email', true) && !empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->greeting('مرحبًا!')
            ->line($this->body ?? $this->title);

        if ($this->url) {
            $mail->action('فتح المهمة', $this->url);
        }

        return $mail->salutation('تحياتنا');
    }
}
