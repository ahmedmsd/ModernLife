<?php

namespace App\Notifications;

use App\Models\ProductionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Support\Settings;

class ProductionRequestStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;
    public $afterCommit = true;

    public function __construct(
        public ProductionRequest $request,
        public string $fromStatus,
        public string $toStatus,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        $channels = [];
        if (Settings::get('notify_in_app', 1)) $channels[] = 'database';
        if (Settings::get('notify_email', 0))  $channels[] = 'mail';
        return $channels;
    }

    protected function title(): string
    {
        return $this->toStatus === 'rejected'
            ? 'تم رفض طلب التصنيع'
            : 'تغيّرت حالة طلب التصنيع';
    }

    public function toMail(object $notifiable): MailMessage
    {
        $pr  = $this->request;
        $url = \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]);

        $mail = (new MailMessage)
            ->subject($this->title() . " #{$pr->id}")
            ->greeting('مرحبًا،')
            ->line("الطلب #{$pr->id}: الحالة تغيّرت من {$this->fromStatus} إلى {$this->toStatus}.");

        if ($this->toStatus === 'rejected' && filled($this->reason)) {
            $mail->line('سبب الرفض: ' . $this->reason);
        }

        return $mail->action('عرض الطلب', $url);
    }

    public function toDatabase(object $notifiable): array
    {
        $pr  = $this->request;
        $url = \App\Filament\Resources\ProductionRequestResource::getUrl('view', ['record' => $pr->getKey()]);

        return [
            'title'   => $this->title(),
            'body'    => $this->toStatus === 'rejected'
                ? "تم رفض الطلب #{$pr->id}" . (filled($this->reason) ? " — السبب: {$this->reason}" : '')
                : "الطلب #{$pr->id}: {$this->fromStatus} ⟶ {$this->toStatus}",
            'icon'    => $this->toStatus === 'rejected' ? 'heroicon-o-x-circle' : 'heroicon-o-arrow-path',
            'actions' => [
                ['label' => 'عرض الطلب', 'url' => $url, 'openUrlInNewTab' => false],
            ],
            'type'        => 'production_request_status_changed',
            'pr_id'       => $pr->id,
            'from_status' => $this->fromStatus,
            'to_status'   => $this->toStatus,
            'reason'      => $this->reason,
            'url'         => $url,
            'created_at'  => now()->toIso8601String(),
        ];
    }
}
