<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Models\SystemSetting;
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
        return $u && $u->hasAnyRole(['purchasing_manager', 'admin', 'super-admin']);
    }

    public static function getNavigationBadge(): ?string
    {
        // المطلوب اعتمادها أو توريدها
        return (string) MaterialRequest::query()
            ->whereNull('provided_at')
            ->whereIn('status', ['requested', 'approved'])
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('طلبات خامات قيد المعالجة')
            ->query(
                MaterialRequest::query()
                    ->whereNull('provided_at')
                    ->whereIn('status', ['requested', 'approved'])
                    ->with([
                        'task.project.productionRequest', // المشروع والطلب الأم
                        'department',
                        'requestedBy',
                    ])
            )
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),

                TextColumn::make('task.id')
                    ->label('المهمة')
                    ->sortable(),

                TextColumn::make('department.dept_name')
                    ->label('القسم')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('task.project.project_name')
                    ->label('المشروع')
                    ->searchable(),

                TextColumn::make('requester')
                    ->label('مقدّم الطلب')
                    // ✅ استخدم $record
                    ->state(fn (MaterialRequest $record) =>
                        ($record->requestedBy?->name)
                        ?? ($record->task?->employee?->employee_name)
                        ?? '—'
                    )
                    ->searchable(),

                TextColumn::make('note')
                    ->label('المطلوبات')
                    ->wrap()
                    ->limit(120),

                TextColumn::make('requested_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('expected_delivery_at')
                    ->label('موعد التوريد (متوقّع)')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    // ✅ استخدم $state
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'requested' => 'بانتظار اعتماد المشتريات',
                        'approved'  => 'بانتظار التوريد',
                        'fulfilled' => 'مورَّد',
                        'cancelled' => 'ملغى',
                        default     => '—',
                    })
                    ->color(fn (?string $state) => match ($state) {
                        'requested' => 'warning',
                        'approved'  => 'info',
                        'fulfilled' => 'success',
                        'cancelled' => 'gray',
                        default     => 'secondary',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('القسم')
                    ->relationship('department', 'dept_name'),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'requested' => 'بانتظار اعتماد المشتريات',
                        'approved'  => 'بانتظار التوريد',
                    ]),
            ])
            ->actions([
                // 1) اعتماد طلب الشراء (من requested -> approved)
                Action::make('approvePurchasing')
                    ->label('اعتماد طلب الشراء')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (MaterialRequest $record) =>
                        auth()->user()?->hasAnyRole(['purchasing_manager', 'admin', 'super-admin'])
                        && $record->status === 'requested'
                    )
                    ->form([
                        Forms\Components\TextInput::make('estimated_cost')
                            ->label('التكلفة التقديرية')
                            ->numeric()
                            ->required(),

                        Forms\Components\DateTimePicker::make('expected_delivery_at')
                            ->label('التاريخ المتوقع للتسليم')
                            ->required(),

                        Forms\Components\Textarea::make('note')
                            ->label('ملاحظة (اختياري)')
                            ->rows(3),
                    ])
                    ->action(function (MaterialRequest $record, array $data) {
                        $record->update([
                            'status'               => 'approved',
                            'estimated_cost'       => (float) $data['estimated_cost'],
                            'expected_delivery_at' => $data['expected_delivery_at'],
                            'approved_at'          => now(),
                            'approved_by'          => auth()->id(),
                            'note'                 => trim(($record->note ? $record->note . "\n\n" : '') . ($data['note'] ?? '')),
                        ]);

                        // تحقق حدّ المشتريات من إعدادات النظام (نسبة)
                        $capPct  = (float) (SystemSetting::get('purchasing_budget_cap_pct', 0.50) ?? 0.50);
                        $task    = $record->task;
                        $pr      = $task?->project?->productionRequest;
                        $order   = (float) ($pr?->total_price ?? 0.0);
                        $matCost = (float) $record->estimated_cost;

                        if ($order > 0 && $matCost > ($order * $capPct)) {
                            foreach (['factory_manager', 'super-admin'] as $roleName) {
                                try {
                                    $role = Role::findByName($roleName);
                                    foreach ($role->users as $user) {
                                        Notification::make()
                                            ->title('تنبيه تجاوز حدّ المشتريات')
                                            ->body("طلب #{$pr?->id}: التكلفة {$matCost} تخطّت " . ($capPct * 100) . "% من سعر الطلب {$order}.")
                                            ->sendToDatabase($user);
                                    }
                                } catch (\Throwable $e) {
                                    // تجاهل أي خطأ في الإشعارات
                                }
                            }

                            Notification::make()
                                ->warning()
                                ->title('تم الاعتماد مع تحذير الميزانية')
                                ->body('التكلفة التقديرية تخطّت الحدّ المسموح—تم تنبيه الإدارة.')
                                ->send();
                        } else {
                            Notification::make()
                                ->success()
                                ->title('تم اعتماد طلب الشراء')
                                ->send();
                        }
                    }),

                // 2) تأكيد توفير الخامات (approved -> fulfilled)
                Action::make('confirmMaterials')
                    ->label('تأكيد توفير الخامات')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    // ✅ $record بدل $r
                    ->visible(fn (MaterialRequest $record) =>
                        auth()->user()?->hasAnyRole(['purchasing_manager', 'admin', 'super-admin'])
                        && $record->status === 'approved'
                        && is_null($record->provided_at)
                    )
                    ->form([
                        Forms\Components\TextInput::make('po_number')->label('رقم الطلب/مرجع (اختياري)'),
                        Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (MaterialRequest $record, array $data) {
                        $record->update([
                            'status'      => 'fulfilled',
                            'po_number'   => $data['po_number'] ?: $record->po_number,
                            'provided_by' => auth()->id(),
                            'provided_at' => now(),
                            'note'        => trim(($record->note ? $record->note . "\n\n" : '') . ($data['note'] ?? '')),
                        ]);

                        // إعادة المهمة للتنفيذ إن كانت متوقفة ولا توجد طلبات خامات مفتوحة أخرى
                        $task = $record->task;

                        if ($task && $task->status === 'blocked') {
                            $hasOpen = $task->materialRequests()
                                ->whereNull('provided_at')
                                ->whereIn('status', ['requested', 'approved'])
                                ->exists();

                            if (! $hasOpen) {
                                $task->update(['status' => 'in_progress']);
                            }
                        }

                        Notification::make()
                            ->title('تم تأكيد توفير الخامات')
                            ->success()
                            ->send();
                    }),


                Action::make('viewDetails')
                    ->label('عرض ')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record) => ViewMaterialRequest::getUrl(['record' => $record])),
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
                            if ($record->status !== 'approved' || $record->provided_at) {
                                continue;
                            }

                            $record->update([
                                'status'      => 'fulfilled',
                                'provided_by' => Auth::id(),
                                'provided_at' => now(),
                            ]);

                            $task = $record->task;

                            if ($task && $task->status === 'blocked') {
                                $hasOpen = $task->materialRequests()
                                    ->whereNull('provided_at')
                                    ->whereIn('status', ['requested', 'approved'])
                                    ->exists();

                                if (! $hasOpen) {
                                    $task->update(['status' => 'in_progress']);
                                }
                            }
                        }

                        Notification::make()
                            ->title('تم تأكيد التوفير للمهام المحددة')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد طلبات خامات قيد المعالجة');
    }

    /** إرسال إشعار لكل مستخدمي دور معيّن */
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
            // تجاهل أي خطأ في الإشعارات
        }
    }
}
