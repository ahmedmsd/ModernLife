<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Enums\{ProductionRequestPhase as Phase, PhaseStatus as S, ProductionRequestStatus};
use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Services\ProductionRequestWorkflow;

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view     = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title   = 'معلومات الطلب التفصيلية';

    public ProductionRequest $record;

    /** عناصر الخط الزمني الجاهزة للعرض */
    public array $timeline = [];

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('access_view_production_timeline') ?? false;
    }

    public function mount(ProductionRequest $record): void
    {
        $this->record = $record->load([
            'logs.causer',
            'client',
            'showroom',
            'files.department',
        ]);

        $this->timeline = $this->record->logs
            ->map(fn ($log) => $this->mapLogToTimelineRow($log))
            // الأحدث أولاً: بالأساس على happened_at ثم id
            ->sortByDesc(fn ($row) => $row['sort_key'])
            ->values()
            ->all();
    }

    /* ======================== Actions اختيارية (إبقاء ما كان لديك) ======================== */

    public function getHeaderActions(): array
    {
        return [
            Action::make('update_status')
                ->label('تحديث حالة الطلب')
                ->icon('heroicon-o-arrow-path')
                ->form([
                    Select::make('status')
                        ->label('الحالة')
                        ->options(ProductionRequestStatus::options())
                        ->default(fn () => (string) $this->record->status)
                        ->required()
                        ->reactive(),
                    Textarea::make('note')
                        ->label('ملاحظة')
                        ->nullable()
                        ->helperText('اختياري، سيُحفظ في الحقلين note و data.note'),
                ])
                ->action(function (array $data): void {
                    $this->updateStatus($data['status'], $data['note'] ?? null);
                    Notification::make()->title('تم تحديث الحالة بنجاح')->success()->send();
                }),

            Action::make('confirmReceipt')
                ->label('تأكيد استلامي')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn () => Auth::user()?->hasRole($this->record->current_owner_role))
                ->action(function () {
                    app(ProductionRequestWorkflow::class)->markReceived($this->record);
                    Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                    $this->refreshRecord();
                }),
        ];
    }

    protected function updateStatus(string $newValue, ?string $note): void
    {
        $current = (string) $this->record->status;

        if ($current !== $newValue) {
            $this->record->update(['status' => $newValue]);

            $this->record->logs()->create([
                'type'        => 'status_changed',
                'data'        => [
                    'from' => $current,
                    'to'   => $newValue,
                    'note' => $note,
                ],
                'note'        => $note
                    ?? 'تم تغيير الحالة إلى: ' . (ProductionRequestStatus::tryFrom($newValue)?->label() ?? $newValue),
                'causer_id'   => Auth::id(),
                'happened_at' => now(),
            ]);

            $this->refreshRecord();
        }
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['logs.causer','client','showroom','files.department']);
        $this->mount($this->record);
    }

    /* ============================== Helpers ============================== */

    private function mapLogToTimelineRow($log): array
    {
        // توقيت موحّد
        $at = $log->happened_at ?? $log->created_at;
        $atC = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);
        $atDate = $atC?->format('Y-m-d H:i') ?? '—';
        $atHuman = $atC?->diffForHumans() ?? '—';
        $sortKey = $atC?->timestamp ?? 0;

        $type  = (string) ($log->type ?? 'event');
        $data  = $this->asArray($log->data);
        $note  = $log->note;
        $who   = $log->causer->name ?? 'مجهول';

        $title   = '';
        $desc    = '';
        $icon    = 'heroicon-o-information-circle';
        $color   = 'gray';

        // قراءة الحقول الشائعة
        $fromPhase   = $data['from']['phase']   ?? null;
        $fromStatus  = $data['from']['status']  ?? ($data['from_status'] ?? null);
        $toPhase     = $data['to']['phase']     ?? null;
        $toStatus    = $data['to']['status']    ?? ($data['to_status'] ?? null);
        $ownerRole   = $data['owner_role']      ?? null;
        $projectId   = $data['project_id']      ?? null;
        $filesCount  = $data['files_created']    ?? null;
        $tasksCount  = $data['tasks_created']    ?? null;

        // تحويل للأسماء المترجمة
        $fromPhaseL = $fromPhase ? $this->phaseLabel($fromPhase) : null;
        $toPhaseL   = $toPhase   ? $this->phaseLabel($toPhase)   : null;
        $fromStatusL= $fromStatus? $this->statusLabel($fromStatus): null;
        $toStatusL  = $toStatus  ? $this->statusLabel($toStatus)  : null;

        switch ($type) {
            case 'created':
                $title = 'تم إنشاء الطلب';
                $desc  = $note ?: 'تم إنشاء طلب التصنيع.';
                $icon  = 'heroicon-o-document-plus';
                $color = 'primary';
                break;

            case 'transition':
                $title = 'انتقال مرحلة';
                $icon  = 'heroicon-o-arrow-right';
                $color = 'info';
                $desc  = sprintf(
                    'من "%s" (%s) إلى "%s" (%s)%s',
                    $fromPhaseL ?? '—',
                    $fromStatusL ?? '—',
                    $toPhaseL   ?? '—',
                    $toStatusL  ?? '—',
                    $ownerRole ? " | المالك: {$ownerRole}" : ''
                );
                if ($note) $desc .= " — {$note}";
                break;

            case 'received':
                $title = 'تأكيد استلام';
                $icon  = 'heroicon-o-hand-thumb-up';
                $color = 'success';
                $desc  = sprintf(
                    'تم الاستلام في "%s" (من %s إلى %s).',
                    $this->phaseLabel($data['phase'] ?? ($toPhase ?? $fromPhase ?? '')),
                    $this->statusLabel($fromStatus ?? 'pending'),
                    $this->statusLabel($toStatus   ?? 'received')
                );
                if (isset($data['wait_seconds'])) {
                    $desc .= ' مدة الانتظار: ' . $this->secondsToHuman($data['wait_seconds']);
                }
                if ($note) $desc .= " — {$note}";
                break;

            case 'rejected':
                $title = 'رفض الطلب';
                $icon  = 'heroicon-o-x-circle';
                $color = 'danger';
                $desc  = sprintf(
                    'في "%s" (من %s إلى %s).',
                    $this->phaseLabel($data['phase'] ?? ($fromPhase ?? '')),
                    $this->statusLabel($fromStatus ?? '—'),
                    $this->statusLabel($toStatus   ?? 'rejected')
                );
                if ($note) $desc .= " — السبب: {$note}";
                break;

            case 'status_changed':
                $title = 'تغيير الحالة العامة';
                $icon  = 'heroicon-o-adjustments-vertical';
                $color = 'warning';
                $desc  = sprintf(
                    'من %s إلى %s.',
                    $this->statusLabel($data['from'] ?? '—'),
                    $this->statusLabel($data['to']   ?? '—')
                );
                if ($note) $desc .= " — {$note}";
                break;

            case 'project_bootstrap':
                $title = 'إنشاء مشروع ومهام الأقسام';
                $icon  = 'heroicon-o-briefcase';
                $color = 'success';
                $desc  = "مشروع #{$projectId} — ملفات: " . ((string)($filesCount ?? 0)) . " | مهام: " . ((string)($tasksCount ?? 0));
                if ($note) $desc .= " — {$note}";
                break;

            case 'sent_to_factory':
                $title = 'إرسال إلى المصنع';
                $icon  = 'heroicon-o-paper-airplane';
                $color = 'info';
                $desc  = 'تم إرسال الطلب إلى مدير المصنع.' . ($note ? " — {$note}" : '');
                break;

            default:
                $title = $this->typeLabel($type);
                $desc  = $note ?: (empty($data) ? '—' : json_encode($data, JSON_UNESCAPED_UNICODE));
                $icon  = 'heroicon-o-information-circle';
                $color = 'gray';
                break;
        }

        return [
            'id'        => $log->id,
            'who'       => $who,
            'title'     => $title,
            'desc'      => $desc,
            'icon'      => $icon,
            'color'     => $color,
            'at'        => $atDate,
            'at_human'  => $atHuman,
            'sort_key'  => $sortKey * 1000 + (int) $log->id, // لضمان استقرار الترتيب
        ];
    }

    private function asArray($val): array
    {
        if (is_array($val)) return $val;
        if (is_object($val)) return (array) $val;
        if (is_string($val)) {
            try { $d = json_decode($val, true, 512, JSON_THROW_ON_ERROR); return is_array($d) ? $d : []; }
            catch (\Throwable) { return []; }
        }
        return [];
    }

    private function secondsToHuman(?int $s): string
    {
        if ($s === null) return '—';
        $h = intdiv($s, 3600);
        $m = intdiv($s % 3600, 60);
        $sec = $s % 60;
        $parts = [];
        if ($h)   $parts[] = "{$h} ساعة";
        if ($m)   $parts[] = "{$m} دقيقة";
        if ($sec || empty($parts)) $parts[] = "{$sec} ثانية";
        return implode(' و ', $parts);
    }

    private function phaseLabel(string $phase): string
    {
        return match ($phase) {
            'sales_intake'               => 'استلام المبيعات',
            'showroom_review'            => 'مراجعة المعرض',
            'factory_intake'             => 'استلام المصنع',
            'department_assignment'      => 'إسناد الأقسام',
            'purchasing'                 => 'المشتريات',
            'manufacturing'              => 'التصنيع',
            'quality_after_manufacture'  => 'جودة ما بعد التصنيع',
            'installation'               => 'التركيب',
            'quality_after_installation' => 'جودة ما بعد التركيب',
            'closed'                     => 'مغلق',
            default                      => $phase,
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'pending'        => 'قيد الانتظار',
            'received'       => 'تم الاستلام',
            'under_review'   => 'قيد المراجعة',
            'approved'       => 'معتمد',
            'rejected'       => 'مرفوض',
            'in_progress'    => 'قيد التنفيذ',
            'materials_prep' => 'تحضير الخامات',
            'materials_done' => 'تم توفير الخامات',
            'on_hold'        => 'معلق',
            'completed'      => 'مكتمل',
            'cancelled'      => 'ملغي',
            default          => $status,
        };
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'created'            => 'تم الإنشاء',
            'transition'         => 'انتقال مرحلة',
            'received'           => 'تأكيد استلام',
            'rejected'           => 'رفض الطلب',
            'status_changed'     => 'تغيير الحالة العامة',
            'project_bootstrap'  => 'تهيئة مشروع',
            'sent_to_factory'    => 'إرسال إلى المصنع',
            default              => $type,
        };
    }
}
