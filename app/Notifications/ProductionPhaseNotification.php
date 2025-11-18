<?php

namespace App\Notifications;

use App\Models\ProductionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Enums\ProductionRequestPhase as Phase;

class ProductionPhaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected int $prId;
    protected string $event;
    protected array $context;


    public function __construct(int|ProductionRequest $prId, string $event, array $context = [])
    {
        if ($prId instanceof ProductionRequest) {
            $this->prId = (int) $prId->getKey();
        } else {
            $this->prId = (int) $prId;
        }

        $this->event   = $event;
        $this->context = $context;
    }

    public function via($notifiable): array
    {
        $channels = ['database'];

        if (! empty($notifiable->email)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toDatabase($notifiable): array
    {
        $pr = $this->pr();

        return [
            'title'   => $this->title($pr),
            'body'    => $this->bodyText($pr),
            'url'     => $this->timelineUrl($pr),
            'icon'    => 'heroicon-o-clipboard-document-check',
            'color'   => $this->color(),
            'event'   => $this->event,
            'context' => $this->context,
            'pr_id'   => $this->prId,
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $pr = $this->pr();

        return (new MailMessage)
            ->subject($this->mailSubject($pr))
            ->line($this->bodyText($pr))
            ->action('عرض طلب التصنيع', $this->timelineUrl($pr));
    }

    protected function pr(): ProductionRequest
    {
        return ProductionRequest::findOrFail($this->prId);
    }

    protected function mailSubject(ProductionRequest $pr): string
    {
        return match ($this->event) {
            'transition'        => "تحديث مرحلة لطلب التصنيع #{$pr->id}",
            'received'          => "تأكيد استلام لطلب التصنيع #{$pr->id}",
            'rejected'          => "رفض طلب التصنيع #{$pr->id}",
            'factory_rejected'  => "رفض طلب التصنيع من إدارة المصنع #{$pr->id}",
            'project_bootstrap' => "تم إنشاء مشروع من طلب التصنيع #{$pr->id}",
            default             => "إشعار جديد لطلب التصنيع #{$pr->id}",
        };
    }

    protected function title(ProductionRequest $pr): string
    {
        return $this->mailSubject($pr);
    }

    protected function bodyText(ProductionRequest $pr): string
    {
        $phaseLabel  = $this->context['phase_label'] ?? null;
        $statusLabel = $this->context['to_label'] ?? null;

        return match ($this->event) {
            'transition'        => $this->transitionBody($phaseLabel, $statusLabel),
            'received'          => 'تم تأكيد استلام الطلب في المرحلة الحالية.',
            'rejected'          => 'تم رفض الطلب من الجهة المسؤولة.',
            'factory_rejected'  => 'قام مدير المصنع برفض الطلب وإعادته للجهة السابقة.',
            'project_bootstrap' => 'تم إنشاء مشروع ومهام مرتبطة بهذا الطلب.',
            default             => 'لديك إشعار جديد يخص طلب التصنيع.',
        };
    }

    protected function transitionBody(?string $phaseLabel, ?string $statusLabel): string
    {
        if ($phaseLabel && $statusLabel) {
            return "تم تحديث حالة الطلب في مرحلة {$phaseLabel} إلى حالة {$statusLabel}.";
        }

        if ($phaseLabel) {
            return "تم تحديث حالة الطلب في مرحلة {$phaseLabel}.";
        }

        return 'تم تحديث مرحلة طلب التصنيع.';
    }

    protected function color(): string
    {
        return match ($this->event) {
            'received'                      => 'success',
            'rejected', 'factory_rejected'  => 'danger',
            'project_bootstrap'             => 'success',
            'transition'                    => 'info',
            default                         => 'info',
        };
    }




    protected function timelineUrl(ProductionRequest $pr): string
    {
        $base = config('app.url') ?: url('/');

        return rtrim($base, '/') . "/admin/production-requests/{$pr->id}/timeline";
    }

    public function getTitle(): string
    {
        return $this->title($this->pr());
    }

    public function getBody(): string
    {
        return $this->bodyText($this->pr());
    }

    public function getColor(): string
    {
        return $this->color();
    }

    public function getUrl(): string
    {
        return $this->timelineUrl($this->pr());
    }
}
