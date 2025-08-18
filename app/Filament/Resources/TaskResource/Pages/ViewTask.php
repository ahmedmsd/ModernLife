<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Infolists\Components\Grid;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Actions;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TaskLog;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        $this->record->load([
            'project:id,project_name',
            'department:dept_id,dept_name',
            'employee:employee_id,employee_name,user_id',
            'logs.causer:id,name',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return url("/admin/projects/{$this->record->project_id}/manage-tasks");
    }

    /** هل المستخدم الحالي هو المسؤول عن المهمة؟ */
    protected function isAssignee(): bool
    {
        // إن كان لديك عمود مباشر مثل assigned_to_user_id استخدمه: $this->record->assigned_to_user_id === Auth::id()
        $employeeUserId = $this->record->employee?->user_id;
        return $employeeUserId && $employeeUserId === Auth::id();
    }

    private function statusAr(?string $val): ?string
    {
        if ($val === null) return null;

        $map = [
            'pending'       => 'قيد الإنشاء',
            'assigned'      => 'مُسندة',
            'acknowledged'  => 'تأكيد الاستلام',
            'in_progress'   => 'قيد التنفيذ',
            'blocked'       => 'متوقفة مؤقتًا',
            'under_review'  => 'قيد المراجعة',
            'rework'        => 'إعادة عمل',
            'completed'     => 'مكتملة',
            'closed'        => 'مغلقة',
            'cancelled'     => 'ملغاة',
            'draft'         => 'مسودة',
        ];

        return $map[$val] ?? $val;
    }

    private function statusColor(?string $val): string
    {
        return match ($val) {
            'pending', 'draft'           => 'gray',
            'assigned', 'acknowledged'   => 'warning',
            'in_progress'                => 'info',
            'blocked', 'rework'          => 'purple',
            'under_review'               => 'cyan',
            'completed', 'closed'        => 'success',
            'cancelled'                  => 'danger',
            default                      => 'gray',
        };
    }

    /** تعريف أزرار الإجراءات في رأس صفحة العرض */
    protected function getHeaderActions(): array
    {
        // لإخفاء الكل لو المستخدم ليس المكلّف
        if (! $this->isAssignee()) {
            return [];
        }

        return [
            // إيقاف مؤقت
            Actions\Action::make('pause')
                ->label('إيقاف مؤقت')
                ->icon('heroicon-m-pause')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['assigned', 'acknowledged', 'in_progress']))
                ->action(function () {
                    $this->changeStatus('blocked', note: 'إيقاف مؤقت بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم إيقاف المهمة مؤقتًا.')->success()->send();
                }),

            // استئناف
            Actions\Action::make('resume')
                ->label('استئناف')
                ->icon('heroicon-m-play')
                ->color('success')
                ->visible(fn () => $this->record->status === 'blocked')
                ->action(function () {
                    $this->changeStatus('in_progress', note: 'استئناف التنفيذ بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم استئناف المهمة.')->success()->send();
                }),

            // إرسال للمراجعة
            Actions\Action::make('send_for_review')
                ->label('إرسال للمراجعة')
                ->icon('heroicon-m-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['in_progress', 'rework']))
                ->action(function () {
                    $this->changeStatus('under_review', note: 'تم الإرسال للمراجعة');
                    Notification::make()->title('تم إرسال المهمة للمراجعة.')->success()->send();
                }),

            // إكمال سريع
            Actions\Action::make('quick_complete')
                ->label('إكمال سريع')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => in_array($this->record->status, ['under_review', 'in_progress']))
                ->action(function () {
                    $this->changeStatus('completed', note: 'إكمال سريع بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم إكمال المهمة.')->success()->send();
                }),
        ];
    }

    /**
     * تغيير الحالة مع إنشاء سجل في الخط الزمني.
     * يتوقع وجود علاقة logs() وعمود happened_at وtype وdata(JSON).
     */
    protected function changeStatus(string $to, ?string $note = null): void
    {
        DB::transaction(function () use ($to, $note) {
            $from = $this->record->status;

            // تحديث الحالة
            $this->record->update([
                'status' => $to,
            ]);

            // إنشاء سجل timeline
            if (method_exists($this->record, 'logs')) {
                $this->record->logs()->create([
                    'type'        => 'status_changed',
                    'happened_at' => now(),
                    'data'        => [
                        'from'  => $from,
                        'to'    => $to,
                        'note'  => $note,
                        'by'    => Auth::user()?->name,
                    ],
                ]);
            }
        });

        // إعادة تحميل السجل لتحديث العرض الفوري
        $this->record->refresh();
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('بيانات المهمة')
                ->schema([
                    TextEntry::make('id')->label('رقم المهمة'),
                    TextEntry::make('project.project_name')->label('المشروع')->placeholder('—'),
                    TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                    TextEntry::make('employee.employee_name')->label('الموظف المسؤول')->placeholder('—'),

                    TextEntry::make('status')
                        ->label('الحالة')
                        ->formatStateUsing(fn ($state) => $this->statusAr($state))
                        ->badge()
                        ->color(fn ($state) => $this->statusColor($state))
                        ->placeholder('—'),

                    TextEntry::make('due_date')
                        ->label('تاريخ التسليم')
                        ->date()
                        ->badge()
                        ->color(function ($state) {
                            if (blank($state)) return 'gray';
                            $due = $state instanceof Carbon ? $state : Carbon::parse($state);
                            return now()->gt($due) ? 'danger' : 'success';
                        })
                        ->placeholder('—'),

                    TextEntry::make('assigned_at')
                        ->label('تاريخ الإسناد')
                        ->dateTime()
                        ->placeholder('—'),
                ])->columns(2),

            Section::make('الخط الزمني')
                ->schema([
                    TextEntry::make('timeline_html')
                        ->label('')
                        ->html()
                        ->columnSpanFull()
                        ->state(function ($record) {
                            // الأحدث أولًا
                            $logs = $record->logs()
                                ->with('causer:id,name')
                                ->orderByDesc('happened_at')
                                ->orderByDesc('created_at')
                                ->get(['id','task_id','type','data','causer_id','happened_at','created_at']);

                            if ($logs->isEmpty()) {
                                return '<div style="opacity:.7">لا توجد عمليات بعد.</div>';
                            }

                            $statusMap = [
                                'pending'=>'قيد الإنشاء','assigned'=>'مُسندة','acknowledged'=>'تأكيد الاستلام',
                                'in_progress'=>'قيد التنفيذ','blocked'=>'متوقفة مؤقتًا','under_review'=>'قيد المراجعة',
                                'rework'=>'إعادة عمل','completed'=>'مكتملة','closed'=>'مغلقة','cancelled'=>'ملغاة','draft'=>'مسودة',
                            ];

                            // اختيار لون البطاقة حسب الحالة "to" أو حسب نوع الحدث
                            $statusColor = function (?string $to, ?string $type): string {
                                return match ($to ?? '') {
                                    'completed', 'closed'        => '#22c55e', // أخضر
                                    'blocked', 'cancelled'       => '#ef4444', // أحمر
                                    'under_review'               => '#06b6d4', // سماوي
                                    'in_progress'                => '#f59e0b', // برتقالي
                                    'rework'                     => '#a855f7', // بنفسجي
                                    'acknowledged', 'assigned'   => '#3b82f6', // أزرق
                                    default => match ($type ?? '') {
                                        'assigned_changed' => '#f59e0b',
                                        'due_changed'      => '#a855f7',
                                        'status_changed'   => '#38bdf8',
                                        default            => '#9ca3af',
                                    },
                                };
                            };

                            $cards = [];

                            foreach ($logs as $i => $curr) {
                                $next   = $logs[$i + 1] ?? null;
                                $tsCurr = $curr->happened_at ?: $curr->created_at;
                                $tsNext = $next ? ($next->happened_at ?: $next->created_at) : now();

                                // data → array آمنة
                                $data = is_array($curr->data) ? $curr->data
                                    : (is_string($curr->data)
                                        ? (json_decode($curr->data, true) ?? ['raw' => $curr->data])
                                        : (array) $curr->data);

                                $type   = $curr->type ?? 'event';
                                $from   = $data['from'] ?? null;
                                $to     = $data['to']   ?? null;
                                $note   = $data['note'] ?? ($data['reason'] ?? ($data['message'] ?? ($data['raw'] ?? null)));
                                $byName = $curr->causer?->name;

                                $date     = $tsCurr ? Carbon::parse($tsCurr)->format('H:i:s Y-m-d') : '—';
                                $duration = $tsCurr
                                    ? Carbon::parse($tsCurr)->diffForHumans(Carbon::parse($tsNext), true)
                                    : '—';

                                $details = '—';
                                if ($type === 'status_changed' && ($from || $to)) {
                                    $fromAr = $from ? ($statusMap[$from] ?? $from) : '—';
                                    $toAr   = $to   ? ($statusMap[$to]   ?? $to)   : '—';
                                    $details = "من: {$fromAr} → إلى: {$toAr}";
                                }
                                if ($note) {
                                    $details = ($details !== '—' ? $details.'<br>' : '') . e($note);
                                }

                                $badgeColor = $statusColor($to, $type);
                                $badgeText  = e($type);
                                $byHtml     = $byName ? '<span style="opacity:.8">بواسطة: '.e($byName).'</span>' : '';

                                $cards[] = <<<HTML
                        <div style="padding:16px 18px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:14px;">
                            <div style="display:flex; gap:14px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:8px;">
                                <div style="display:flex; gap:10px; align-items:center;">
                                    <span style="display:inline-block; padding:4px 10px; border-radius:9999px; background:{$badgeColor}22; color:{$badgeColor}; font-size:12px; font-weight:600;">
                                        {$badgeText}
                                    </span>
                                    <div style="font-weight:700;">{$date}</div>
                                </div>
                                {$byHtml}
                            </div>
                            <div style="opacity:.95; margin-bottom:8px; line-height:1.7;">{$details}</div>
                            <div style="opacity:.7; font-size:12px;">المدة حتى الحدث التالي: {$duration}</div>
                        </div>
                    HTML;
                            }

                            $rowsHtml = implode('', $cards);

                            return <<<HTML
                    <div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">
                        {$rowsHtml}
                    </div>
                HTML;
                        }),
                ]),

            Section::make('إحصائيات')
                ->schema([
                    TextEntry::make('total_time')
                        ->label('إجمالي الوقت منذ أول حدث')
                        ->state(function ($record) {
                            $firstAt = $record->logs()->min('happened_at');
                            if (!$firstAt) return '—';

                            $lastAt = $record->logs()->max('happened_at') ?? now();
                            return Carbon::parse($firstAt)->diffForHumans(Carbon::parse($lastAt), true);
                        }),

                    TextEntry::make('is_late')
                        ->label('هل المهمة متأخرة؟')
                        ->badge()
                        ->state(function ($record) {
                            if (blank($record->due_date)) return '—';
                            if ($record->status === 'completed') return 'مكتملة';
                            $due = $record->due_date instanceof Carbon ? $record->due_date : Carbon::parse($record->due_date);
                            return now()->gt($due) ? 'متأخرة' : 'ضمن الوقت';
                        })
                        ->color(fn ($state) => match ($state) {
                            'متأخرة'     => 'danger',
                            'مكتملة'     => 'gray',
                            'ضمن الوقت'  => 'success',
                            default       => 'gray',
                        }),
                ])->columns(2),
        ]);
    }
}
