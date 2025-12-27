<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProductionRequestResource;
use App\Models\ProductionRequest;
use App\Models\User;
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

class DelayedRequestsTable extends TableWidget
{
    protected static ?string $heading = 'تأخيرات استلام/الرد على الطلبات';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('project_client')
                    ->label('المشروع / العميل')
                    ->html()
                    ->state(function (ProductionRequest $r) {
                        $pName = $r->project_name ?? '—';
                        $cName = $r->client?->client_name ?? '—';
                        return "<div>{$pName}</div><div class='text-xs text-gray-500'>{$cName}</div>";
                    })
                    ->searchable(['id', 'project_name', 'client.client_name']),

                TextColumn::make('owner_details')
                    ->label('المالك')
                    ->html()
                    ->state(function (ProductionRequest $r) {
                        $u = $r->currentOwnerUser;
                        $uName = $u?->name ?? '—';
                        $role = $r->current_owner_role ?? '—';
                        $roleAr = match($role) {
                            'factory_manager'    => 'مدير المصنع',
                            'sales'              => 'المبيعات',
                            'purchasing_manager' => 'مدير المشتريات',
                            default              => $role,
                        };
                        return "<div>{$uName}</div><div class='text-xs text-gray-500'>{$roleAr}</div>";
                    }),

                TextColumn::make('handoff_delay_days')
                    ->label('تأخير الاستلام')
                    ->formatStateUsing(fn ($state) => ((int) $state) . ' يوم')
                    ->color(fn ($state) => ((int) $state) >= 3 ? 'danger' : 'gray')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('current_owner_role')
                    ->label('دور المالك')
                    ->options([
                        'factory_manager'     => 'مدير المصنع',
                        'sales'       => ' المبيعات',
                        'purchasing_manager'  => 'مدير المشتريات',
                    ]),

                SelectFilter::make('status')
                    ->label('حالة الطلب')
                    ->options([
                        'submitted'    => 'تم التقديم',
                        'under_review' => 'قيد المراجعة',
                        'approved'     => 'تم الموافقة',
                        'rejected'     => 'تم الرفض',
                    ]),

                Filter::make('waiting_receive')
                    ->label('بانتظار الاستلام/الرد')
                    ->toggle()
                    ->query(function (Builder $q) {
                        $table = $q->getModel()->getTable();
                        $q->whereNull("{$table}.received_by_owner_at")
                            ->where(function (Builder $w) use ($table) {
                                $w->whereNotNull("{$table}.sent_to_owner_at")
                                    ->orWhereNotNull("{$table}.submitted_at");
                            });
                    }),

                Filter::make('stale_under_review')
                    ->label('مراجعة متأخرة (>3 أيام)')
                    ->toggle()
                    ->query(function (Builder $q) {
                        $table = $q->getModel()->getTable();
                        $q->where("{$table}.status", 'under_review')
                            ->where("{$table}.submitted_at", '<', now()->subDays(3));
                    }),
            ])
            ->actions([
                Action::make('remindOwner')
                    ->label('تذكير المالك')
                    ->icon('heroicon-o-bell-alert')
                    ->color('warning')
                    ->visible(fn (ProductionRequest $r) => !empty($r->current_owner_user_id))
                    ->form([
                        Forms\Components\Textarea::make('message')
                            ->label('رسالة التذكير')
                            ->rows(3)
                            ->default(fn (ProductionRequest $r) => $this->defaultRequestMsg($r))
                            ->required(),
                        Forms\Components\Toggle::make('via_email')
                            ->label('إرسال أيضًا عبر البريد')
                            ->default(false),
                    ])
                    ->action(function (ProductionRequest $r, array $data) {
                        $owner = $r->current_owner_user_id
                            ? User::find($r->current_owner_user_id)
                            : null;

                        if (!$owner) {
                            FilamentNotification::make()
                                ->title('لا يوجد مالك مُعرّف للطلب.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $channels = ['database'];
                        if (!empty($data['via_email']) && !empty($owner->email)) {
                            $channels[] = 'mail';
                        }

                        $url   = ProductionRequestResource::getUrl('view', ['record' => $r]);
                        $title = "تذكير استلام/الرد على طلب رقم {$r->id}";
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
                    ->url(fn (ProductionRequest $r) => ProductionRequestResource::getUrl('view', ['record' => $r])),

                Action::make('review')
                    ->label('مراجعة الطلب')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->url(fn (ProductionRequest $r) => ProductionRequestResource::getUrl('edit', ['record' => $r])),
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
                            ->default('تذكير: توجد طلبات متأخرة/بانتظار الاستلام أو الرد. يرجى المتابعة.')
                            ->required(),
                        Forms\Components\Toggle::make('via_email')
                            ->label('إرسال أيضًا عبر البريد')
                            ->default(false),
                    ])
                    ->action(function (array $records, array $data) {
                        $ids = [];
                        foreach ($records as $r) {
                            if ($r->current_owner_user_id) {
                                $ids[$r->current_owner_user_id] = true;
                            }
                        }

                        if (empty($ids)) {
                            FilamentNotification::make()->title('لا توجد حسابات مالكين صالحة.')->danger()->send();
                            return;
                        }

                        $users = User::whereIn('id', array_keys($ids))->get();
                        if ($users->isEmpty()) {
                            FilamentNotification::make()->title('لا يوجد مستلمون.')->warning()->send();
                            return;
                        }

                        $channels = ['database'];
                        if (!empty($data['via_email'])) {
                            $channels[] = 'mail';
                        }

                        $title = 'تذكير جماعي: طلبات متأخرة/بانتظار الاستلام أو الرد';
                        $body  = (string) $data['message'];

                        foreach ($users as $u) {
                            LaravelNotification::send(
                                $u,
                                new OwnerReminderNotification($title, $body, null, $channels)
                            );
                        }

                        FilamentNotification::make()->title('تم إرسال التذكير الجماعي.')->success()->send();
                    }),
            ])
            ->paginationPageOptions([25, 50, 100]);
    }

    protected function defaultRequestMsg(ProductionRequest $r): string
    {
        $start = $r->sent_to_owner_at ?? $r->submitted_at;
        $waitH = $start ? Carbon::parse($start)->diffInHours(now()) : 0;
        $base  = "تذكير باستلام/الرد على طلب رقم {$r->id} - {$r->project_name}.";
        $w     = $waitH > 0 ? " مدة الانتظار: {$waitH} ساعة." : '';
        return trim("{$base}{$w}");
    }

    protected function baseQuery(): Builder
    {
        $model = new ProductionRequest();
        $table = $model->getTable();

        return $model->newQuery()
            ->with([
                'client:client_id,client_name',
                'currentOwnerUser',
            ])
            ->select("{$table}.*")
            ->selectRaw("
                CASE
                    WHEN {$table}.received_by_owner_at IS NOT NULL THEN 0
                    WHEN COALESCE({$table}.sent_to_owner_at, {$table}.submitted_at) IS NULL THEN 0
                    ELSE GREATEST(
                        0,
                        TIMESTAMPDIFF(
                            HOUR,
                            COALESCE({$table}.sent_to_owner_at, {$table}.submitted_at),
                            NOW()
                        )
                    )
                END AS wait_hours
            ")
            ->selectRaw("
                CASE
                    WHEN COALESCE({$table}.sent_to_owner_at, {$table}.submitted_at) IS NULL THEN 0
                    ELSE DATEDIFF(
                        COALESCE({$table}.received_by_owner_at, NOW()),
                        COALESCE({$table}.sent_to_owner_at, {$table}.submitted_at)
                    )
                END AS handoff_delay_days
            ")
            ->selectRaw("
                CASE
                    WHEN {$table}.status = 'under_review'
                         AND {$table}.submitted_at IS NOT NULL
                    THEN GREATEST(
                        0,
                        TIMESTAMPDIFF(
                            DAY,
                            {$table}.submitted_at,
                            NOW()
                        )
                    )
                    ELSE 0
                END AS under_review_days
            ")
            ->whereNotIn("{$table}.status", ['approved', 'rejected'])
            ->whereNull("{$table}.received_by_owner_at")
            ->where(function (Builder $q) use ($table) {
                $cutoff = now()->subDays(3)->startOfDay();
                // Strictly > 3 days since sent_to_owner or submitted_at
                $q->where("{$table}.sent_to_owner_at", '<', $cutoff)
                  ->orWhere(function ($qq) use ($table, $cutoff) {
                      $qq->whereNull("{$table}.sent_to_owner_at")
                         ->where("{$table}.submitted_at", '<', $cutoff);
                  });
            });
    }

}
