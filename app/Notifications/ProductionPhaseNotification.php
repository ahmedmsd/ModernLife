<?php

namespace App\Notifications;

use App\Models\ProductionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ProductionPhaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $prId,
        public string $event,
        public array $context = []
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        $channels = ['database'];
        if (!empty($notifiable->email)) {
            $channels[] = 'mail';
        }
        return $channels;
    }

    public function viaQueues(): array
    {
        return [
            'mail' => 'mail',
            'database' => 'default',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $pr = $this->pr();
        $url = $this->timelineUrl($pr);
        $subject = $this->mailSubject($pr);

        return (new MailMessage)
            ->subject($subject)
            ->greeting('مرحبًا ' . ($notifiable->name ?? ''))
            ->line($this->mailLine())
            ->action('عرض تفاصيل الطلب', $url)
            ->line('رقم الطلب: #' . $pr->id);
    }

    public function toDatabase($notifiable): array
    {
        $pr = $this->pr();

        return [
            'pr_id'   => $pr->id,
            'event'   => $this->event, // was: type
            'context' => $this->context,
            'title'   => $this->dbTitle(),
            'body'    => $this->dbBody(),
            'url'     => $this->timelineUrl($pr),
        ];
    }

    /* ---------------- Helpers ---------------- */

    protected function pr(): ProductionRequest
    {
        return (new \App\Models\ProductionRequest)->findOrFail($this->prId);
    }

    protected function mailSubject(ProductionRequest $pr): string
    {
        return match ($this->event) {
            'transition'        => "تحديث مرحلة لطلب التصنيع #{$pr->id}",
            'received'          => "تأكيد استلام لطلب التصنيع #{$pr->id}",
            'rejected'          => "رفض طلب التصنيع #{$pr->id}",
            'project_bootstrap' => "تهيئة مشروع لطلب التصنيع #{$pr->id}",
            default             => "إشعار حول طلب التصنيع #{$pr->id}",
        };
    }

    protected function mailLine(): string
    {
        return match ($this->event) {
            'transition'        => 'تم تحديث مرحلة الطلب.',
            'received'          => 'تم تأكيد استلام الطلب من قِبل المالك الحالي.',
            'rejected'          => 'تم رفض الطلب.',
            'project_bootstrap' => 'تم إنشاء مشروع ومهام مرتبطة بالطلب.',
            default             => 'لديك إشعار متعلق بطلب التصنيع.',
        };
    }

    protected function dbTitle(): string
    {
        return match ($this->event) {
            'transition'        => 'انتقال مرحلة',
            'received'          => 'تأكيد استلام',
            'rejected'          => 'رفض طلب',
            'project_bootstrap' => 'تهيئة مشروع',
            default             => 'إشعار طلب تصنيع',
        };
    }

    protected function dbBody(): string
    {
        return $this->mailLine();
    }

    protected function timelineUrl(ProductionRequest $pr): string
    {
        $base = config('app.url') ?: url('/');
        return rtrim($base, '/') . "/admin/production-requests/{$pr->id}/timeline";
    }
}
