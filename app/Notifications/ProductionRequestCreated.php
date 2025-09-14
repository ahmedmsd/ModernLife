<?php

// app/Notifications/ProductionRequestCreated.php
namespace App\Notifications;

use App\Models\ProductionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Support\Settings;

class ProductionRequestCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductionRequest $request) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if (Settings::get('notify_in_app', 1)) $channels[] = 'database';
        if (Settings::get('notify_email', 0))  $channels[] = 'mail';
        return $channels;
    }

    protected function title(): string
    {
        return $this->request->request_type === 'direct'
            ? 'طلب تصنيع مباشر'
            : 'طلب تصنيع غير مباشر';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pr   = $this->request;
        $url  = \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]);

        return (new MailMessage)
            ->subject("تم إنشاء {$this->title()} #{$pr->id}")
            ->greeting('مرحبًا،')
            ->line("تم إنشاء {$this->title()} برقم #{$pr->id}.")
            ->lineIf(!empty($pr->project_name ?? ''), 'المشروع: ' . $pr->project_name)
            ->action('عرض الطلب', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $pr = $this->request;

        return [
            'type'                  => 'production_request_created',
            'title'                 => $this->title(),
            'production_request_id' => $pr->id,
            'project_name'          => $pr->project_name ?? null,
            'request_type'          => $pr->request_type,        // direct | indirect
            'url'                   => \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]),
            'created_at'            => now()->toIso8601String(),
        ];
    }
}

