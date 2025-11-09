<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\DatabaseNotification;

class MyNotifications extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-bell';
    protected static ?string $navigationLabel = 'إشعاراتي';
    protected static ?string $title           = 'إشعاراتي';
    protected static ?string $slug            = 'my-notifications';

    // اجعلها في المجموعة التي تريدها في القائمة الجانبية أو احذفها:
    protected static ?string $navigationGroup = 'حسابي';

    protected static string $view = 'filament.pages.my-notifications';

    public static function canAccess(): bool
    {
        return auth()->check();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('العنوان')
                    ->state(function (DatabaseNotification $record): string {
                        $data  = $record->data ?? [];
                        $title =
                            $data['title']
                            ?? $data['subject']
                            ?? $data['message']
                            ?? $data['action']
                            ?? class_basename($record->type);

                        return (string) $title;
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('related')
                    ->label('يتعلق بـ')
                    ->state(function (DatabaseNotification $record): ?string {
                        $data = $record->data ?? [];

                        // مهمة تصنيع
                        if (! empty($data['task_title'])) {
                            return 'مهمة: ' . $data['task_title'];
                        }
                        if (! empty($data['task_id']) && empty($data['task_title'])) {
                            return 'مهمة #' . $data['task_id'];
                        }

                        // مشروع
                        if (! empty($data['project_name'])) {
                            return 'مشروع: ' . $data['project_name'];
                        }
                        if (! empty($data['project_id']) && empty($data['project_name'])) {
                            return 'مشروع #' . $data['project_id'];
                        }

                        // طلب تصنيع
                        if (! empty($data['pr_id'])) {
                            return 'طلب تصنيع #' . $data['pr_id'];
                        }

                        // طلب صيانة
                        if (! empty($data['request_id']) && ($data['type'] ?? null) === 'maintenance') {
                            return 'طلب صيانة #' . $data['request_id'];
                        }

                        // طلب مشتريات قسم (DPR)
                        if (! empty($data['request_number'])) {
                            return 'طلب شراء #' . $data['request_number'];
                        }

                        // اسم عميل لو موجود
                        if (! empty($data['client_name'])) {
                            return 'عميل: ' . $data['client_name'];
                        }

                        return null;
                    })
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('body')
                    ->label('التفاصيل')
                    ->state(function (DatabaseNotification $record): ?string {
                        $data = $record->data ?? [];

                        return $data['body']
                            ?? $data['message']
                            ?? $data['note']
                            ?? $data['description']
                            ?? null;
                    })
                    ->limit(80)
                    ->wrap()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('source')
                    ->label('النوع')
                    ->state(function (DatabaseNotification $record): string {
                        return str_contains($record->type, 'Filament\\Notifications')
                            ? 'تنبيه نظام (Filament)'
                            : 'تنبيه تطبيق الأعمال';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'تنبيه نظام (Filament)' ? 'info' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('منذ')
                    ->since()
                    ->sortable(),

                Tables\Columns\TextColumn::make('read_state')
                    ->label('الحالة')
                    ->state(fn (DatabaseNotification $record) => $record->read_at ? 'مقروء' : 'غير مقروء')
                    ->badge()
                    ->color(fn (DatabaseNotification $record) => $record->read_at ? 'success' : 'warning'),
            ])
            ->actions([
                Tables\Actions\Action::make('open')
                    ->label('فتح')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(function (DatabaseNotification $record): ?string {
                        $data = $record->data ?? [];
                        return $data['url'] ?? $data['action_url'] ?? null;
                    }, shouldOpenInNewTab: true)
                    ->visible(function (DatabaseNotification $record): bool {
                        $data = $record->data ?? [];
                        return ! empty($data['url'] ?? $data['action_url'] ?? null);
                    }),

                Tables\Actions\Action::make('markAsRead')
                    ->label('تحديد كمقروء')
                    ->icon('heroicon-o-check')
                    ->visible(fn (DatabaseNotification $record) => is_null($record->read_at))
                    ->action(function (DatabaseNotification $record): void {
                        $record->markAsRead();
                    }),

                Tables\Actions\Action::make('markAsUnread')
                    ->label('تحديد كغير مقروء')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->visible(fn (DatabaseNotification $record) => ! is_null($record->read_at))
                    ->action(function (DatabaseNotification $record): void {
                        $record->update(['read_at' => null]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('markAllAsRead')
                    ->label('تحديد كمقروء (مجموعة)')
                    ->icon('heroicon-o-check')
                    ->action(function ($records): void {
                        /** @var \Illuminate\Support\Collection $records */
                        $records->each(function (DatabaseNotification $record): void {
                            $record->markAsRead();
                        });
                    }),
                Tables\Actions\BulkAction::make('markAllAsUnread')
                    ->label('تحديد كغير مقروء (مجموعة)')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->action(function ($records): void {
                        $records->each(function (DatabaseNotification $record): void {
                            $record->update(['read_at' => null]);
                        });
                    }),
            ]);
    }

    protected function getTableQuery(): Builder
    {
        $user = auth()->user();

        return DatabaseNotification::query()
            ->where('notifiable_type', get_class($user))
            ->where('notifiable_id', $user->getKey())
            ->latest();
    }
}
