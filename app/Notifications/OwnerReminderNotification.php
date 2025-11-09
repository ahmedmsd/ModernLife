<?php

namespace App\Notifications;

use App\Notifications\Concerns\AsFilamentDatabaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OwnerReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use AsFilamentDatabaseNotification;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public array $channels = ['database', 'mail']
    ) {
    }

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

    public function toDatabase($notifiable): array
    {
        return $this->filamentDbMessage(
            $this->title,
            $this->body,
            [
                'url' => $this->url,
            ]
        );
    }

    public function toArray($notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
