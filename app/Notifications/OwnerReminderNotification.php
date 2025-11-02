<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OwnerReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public array $channels = ['database', 'mail'] // ['database', 'mail'] إن رغبت بالبريد
    ) {}

    public function via($notifiable): array
    {
        return $this->channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject($this->title)
            ->line($this->body);

        if ($this->url) {
            $mail->action('فتح التفاصيل', $this->url);
        }
        return $mail;
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'url'   => $this->url,
        ];
    }
}
