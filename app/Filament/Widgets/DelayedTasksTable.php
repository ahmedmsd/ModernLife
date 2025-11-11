<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\TaskResource;
use App\Models\Department;
use App\Models\ProductionTask;
use App\Notifications\OwnerReminderNotification;
use Filament\Forms;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Illuminate\Support\Facades\DB;

class DelayedTasksTable extends TableWidget
{
    protected static ?string $heading = 'تأخيرات استلام وتسليم المهام';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('project.project_name')
                    ->label('المشروع')
                    ->searchable()
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('department_label')
                    ->label('القسم')
                    ->state(function (ProductionTask $r) {
                        $d = $r->department;
                        if (! $d) {
                            return '—';
                        }

                        return $d->name
                            ?? $d->dept_name
                            ?? $d->title
                            ?? $d->label
                            ?? ('قسم #' . ($d->dept_id ?? $d->id ?? '?'));
                    })
                    ->toggleable(),

                TextColumn::make('current_owner_role')
                    ->label('مالك المهمة')
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('current_owner_user_label')
                    ->label('المستخدم الحالي')
                    ->state(function (ProductionTask $r) {
                        $u = $r->currentOwnerUser;
                        if (! $u) {
                            return '—';
                        }

                        return $u->name
                            ?? $u->full_name
                            ?? $u->username
                            ?? ($u->email ?? ('مستخدم #' . ($u->id ?? '?')));
                    })
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed','closed' => 'success',
                        'in_progress'        => 'warning',
                        'assigned'           => 'info',
                        default              => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('assigned_at')
                    ->label('تاريخ الإسناد')
                    ->since()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('sent_to_owner_at')
                    ->label('أُرسِلت للمالك')
                    ->since()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('received_by_owner_at')
                    ->label('تم الاستلام')
                    ->since()
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('due_date')
                    ->label('تاريخ التسليم')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('wait_days_for_owner')
                    ->label('أيام الانتظار لدى المالك')
                    ->state(function (ProductionTask $r) {
                        $start = $r->sent_to_owner_at ?? $r->assigned_at;
                        if (! $start) return '—';

                        $startC = $start instanceof Carbon ? $start : Carbon::parse($start);
                        $endC   = $r->received_by_owner_at
                            ? ($r->received_by_owner_at instanceof Carbon ? $r->received_by_owner_at : Carbon::parse($r->received_by_owner_at))
                            : now();

                        $days = $startC->diffInDays($endC);
                        return intval($days) . ' يوم';
                    })
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('overdue_days_calc')
                    ->label('تأخير التسليم')
                    ->state(fn ($record) => ((int) $record->overdue_days_calc) . ' ي')
                    ->color(fn ($record) => ((int) $record->overdue_days_calc) > 0 ? 'danger' : 'gray')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('due_date', 'asc')
            ->filters([
                SelectFilter::make('department_id')
                    ->label('القسم')
                    ->options(function () {
                        return Department::query()
                            ->get()
                            ->mapWithKeys(function ($d) {
                                $label = $d->name
                                    ?? $d->dept_name
                                    ?? $d->title
                                    ?? $d->label
                                    ?? ('قسم #' . ($d->dept_id ?? $d->id ?? '?'));
                                $key = $d->dept_id ?? $d->id;
                                return [$key => $label];
                            })
                            ->toArray();
                    }),

                SelectFilter::make('current_owner_role')
                    ->label('دور المالك')
                    ->options([
                        'factory_manager'     => 'مدير المصنع',
                        'department_manager'  => 'مدير القسم',
                        'employee'            => 'موظف',
                        'purchasing_manager'  => 'مدير المشتريات',
                    ]),

                Filter::make('overdue_only')
                    ->label('متأخرة عن التسليم (٣ أيام فأكثر)')
                    ->toggle()
                    ->query(function (Builder $q) {
                        $table = $q->getModel()->getTable();

                        return $q->whereRaw("
                            {$table}.due_date IS NOT NULL
                            AND GREATEST(
                                TIMESTAMPDIFF(
                                    DAY,
                                    {$table}.due_date,
                                    COALESCE({$table}.completed_at, NOW())
                                ),
                                0
                            ) >= 3
                        ");
                    }),

                Filter::make('waiting_receive')
                    ->label('بانتظار استلام المالك')
                    ->toggle()
                    ->query(fn (Builder $q) =>
                    $q->whereNull('received_by_owner_at')
                        ->where(function (Builder $w) {
                            $w->whereNotNull('sent_to_owner_at')
                                ->orWhereNotNull('assigned_at');
                        })
                    ),
            ])
            ->actions([
                Action::make('remindOwner')
                    ->label('تذكير المالك')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->visible(fn (ProductionTask $r) => $r->current_owner_user_id !== null)
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('رسالة التذكير')
                            ->rows(3)
                            ->default(fn (ProductionTask $r) => $this->defaultTaskMsg($r))
                            ->required(),
                        Forms\Components\Toggle::make('via_email')
                            ->label('إرسال أيضًا عبر البريد')
                            ->default(false),
                    ])
                    ->action(function (ProductionTask $r, array $data) {
                        $owner = $r->currentOwnerUser;
                        if (! $owner) {
                            FilamentNotification::make()
                                ->title('لا يوجد مالك مُعرّف للمهمة.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $channels = ['database'];
                        if (! empty($data['via_email']) && ! empty($owner->email)) {
                            $channels[] = 'mail';
                        }

                        $url   = TaskResource::getUrl('view', ['record' => $r]);
                        $title = "تذكير استلام/معالجة مهمة رقم {$r->id}";
                        $body  = (string) $data['message'];

                        LaravelNotification::send(
                            $owner,
                            new OwnerReminderNotification($title, $body, $url, $channels)
                        );

                        FilamentNotification::make()
                            ->title('تم إرسال التذكير.')
                            ->success()
                            ->send();
                    }),

                Action::make('view')
                    ->label('عرض')
                    ->icon('heroicon-o-eye')
                    ->url(fn (ProductionTask $r) => TaskResource::getUrl('view', ['record' => $r])),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulkRemind')
                    ->label('تذكير جماعي للمالكين')
                    ->icon('heroicon-o-megaphone')
                    ->color('warning')
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('رسالة التذكير')
                            ->rows(3)
                            ->default('تذكير: توجد مهام متأخرة/بانتظار الاستلام. يرجى المتابعة.')
                            ->required(),
                        Forms\Components\Toggle::make('via_email')
                            ->label('إرسال أيضًا عبر البريد')
                            ->default(false),
                    ])
                    ->action(function (array $records, array $data) {
                        $owners = [];
                        foreach ($records as $r) {
                            if ($r->currentOwnerUser) {
                                $owners[$r->currentOwnerUser->getKey()] = $r->currentOwnerUser;
                            }
                        }

                        if (empty($owners)) {
                            FilamentNotification::make()
                                ->title('لا توجد حسابات مالكين صالحة.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $channels = ['database'];
                        if (! empty($data['via_email'])) {
                            $channels[] = 'mail';
                        }

                        $title = 'تذكير جماعي: مهام متأخرة/بانتظار الاستلام';
                        $body  = (string) $data['message'];

                        foreach ($owners as $owner) {
                            LaravelNotification::send(
                                $owner,
                                new OwnerReminderNotification($title, $body, null, $channels)
                            );
                        }

                        FilamentNotification::make()
                            ->title('تم إرسال التذكير الجماعي.')
                            ->success()
                            ->send();
                    }),
            ])
            ->paginationPageOptions([25, 50, 100]);
    }

    protected function defaultTaskMsg(ProductionTask $r): string
    {
        $start = $r->sent_to_owner_at ?? $r->assigned_at;
        $waitH = 0;

        if ($start) {
            $startC = $start instanceof Carbon ? $start : Carbon::parse($start);
            $endC   = $r->received_by_owner_at
                ? ($r->received_by_owner_at instanceof Carbon ? $r->received_by_owner_at : Carbon::parse($r->received_by_owner_at))
                : now();

            $waitH = $startC->diffInHours($endC);
        }

        $over = 0;
        if ($r->due_date) {
            $dueC = $r->due_date instanceof Carbon ? $r->due_date : Carbon::parse($r->due_date);
            $refC = $r->completed_at
                ? ($r->completed_at instanceof Carbon ? $r->completed_at : Carbon::parse($r->completed_at))
                : now();

            if ($refC->greaterThan($dueC)) {
                $over = $refC->diffInDays($dueC);
            }
        }

        $base = "تذكير باستلام/معالجة المهمة رقم {$r->id}.";
        $w    = $waitH > 0 ? " مدة الانتظار: {$waitH} ساعة." : '';
        $o    = $over  > 0 ? " تأخير التسليم: {$over} يوم." : '';

        return trim("{$base}{$w}{$o}");
    }

    protected function baseQuery(): Builder
    {
        $table = (new ProductionTask())->getTable();

        return ProductionTask::query()
            ->select("{$table}.*")
            ->with([
                'project',
                'department',
                'currentOwnerUser',
            ])
            ->addSelect([
                'overdue_days_calc' => DB::raw("
                    CASE
                        WHEN {$table}.due_date IS NULL THEN 0
                        ELSE GREATEST(
                            TIMESTAMPDIFF(
                                DAY,
                                {$table}.due_date,
                                COALESCE({$table}.completed_at, NOW())
                            ),
                            0
                        )
                    END
                "),
            ])
            ->where(function (Builder $q) use ($table) {
                $q
                    ->where(function (Builder $w) use ($table) {
                        $w->whereNull("{$table}.received_by_owner_at")
                            ->where(function (Builder $ww) use ($table) {
                                $ww->whereNotNull("{$table}.sent_to_owner_at")
                                    ->orWhereNotNull("{$table}.assigned_at");
                            });
                    })
                    ->orWhereRaw("
                        {$table}.due_date IS NOT NULL
                        AND GREATEST(
                            TIMESTAMPDIFF(
                                DAY,
                                {$table}.due_date,
                                COALESCE({$table}.completed_at, NOW())
                            ),
                            0
                        ) >= 3
                    ");
            });
    }

    public function getTableRecordKey(mixed $record): string
    {
        if (method_exists($record, 'getKey')) {
            $key = $record->getKey();
        } else {
            $keyName = method_exists($record, 'getKeyName') ? $record->getKeyName() : 'id';
            $key = $record->{$keyName} ?? null;
        }

        if ($key === null) {
            $key = $record->id ?? $record->uuid ?? spl_object_id($record);
        }

        return (string) $key;
    }
}
