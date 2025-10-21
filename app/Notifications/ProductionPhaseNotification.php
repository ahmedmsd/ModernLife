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
        public ProductionRequest $pr,
        public string $type,
        public array $context = []
    ) {
        $this->afterCommit();
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $url = $this->timelineUrl($this->pr);
        $subject = $this->mailSubject();

        return (new MailMessage)
            ->subject($subject)
            ->greeting('مرحبًا ' . ($notifiable->name ?? ''))
            ->line($this->mailLine())
            ->action('عرض تفاصيل الطلب', $url)
            ->line('رقم الطلب: #' . $this->pr->id);
    }

    public function toDatabase($notifiable): array
    {
        return [
            'pr_id'     => $this->pr->id,
            'type'      => $this->type,
            'context'   => $this->context,
            'title'     => $this->dbTitle(),
            'body'      => $this->dbBody(),
            'url'       => $this->timelineUrl($this->pr),
        ];
    }

    protected function mailSubject(): string
    {
        return match ($this->type) {
            'transition'        => "تحديث مرحلة لطلب التصنيع #{$this->pr->id}",
            'received'          => "تأكيد استلام لطلب التصنيع #{$this->pr->id}",
            'rejected'          => "رفض طلب التصنيع #{$this->pr->id}",
            'project_bootstrap' => "تهيئة مشروع لطلب التصنيع #{$this->pr->id}",
            default             => "إشعار حول طلب التصنيع #{$this->pr->id}",
        };
    }

    protected function mailLine(): string
    {
        return match ($this->type) {
            'transition'        => 'تم تحديث مرحلة الطلب.',
            'received'          => 'تم تأكيد استلام الطلب من قِبل المالك الحالي.',
            'rejected'          => 'تم رفض الطلب.',
            'project_bootstrap' => 'تم إنشاء مشروع ومهام مرتبطة بالطلب.',
            default             => 'لديك إشعار متعلق بطلب التصنيع.',
        };
    }

    protected function dbTitle(): string
    {
        return match ($this->type) {
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
