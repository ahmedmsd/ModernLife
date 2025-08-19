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
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TaskLog;
use App\Models\MaterialRequest;
use Spatie\Permission\Models\Role;

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
            'materialRequests:id,task_id,status,requested_at,provided_at', // لسرعة الفحص
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return url("/admin/projects/{$this->record->project_id}/manage-tasks");
    }

    /** هل المستخدم الحالي هو المسؤول عن المهمة؟ */
    protected function isAssignee(): bool
    {
        $employeeUserId = $this->record->employee?->user_id;
        return $employeeUserId && $employeeUserId === Auth::id();
    }

    /** حالة كسلسلة دائمًا */
    protected function statusVal(): string
    {
        $s = $this->record->status;
        return $s instanceof \BackedEnum ? $s->value : (string) $s;
    }




    protected function openTimeEntryIfNeeded(string $reason = 'status_change'): void
    {
        if (! method_exists($this->record, 'timeEntries')) return;

        $hasOpen = $this->record->timeEntries()->whereNull('ended_at')->exists();
        if ($hasOpen) return;

        $this->record->timeEntries()->create([
            'started_at'   => Carbon::now('UTC'),
            'ended_at'     => null,
            'duration_sec' => 0,
            'reason'       => $reason,
        ]);
    }


    protected function closeActiveTimeEntrySafe(string $reason = 'status_change'): void
    {
        if (! method_exists($this->record, 'timeEntries')) {
            return;
        }

        $entry = $this->record->timeEntries()
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $entry) {
            return;
        }

        // نخزّن وقت الإغلاق بـ UTC دائمًا
        $endedAtUtc = Carbon::now('UTC')->format('Y-m-d H:i:s');

        // تحديث آمن من خلال SQL: يحسب الثواني ويصفّر السالب
        DB::table('production_tasks_time_entries')
            ->where('id', $entry->id)
            ->update([
                'ended_at'     => $endedAtUtc,
                'duration_sec' => DB::raw("GREATEST(TIMESTAMPDIFF(SECOND, started_at, '{$endedAtUtc}'), 0)"),
                'reason'       => $reason,
                'updated_at'   => $endedAtUtc,
            ]);
    }


    protected function hasOpenMaterialsRequest(): bool
    {
        return $this->record->materialRequests()
            ->whereNull('provided_at')
            ->exists();
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
        $status = $this->statusVal();
        $isAssignee = $this->isAssignee();

        return [

            // تأكيد الاستلام (للمكلّف فقط)
            Actions\Action::make('confirmReceipt')
                ->label('تأكيد الاستلام')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () =>
                    $this->isAssignee()
                    && in_array($this->statusVal(), ['pending','assigned'], true)
                    && blank($this->record->received_at)
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'received_at' => now(),
                        'status'      => 'acknowledged',
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->title('تم تأكيد استلام المهمة')
                        ->success()
                        ->send();

                    // تحديث العرض مباشرة
                    $this->record->refresh();
                }),

            // ============================
            // المشتريات / طلب خامات
            // ============================
            Actions\Action::make('requestMaterials')
                ->label('طلب خامات')
                ->icon('heroicon-o-truck')
                ->visible(fn () =>
                    in_array($this->statusVal(), ['in_progress','acknowledged'], true)
                    && ! $this->hasOpenMaterialsRequest()
                    && (
                        $this->isAssignee() // 👈 المكلّف يرى الزر
                        || Auth::user()?->hasAnyRole(['department_manager','admin','super-admin'])
                    )
                )
                ->form([
                    Forms\Components\Textarea::make('note')->label('المطلوبات')->required()->rows(5),
                    Forms\Components\TextInput::make('po_number')->label('رقم طلب الشراء/مرجع (اختياري)'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $task = $this->record;

                    // إنشاء طلب خامات كنص في جدول material_requests
                    MaterialRequest::create([
                        'task_id'       => $task->id,
                        'department_id' => $task->department_id,
                        'requested_by'  => Auth::id(),
                        'requested_at'  => now(),
                        'po_number'     => $data['po_number'] ?? null,
                        'note'          => $data['note'] ?? null,
                    ]);

                    // إيقاف المهمة مؤقتًا
                    $this->changeStatus('blocked', note: 'طلب خامات: ' . ($data['note'] ?? ''));

                    // إشعار مدير المشتريات
                    $this->notifyRole('purchasing_manager',
                        'طلب خامات جديد',
                        "المهمة #{$task->id}: تم إنشاء طلب خامات من مدير القسم.");

                    Notification::make()->title('تم إرسال طلب الخامات إلى المشتريات')->success()->send();
                }),

            // تأكيد توفير الخامات (من داخل صفحة المهمة) — للمشتريات
            Actions\Action::make('confirmMaterials')
                ->label('تأكيد توفير الخامات')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () =>
                    \Illuminate\Support\Facades\Auth::user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && $this->hasOpenMaterialsRequest()
                )
                ->form([
                    Forms\Components\TextInput::make('po_number')->label('رقم الطلب/مرجع (اختياري)'),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $task = $this->record;

                    // آخر طلب مفتوح
                    $req = $task->materialRequests()
                        ->whereNull('provided_at')   // آخر طلب غير مُؤكد
                        ->latest('requested_at')
                        ->first();

                    if (! $req) {
                        Notification::make()->title('لا يوجد طلب خامات مفتوح')->warning()->send();
                        return;
                    }

                    $req->update([
                        'status'      => 'fulfilled',
                        'po_number'   => $data['po_number'] ?? $req->po_number,
                        'provided_by' => Auth::id(),
                        'provided_at' => now(),
                        'note'        => trim(($req->note ? $req->note . "\n\n" : '') . ($data['note'] ?? '')),
                    ]);

                    // إن لم تبقَ طلبات مفتوحة، أعد المهمة للتنفيذ إن كانت blocked
                    $stillOpen = $task->materialRequests()->whereNull('provided_at')->exists();
                    if (! $stillOpen && $this->statusVal() === 'blocked') {
                        $this->changeStatus('in_progress', note: 'تأكيد توفير الخامات من المشتريات');
                    }

                    // إشعار مدير القسم
                    $this->notifyRole('department_manager',
                        'تم توفير الخامات',
                        "المهمة #{$task->id}: تم التأكيد، يمكن استئناف التنفيذ.");

                    Notification::make()->title('تم تأكيد التوفير')->success()->send();
                }),

            // ============================
            // الجودة
            // ============================
            // إرسال للمراجعة (مدير القسم/المكلّف)
            Actions\Action::make('send_for_review')
                ->label('إرسال للمراجعة')
                ->icon('heroicon-m-paper-airplane')
                ->color('info')
                ->requiresConfirmation()
                ->visible(fn () =>
                    in_array($this->statusVal(), ['in_progress','rework'], true)
                    && $isAssignee // أو مدير القسم
                    || (Auth::user()?->hasAnyRole(['department_manager','admin','super-admin']) && in_array($this->statusVal(), ['in_progress','rework'], true))
                )
                ->action(function () {
                    $this->changeStatus('under_review', note: 'إرسال المهمة لقسم الجودة');
                    $this->notifyRole('quality_manager', 'مهمة بانتظار مراجعة الجودة', "المهمة #{$this->record->id}: بانتظار مراجعة الجودة.");
                    Notification::make()->title('تم إرسال المهمة للمراجعة')->success()->send();
                }),

            // اعتماد الجودة (قبول)
            Actions\Action::make('qualityApprove')
                ->label('اعتماد الجودة')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () =>
                    $this->statusVal() === 'under_review'
                    && Auth::user()?->hasAnyRole(['quality_manager','admin','super-admin'])
                )
                ->form([
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    // تغيّر إلى closed بدل completed
                    $this->changeStatus('closed', note: $data['note'] ?? 'اعتماد الجودة وإغلاق المهمة');
                    $this->notifyRole(
                        'department_manager',
                        'تم إغلاق المهمة بعد اعتماد الجودة',
                        "المهمة #{$this->record->id}: تم اعتماد الجودة وإغلاق المهمة."
                    );
                    Notification::make()->title('تم اعتماد الجودة وإغلاق المهمة')->success()->send();
                }),

            // رفض الجودة (إعادة عمل)
            Actions\Action::make('qualityReject')
                ->label('رفض الجودة (إعادة عمل)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () =>
                    $this->statusVal() === 'under_review'
                    && Auth::user()?->hasAnyRole(['quality_manager','admin','super-admin'])
                )
                ->form([
                    Forms\Components\Textarea::make('note')->label('سبب الرفض / المطلوب')->required()->rows(4),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->changeStatus('rework', note: 'رفض الجودة: ' . ($data['note'] ?? ''));
                    $this->notifyRole('department_manager', 'رفض الجودة', "المهمة #{$this->record->id}: مرفوضة وتتطلب إعادة عمل.");
                    Notification::make()->title('تم رفض الجودة وإرجاع المهمة')->success()->send();
                }),

            // ============================
            // أزرار التنفيذ اليومية (للمكلّف فقط)
            // ============================
            Actions\Action::make('pause')
                ->label('إيقاف مؤقت')
                ->icon('heroicon-m-pause')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn () => $this->isAssignee() && in_array($this->statusVal(), ['assigned','acknowledged','in_progress'], true))
                ->action(function () {
                    $this->changeStatus('blocked', note: 'إيقاف مؤقت بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم إيقاف المهمة مؤقتًا')->success()->send();
                }),

            Actions\Action::make('resume')
                ->label('استئناف')
                ->icon('heroicon-m-play')
                ->color('success')
                ->visible(fn () => $this->isAssignee() && $this->statusVal() === 'blocked' && ! $this->hasOpenMaterialsRequest())
                ->action(function () {
                    $this->changeStatus('in_progress', note: 'استئناف التنفيذ بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم استئناف المهمة')->success()->send();
                }),

            Actions\Action::make('quick_complete')
                ->label('إكمال سريع')
                ->icon('heroicon-m-check-badge')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => $this->isAssignee() && in_array($this->statusVal(), ['under_review','in_progress'], true))
                ->action(function () {
                    $this->changeStatus('completed', note: 'إكمال سريع بواسطة المسؤول عن المهمة');
                    Notification::make()->title('تم إكمال المهمة')->success()->send();
                }),
        ];
    }


    protected function changeStatus(string $to, ?string $note = null): void
    {
        DB::transaction(function () use ($to, $note) {
            $from = $this->statusVal();
            if (in_array($to, ['in_progress','rework'], true)) {
                $this->openTimeEntryIfNeeded("status_to_{$to}");
            }
            // لو نغادر وضع التشغيل، أغلق السجل المفتوح بأمان
            if (in_array($from, ['in_progress','rework'], true)
                && in_array($to, ['blocked','under_review','completed','closed','cancelled'], true)) {
                $this->closeActiveTimeEntrySafe("status_to_{$to}");
            }

            // حدِّث الحالة
            $this->record->update(['status' => $to]);

            // لوج التايملاين (كما هو عندك)
            if (method_exists($this->record, 'logs')) {
                $this->record->logs()->create([
                    'type'        => 'status_changed',
                    'happened_at' => now(),
                    'data'        => [
                        'from'  => $from,
                        'to'    => $to,
                        'note'  => $note,
                        'by'    => auth()->user()?->name,
                    ],
                ]);
            }
        });

        $this->record->refresh();
    }


    // إشعار DB لكل مستخدمي دور معين
    protected function notifyRole(string $roleName, string $title, string $body): void
    {
        try {
            $role = Role::findByName($roleName);
            foreach ($role->users as $user) {
                Notification::make()
                    ->title($title)
                    ->body($body)
                    ->sendToDatabase($user);
            }
        } catch (\Throwable $e) {
            // تجاهل إن لم يوجد الدور
        }
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
                        ->formatStateUsing(fn ($state) => $this->statusAr($state instanceof \BackedEnum ? $state->value : $state))
                        ->badge()
                        ->color(fn ($state) => $this->statusColor($state instanceof \BackedEnum ? $state->value : $state))
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

                            $statusColor = function (?string $to, ?string $type): string {
                                return match ($to ?? '') {
                                    'completed', 'closed'        => '#22c55e',
                                    'blocked', 'cancelled'       => '#ef4444',
                                    'under_review'               => '#06b6d4',
                                    'in_progress'                => '#f59e0b',
                                    'rework'                     => '#a855f7',
                                    'acknowledged', 'assigned'   => '#3b82f6',
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
                            if (in_array($record->status instanceof \BackedEnum ? $record->status->value : $record->status, ['completed','closed'], true)) {
                                return ($record->status instanceof \BackedEnum ? $record->status->value : $record->status) === 'closed' ? 'مغلقة' : 'مكتملة';
                            }
                            $due = $record->due_date instanceof \Carbon\Carbon ? $record->due_date : \Carbon\Carbon::parse($record->due_date);
                            return now()->gt($due) ? 'متأخرة' : 'ضمن الوقت';
                        })
                        ->color(fn ($state) => match ($state) {
                            'متأخرة'     => 'danger',
                            'مكتملة', 'مغلقة' => 'gray',
                            'ضمن الوقت'  => 'success',
                            default       => 'gray',
                        }),
                ])->columns(2),
        ]);
    }
}
