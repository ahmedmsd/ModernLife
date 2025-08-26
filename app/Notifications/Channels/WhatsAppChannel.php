<?php
// app/Notifications/Channels/WhatsAppChannel.php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use Twilio\Rest\Client;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        if (! config('notify.whatsapp')) {
            return;
        }

        // رقم المستلم
        $to = method_exists($notifiable, 'routeNotificationForWhatsApp')
            ? $notifiable->routeNotificationForWhatsApp()
            : ($notifiable->phone ?? null);

        if (! $to) {
            return;
        }

        $sid   = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $from  = config('services.twilio.whatsapp_from');

        if (! $sid || ! $token || ! $from) {
            return;
        }

        // Twilio يتطلب البادئة "whatsapp:"
        $to   = str_starts_with($to, 'whatsapp:') ? $to : ('whatsapp:' . $to);
        $from = str_starts_with($from, 'whatsapp:') ? $from : ('whatsapp:' . $from);

        $message = method_exists($notification, 'toWhatsApp')
            ? $notification->toWhatsApp($notifiable)
            : (method_exists($notification, 'toArray') ? $notification->toArray($notifiable)['body'] ?? '' : '');

        if (! $message) {
            return;
        }

        $client = new Client($sid, $token);
        $client->messages->create($to, [
            'from' => $from,
            'body' => $message,
        ]);
    }
}
