<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\Carbon;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    public function mount($record): void
    {
        parent::mount($record);

        // ✅ تأكد من أعمدة المفاتيح الصحيحة في جداولك
        $this->record->load([
            'project:id,project_name',           // projects: id, project_name
            'department:dept_id,dept_name',      // departments: dept_id, dept_name
            'employee:employee_id,employee_name' // employees: employee_id, employee_name (عدّلها إلى id إذا كان المفتاح id)
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return url("/admin/projects/{$this->record->project_id}/manage-tasks");
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
                    RepeatableEntry::make('timeline')
                        ->label('سجل العمليات')
                        // ✅ استخدم $record أو Type-hint للموديل بدلاً من $task
                        ->state(function ($record) {
                            // $record هنا هو موديل Task
                            $logs = $record->logs()
                                ->orderBy('happened_at')
                                ->get(['id', 'type', 'data', 'happened_at']);

                            $out = [];
                            $count = $logs->count();

                            for ($i = 0; $i < $count; $i++) {
                                $current = $logs[$i];
                                $next = $i + 1 < $count ? $logs[$i + 1] : null;

                                $from = Carbon::parse($current->happened_at);
                                $to = $next ? Carbon::parse($next->happened_at) : now();

                                $out[] = [
                                    'happened_at' => $current->happened_at,
                                    'type'        => $current->type,
                                    'data'        => $current->data, // متوقع cast إلى array
                                    'duration'    => $from->diffForHumans($to, true),
                                ];
                            }

                            return $out;
                        })
                        ->schema([
                            TextEntry::make('happened_at')
                                ->label('التاريخ')
                                ->formatStateUsing(fn ($state) => $state ? Carbon::parse($state)->toDateTimeString() : '—'),

                            TextEntry::make('type')
                                ->label('الحدث')
                                ->badge()
                                ->color(fn ($state) => match ($state) {
                                    'created'          => 'gray',
                                    'status_changed'   => 'info',
                                    'assigned_changed' => 'warning',
                                    'due_changed'      => 'purple',
                                    'timer_started'    => 'success',
                                    'timer_stopped'    => 'danger',
                                    default            => 'gray',
                                }),

                            TextEntry::make('data')
                                ->label('التفاصيل')
                                ->formatStateUsing(function ($state, array $row) {
                                    if (is_array($state)) {
                                        if (($row['type'] ?? null) === 'status_changed') {
                                            $from = $this->statusAr($state['from'] ?? null);
                                            $to   = $this->statusAr($state['to']   ?? null);
                                            if ($from || $to) {
                                                return "من: " . ($from ?? '—') . " → إلى: " . ($to ?? '—');
                                            }
                                        }

                                        return $state['note']
                                            ?? $state['reason']
                                            ?? json_encode($state, JSON_UNESCAPED_UNICODE);
                                    }

                                    return '—';
                                })
                                ->columnSpan(2),

                            TextEntry::make('duration')
                                ->label('المدة حتى الحدث التالي')
                                ->placeholder('—'),
                        ])
                        ->columns(4),
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
                ]),
        ]);
    }
}
