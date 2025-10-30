<?php

namespace App\Filament\Resources\ProductionRequestResource\Pages;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class ViewProductionTimeline extends Page
{
    protected static string $resource = ProductionRequestResource::class;
    protected static string $view = 'filament.resources.production-request-resource.pages.view-production-timeline';
    protected static ?string $title = 'عرض معلومات طلب التصنيع';

    private array $i18n = [
        'roles' => [
            'factory_manager'       => 'مدير المصنع',
            'showroom_manager'      => 'مدير المعرض',
            'purchasing_manager'    => 'مدير المشتريات',
            'sales'                 => 'مدير المبيعات',
            'quality_manager'       => 'مدير الجودة',
            'manufacturing_manager' => 'مدير التصنيع',
            'installer'             => 'فني التركيب',
            'department_manager'    => 'مدير القسم',
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
            // حقول شائعة إضافية قد تظهر داخل data لبعض الأحداث
            'expected_delivery_at' => 'توريد متوقع',
            'provided_at'          => 'توريد فعلي',
            'planned_start_at'     => 'بداية التصنيع (متوقعة)',
            'planned_end_at'       => 'نهاية التصنيع (متوقعة)',
            'actual_start_at'      => 'بداية التصنيع (فعلية)',
            'actual_end_at'        => 'نهاية التصنيع (فعلية)',
        ],
    ];

    public ProductionRequest $record;

    public array $summary = [];

    public array $timeline = [];

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
            'project.tasks',
        ]);

        $this->summary = $this->buildSummary($this->record);

        $this->timeline = $this->record->logs
            ->map(fn ($log) => $this->mapLogToTimelineRow($log))
            ->sortByDesc(fn ($row) => $row['sort_key'])
            ->values()
            ->all();
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('openReview')
                ->label('فتح صفحة المراجعة')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->url(ProductionRequestResource::getUrl('review', ['record' => $this->record])),
        ];
    }

    private function refreshRecord(): void
    {
        $this->record->refresh()->load(['logs.causer','client','showroom','files.department','project.tasks']);
        $this->mount($this->record);
    }

    /* ============================== Summary ============================== */


    private function buildSummary(ProductionRequest $pr): array
    {
        $fmt = fn (?Carbon $c) => $c?->format('Y-m-d H:i') ?? '—';
        $h   = fn (?Carbon $a, ?Carbon $b) => $this->humanDiff($a, $b);

        $expected = $pr->expected_delivery_at ? Carbon::parse($pr->expected_delivery_at) : null;
        $provided = $pr->provided_at          ? Carbon::parse($pr->provided_at)          : null;
        $created  = $pr->created_at           ? Carbon::parse($pr->created_at)           : null;
        $approved = $pr->approved_at          ? Carbon::parse($pr->approved_at)          : null;

        $startLog = $this->firstLog($pr, 'manufacturing_started');
        $endLog   = $this->firstLog($pr, 'manufacturing_finished');
        $clientR  = $this->firstLog($pr, 'client_receipt_uploaded');

        $actualStart = $startLog?->happened_at ?? $startLog?->created_at;
        $actualEnd   = $endLog?->happened_at   ?? $endLog?->created_at;
        $clientAt    = $clientR?->happened_at  ?? $clientR?->created_at;

        $expectedVsActual = '—';
        if ($expected && $provided) {
            $mins = $expected->diffInMinutes($provided, false);
            $abs  = abs($mins);
            $expectedVsActual = $mins === 0 ? 'في الموعد تمامًا'
                : ($mins < 0 ? 'أبكر بـ ' : 'متأخر بـ ') . $this->minutesToHuman($abs);
        }

        return [
            'expected_delivery_at'   => $fmt($expected),
            'provided_at'            => $fmt($provided),
            'expected_vs_actual'     => $expectedVsActual,

            'created_to_provided'    => $h($created,  $provided), // من إنشاء الطلب حتى التوريد
            'approved_to_provided'   => $h($approved, $provided), // من اعتماد المشتريات حتى التوريد

            'manufacturing_duration' => $h(
                $actualStart ? Carbon::parse($actualStart) : null,
                $actualEnd   ? Carbon::parse($actualEnd)   : null
            ),

            'total_to_client'        => $h($created, $clientAt ? Carbon::parse($clientAt) : null),

            'project_open_tasks'     => (int) $this->record->project?->tasks()
                    ->whereNotIn('status', ['completed','cancelled','closed'])->count() ?? 0,
        ];
    }

    private function firstLog(ProductionRequest $pr, string $event): ?object
    {
        return $pr->logs->firstWhere('type', $event);
    }

    private function humanDiff(?Carbon $a, ?Carbon $b): string
    {
        if (!$a || !$b) return '—';
        $minutes = $a->diffInMinutes($b);
        return $this->minutesToHuman($minutes);
    }

    private function minutesToHuman(int $min): string
    {
        $d = intdiv($min, 1440);
        $h = intdiv($min % 1440, 60);
        $m = $min % 60;
        $parts = [];
        if ($d) $parts[] = "{$d} يوم";
        if ($h) $parts[] = "{$h} ساعة";
        if ($m || (!$d && !$h)) $parts[] = "{$m} دقيقة";
        return implode(' و ', $parts);
    }

    /* ============================== Helpers ============================== */

    private function mapLogToTimelineRow($log): array
    {
        $at   = $log->happened_at ?? $log->created_at;
        $atC  = $at instanceof Carbon ? $at : ($at ? Carbon::parse($at) : null);
        $atDate  = $atC?->isoFormat('YYYY-MM-DD HH:mm') ?? '—';
        $atHuman = $atC?->diffForHumans() ?? '—';
        $sortKey = $atC?->timestamp ?? 0;

        $type   = (string) ($log->type ?? 'event');
        $data   = $this->asArray($log->data);
        $note   = $log->note;

        $who = $log->causer?->name
            ?? ($this->asArray($log->data)['actor_name'] ?? null)
            ?? ($log->causer_id ? ('مستخدم #' . $log->causer_id) : 'النظام');

        $title = '';
        $desc  = '';
        $icon  = 'heroicon-o-information-circle';
        $color = 'gray';

        // قراءة حقول شائعة
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

            /* ===== أحداث الـ workflow الجديدة ===== */

            case 'materials_provided':
                $title = 'توفير الخامات';
                $icon  = 'heroicon-o-truck';
                $color = 'orange';
                $desc  = 'تم توريد الخامات للمهمة/المشروع.' . ($note ? " — {$note}" : '');
                break;

            case 'materials_received_ok':
                $title = 'استلام الخامات (القسم)';
                $icon  = 'heroicon-o-hand-thumb-up';
                $color = 'violet';
                $desc  = 'تم تأكيد استلام الخامات من قبل مدير القسم.' . ($note ? " — {$note}" : '');
                break;

            case 'waiting_production':
                $title = 'جاهز لبدء التصنيع';
                $icon  = 'heroicon-o-clock';
                $color = 'amber';
                $desc  = 'المهمة بانتظار بدء التصنيع من مدير القسم.' . ($note ? " — {$note}" : '');
                break;

            case 'manufacturing_started':
                $title = 'بدء التصنيع (فعلي)';
                $icon  = 'heroicon-o-play-circle';
                $color = 'sky';
                $desc  = 'بدأت أعمال التصنيع فعليًا.' . ($note ? " — {$note}" : '');
                break;

            case 'manufacturing_finished':
                $title = 'نهاية التصنيع (فعلي)';
                $icon  = 'heroicon-o-check-circle';
                $color = 'emerald';
                $desc  = 'انتهت أعمال التصنيع.' . ($note ? " — {$note}" : '');
                break;

            case 'manufacturing_sent_to_qa':
                $title = 'إرسال للجودة';
                $icon  = 'heroicon-o-shield-check';
                $color = 'blue';
                $desc  = 'تم إرسال المهمة لفحص الجودة.' . ($note ? " — {$note}" : '');
                break;

            case 'qa_approved_installation':
                $title = 'اعتماد التركيب';
                $icon  = 'heroicon-o-wrench';
                $color = 'teal';
                $desc  = 'تم اعتماد التركيب من الجودة/الجهة المختصة.' . ($note ? " — {$note}" : '');
                break;

            case 'client_receipt_uploaded':
                $title = 'استلام العميل';
                $icon  = 'heroicon-o-arrow-up-on-square';
                $color = 'indigo';
                $desc  = 'تم رفع سند استلام العميل وإقفال المهمة.' . ($note ? " — {$note}" : '');
                break;

            case 'project_completed':
                $title = 'اكتمال المشروع';
                $icon  = 'heroicon-o-flag';
                $color = 'green';
                $desc  = 'أُكمل المشروع لعدم وجود مهام مفتوحة.' . ($note ? " — {$note}" : '');
                break;

            case 'production_request_closed':
                $title = 'إقفال طلب الإنتاج';
                $icon  = 'heroicon-o-lock-closed';
                $color = 'slate';
                $desc  = 'أُقفل طلب الإنتاج المتعلق بالمشروع.' . ($note ? " — {$note}" : '');
                break;

            default:
                $title = $this->typeLabel($type);
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
            'at_human'  => $atHuman,
            'sort_key'  => $sortKey * 1000 + (int) $log->id,
        ];
    }

    private function asArray($val): array
    {
        if (is_array($val)) return $val;
        if (is_object($val)) return (array) $val;
        if (is_string($val)) {
            try {
                $d = json_decode($val, true, 512, JSON_THROW_ON_ERROR);
                return is_array($d) ? $d : [];
            } catch (\Throwable) { return []; }
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

        $map = [
            'phase'             => fn($v) => $this->phaseLabel((string)$v),
            'status'            => fn($v) => $this->statusLabel((string)$v),
            'owner_role'        => fn($v) => $this->ownerRoleLabel((string)$v) ?? (string)$v,
            'project_id'        => fn($v) => (string)$v,
            'files_created'     => fn($v) => (string)$v,
            'tasks_created'     => fn($v) => (string)$v,
            'wait_seconds'      => fn($v) => $this->secondsToHuman((int)$v),
            'note'              => fn($v) => (string)$v,
            'expected_delivery_at' => fn($v) => (string)$v,
            'provided_at'          => fn($v) => (string)$v,
            'planned_start_at'     => fn($v) => (string)$v,
            'planned_end_at'       => fn($v) => (string)$v,
            'actual_start_at'      => fn($v) => (string)$v,
            'actual_end_at'        => fn($v) => (string)$v,
        ];

        foreach ($map as $k => $format) {
            if (array_key_exists($k, $data) && $data[$k] !== null) {
                $label = $this->i18n['keys'][$k] ?? $k;
                $parts[] = $label . ': ' . $format($data[$k]);
            }
        }

        return $parts ? implode(' | ', $parts) : json_encode($data, JSON_UNESCAPED_UNICODE);
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
            'pending'           => 'قيد الانتظار',
            'received'          => 'تم الاستلام',
            'under_review'      => 'قيد المراجعة',
            'approved'          => 'معتمد',
            'rejected'          => 'مرفوض',
            'materials_wait'    => 'بانتظار التوريد',
            'materials_prep'    => 'تحضير الخامات',
            'materials_done'    => 'تم توفير الخامات',
            'waiting_production'=> 'جاهز لبدء التصنيع',
            'in_progress'       => 'قيد التنفيذ',
            'on_hold'           => 'معلّق',
            'completed'         => 'مكتمل',
            'cancelled'         => 'ملغي',
            'closed'            => 'مغلق',
            default             => $status,
        };
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            'created'                     => 'تم الإنشاء',
            'transition'                  => 'انتقال مرحلة',
            'received'                    => 'تأكيد استلام',
            'rejected'                    => 'رفض الطلب',
            'status_changed'              => 'تغيير الحالة العامة',
            'project_bootstrap'           => 'تهيئة مشروع',
            'sent_to_factory'             => 'إرسال إلى المصنع',
            'materials_provided'          => 'توفير الخامات',
            'materials_received_ok'       => 'استلام الخامات (القسم)',
            'waiting_production'          => 'جاهز لبدء التصنيع',
            'manufacturing_started'       => 'بدء التصنيع (فعلي)',
            'manufacturing_finished'      => 'نهاية التصنيع (فعلي)',
            'manufacturing_sent_to_qa'    => 'إرسال للجودة',
            'qa_approved_installation'    => 'اعتماد التركيب',
            'client_receipt_uploaded'     => 'استلام العميل',
            'project_completed'           => 'اكتمال المشروع',
            'production_request_closed'   => 'إقفال طلب الإنتاج',
            default                       => $type,
        };
    }
}
