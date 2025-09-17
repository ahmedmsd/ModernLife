<?php

namespace App\Notifications;

use App\Models\ProductionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Support\Settings;

class ProductionRequestUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ProductionRequest $request,
        public array $changedKeys
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if (Settings::get('notify_in_app', 1)) $channels[] = 'database';
        if (Settings::get('notify_email', 0))  $channels[] = 'mail';
        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pr  = $this->request;
        $url = \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]);
        $changed = implode(', ', $this->changedKeys);

        return (new MailMessage)
            ->subject("تم تعديل طلب التصنيع #{$pr->id}")
            ->greeting('مرحبًا،')
            ->line("تم تعديل الطلب #{$pr->id}.")
            ->line("الحقول المعدلة: {$changed}")
            ->action('عرض الطلب', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $pr  = $this->request;
        $url = \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]);
        $changed = implode(', ', $this->changedKeys);

        return [
            'title'   => 'تم تعديل طلب التصنيع',
            'body'    => "رقم الطلب #{$pr->id}. الحقول المعدلة: {$changed}",
            'icon'    => 'heroicon-o-pencil-square',
            'actions' => [
                ['label' => 'عرض الطلب', 'url' => $url, 'openUrlInNewTab' => false],
            ],
            'type'       => 'production_request_updated',
            'pr_id'      => $pr->id,
            'changed'    => $this->changedKeys,
            'url'        => $url,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
