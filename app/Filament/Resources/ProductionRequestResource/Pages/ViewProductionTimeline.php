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
    public array $timeline = [];

    protected array $i18n = [
        'roles' => [
            'factory_manager'      => 'مدير المصنع',
            'showroom_manager'     => 'مدير المعرض',
            'purchasing_manager'   => 'مدير المشتريات',
            'sales'        => 'مدير المبيعات',
            'quality_manager'      => 'مدير الجودة',
            'manufacturing_manager'=> 'مدير التصنيع',
            'installer'            => 'فني التركيب',
        ],
        'keys' => [
            'phase'          => 'المرحلة',
            'status'         => 'الحالة',
            'from'           => 'من',
            'to'             => 'إلى',
            'owner_role'     => 'المالك',
            'wait_seconds'   => 'مدة الانتظار',
            'project_id'     => 'رقم المشروع',
            'files_created'  => 'عدد الملفات',
            'tasks_created'  => 'عدد المهام',
            'note'           => 'ملاحظة',
        ],
    ];

    public static function canAccess(array $parameters = []): bool
    {
        return Auth::user()?->can('access_view_production_timeline') ?? false;
    }

    public function mount(ProductionRequest $record): void
    {
        app()->setLocale('ar');
        Carbon::setLocale('ar');

        $this->record = $record->load([
            'logs.causer',
            'client',
            'showroom',
            'files.department',
        ]);

        $this->timeline = $this->record->logs
            ->map(fn ($log) => $this->mapLogToTimelineRow($log))
            ->sortByDesc(fn ($row) => $row['sort_key'])
            ->values()
            ->all();
    }

    public function getHeaderActions(): array
    {
        return [
//            Action::make('update_status')
//                ->label('تحديث حالة الطلب')
//                ->icon('heroicon-o-arrow-path')
//                ->form([
//                    Select::make('status')
//                        ->label('الحالة')
//                        ->options(ProductionRequestStatus::options())
//                        ->default(fn () => (string) $this->record->status)
//                        ->required()
//                        ->reactive(),
//                    Textarea::make('note')
//                        ->label('ملاحظة')
//                        ->nullable()
//                        ->helperText('اختياري، سيُحفظ في الحقلين note و data.note'),
//                ])
//                ->action(function (array $data): void {
//                    $this->updateStatus($data['status'], $data['note'] ?? null);
//                    Notification::make()->title('تم تحديث الحالة بنجاح')->success()->send();
//                }),

            Action::make('confirmReceipt')
                ->label('تأكيد استلامي')
                ->icon('heroicon-o-hand-thumb-up')
                ->visible(fn () => $this->userHasRole($this->record->current_owner_role))
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
        $at = $log->happened_at ?? $log->created_at;
        $atC = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);
        $atDate  = $atC?->isoFormat('YYYY-MM-DD HH:mm') ?? '—'; // يبقى شكل التاريخ نفسه
        $atHuman = $atC?->diffForHumans() ?? '—';               // ← عربي بعد setLocale
        $sortKey = $atC?->timestamp ?? 0;

        $type  = (string) ($log->type ?? 'event');
        $data  = $this->asArray($log->data);
        $note  = $log->note;
        $who = $log->causer?->name
            ?? ($this->asArray($log->data)['actor_name'] ?? null)
            ?? ($log->causer_id ? ('مستخدم #' . $log->causer_id) : 'النظام');

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
        $filesCount  = $data['files_created']   ?? null;
        $tasksCount  = $data['tasks_created']   ?? null;

        $fromPhaseL  = $fromPhase ? $this->phaseLabel($fromPhase) : null;
        $toPhaseL    = $toPhase   ? $this->phaseLabel($toPhase)   : null;
        $fromStatusL = $fromStatus? $this->statusLabel($fromStatus): null;
        $toStatusL   = $toStatus  ? $this->statusLabel($toStatus)  : null;
        $ownerRoleL  = $this->ownerRoleLabel($ownerRole);

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
                    $ownerRoleL ? " | المالك: {$ownerRoleL}" : ''
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
                    $desc .= ' — مدة الانتظار: ' . $this->secondsToHuman((int) $data['wait_seconds']);
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
                $desc  = 'رقم المشروع: '.((string)($projectId ?? '—'))
                    .' — عدد الملفات: '.((string)($filesCount ?? 0))
                    .' — عدد المهام: '.((string)($tasksCount ?? 0));
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
                // إن كانت data غير معروفة البنية، نحاول وصفها عربيًا بدل JSON
                $desc  = $note ?: $this->describeData($data);
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
            'at_human'  => $atHuman, // ← “قبل دقيقة / منذ ساعة …”
            'sort_key'  => $sortKey * 1000 + (int) $log->id,
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

    private function ownerRoleLabel(?string $role): ?string
    {
        if (!$role) return null;
        return $this->i18n['roles'][$role] ?? $role;
    }

    private function describeData(array $data): string
    {
        if (empty($data)) return '—';

        $parts = [];
        if (isset($data['from']) || isset($data['to'])) {
            $from = $data['from'] ?? null;
            $to   = $data['to']   ?? null;

            if ($from || $to) {
                $fromStr = [];
                if ($from['phase']  ?? false)  $fromStr[] = $this->phaseLabel((string)$from['phase']);
                if ($from['status'] ?? false)  $fromStr[] = $this->statusLabel((string)$from['status']);

                $toStr   = [];
                if ($to['phase']    ?? false)  $toStr[]   = $this->phaseLabel((string)$to['phase']);
                if ($to['status']   ?? false)  $toStr[]   = $this->statusLabel((string)$to['status']);

                if ($fromStr || $toStr) {
                    $parts[] = 'من: ' . (implode(' / ', $fromStr) ?: '—') . ' → إلى: ' . (implode(' / ', $toStr) ?: '—');
                }
            }
        }

        // مفاتيح شائعة أخرى
        $map = [
            'phase'         => fn($v) => $this->phaseLabel((string)$v),
            'status'        => fn($v) => $this->statusLabel((string)$v),
            'owner_role'    => fn($v) => $this->ownerRoleLabel((string)$v) ?? (string)$v,
            'project_id'    => fn($v) => (string)$v,
            'files_created' => fn($v) => (string)$v,
            'tasks_created' => fn($v) => (string)$v,
            'wait_seconds'  => fn($v) => $this->secondsToHuman((int)$v),
            'note'          => fn($v) => (string)$v,
        ];

        foreach ($map as $k => $format) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $label = $this->i18n['keys'][$k] ?? $k;
                $parts[] = $label . ': ' . $format($data[$k]);
            }
        }

        return $parts ? implode(' | ', $parts) : json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    private function userHasRole(?string $role): bool
    {
        if (!$role) return false;
        $u = Auth::user();
        return $u && method_exists($u, 'hasRole') && $u->hasRole((string) $role, 'web');
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
