<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Enums\TaskStatus as S;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class MaterialsRequests extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'طلبات الخامات';
    protected static ?string $title           = 'طلبات الخامات (المشتريات)';
    protected static ?string $slug            = 'purchasing/materials-requests';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static ?int    $navigationSort  = 10;

    protected static string $view = 'filament.pages.purchasing.materials-requests';

    public static function shouldRegisterNavigation(): bool
    {
        $u = Auth::user();
        return $u && $u->hasAnyRole(['purchasing_manager','admin','super-admin']);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) MaterialRequest::query()->whereNull('provided_at')->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('طلبات خامات قيد الانتظار')
            ->query(
                MaterialRequest::query()
                    ->whereNull('provided_at')
                    ->with(['task', 'department'])
            )
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('task.id')->label('المهمة')->sortable(),
                TextColumn::make('department.dept_name')->label('القسم')->sortable()->searchable(),
                TextColumn::make('note')->label('المطلوبات')->wrap()->limit(120),
                TextColumn::make('requested_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn() => 'قيد التوريد')
                    ->color('warning'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),
            ])
            ->actions([
                // زر واحد: تأكيد توفير الخامات
                Action::make('confirmMaterials')
                    ->label('تأكيد توفير الخامات')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn () => Auth::user()?->hasAnyRole(['purchasing_manager','admin','super-admin']))
                    ->form([
                        Forms\Components\TextInput::make('po_number')->label('رقم الطلب/مرجع (اختياري)'),
                        Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (MaterialRequest $record, array $data) {
                        // إغلاق الطلب
                        $record->update([
                            'status'      => 'fulfilled',
                            'po_number'   => $data['po_number'] ?? $record->po_number,
                            'provided_by' => Auth::id(),
                            'provided_at' => now(),
                            'note'        => trim(($record->note ? $record->note . "\n\n" : '') . ($data['note'] ?? '')),
                        ]);

                        // إعادة المهمة للتنفيذ إن كانت متوقفة بلا طلبات مفتوحة أخرى
                        $task = $record->task;
                        if ($task && $task->status === 'blocked' && ! $task->materialRequests()->whereNull('provided_at')->exists()) {
                            $task->status = 'in_progress';
                            $task->save();
                        }

                        // إشعار مدير القسم
                        $this->notifyRole('department_manager',
                            'تم توفير الخامات',
                            "المهمة #{$record->task?->id}: تم التأكيد، يمكن استئناف التنفيذ.");

                        Notification::make()->title('تم تأكيد التوفير')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('bulkConfirm')
                    ->label('تأكيد توفير (مجمع)')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            /** @var MaterialRequest $record */
                            if ($record->status !== 'requested') {
                                continue;
                            }
                            $record->update([
                                'status'      => 'fulfilled',
                                'provided_by' => Auth::id(),
                                'provided_at' => now(),
                            ]);

                            $task = $record->task;
                            if ($task && $task->status === S::Blocked && ! $task->materialRequests()->where('status', 'requested')->exists()) {
                                $task->status = S::InProgress;
                                $task->save();
                            }
                        }
                        Notification::make()->title('تم تأكيد التوفير للمهام المحددة')->success()->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد طلبات خامات قيد الانتظار');
    }

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
        } catch (\Throwable $e) {}
    }
}
