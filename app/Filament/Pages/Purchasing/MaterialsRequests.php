<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Models\SystemSetting;
use App\Models\ProductionTask;
use App\Support\Filament\HasShieldAccess;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;


class MaterialsRequests extends Page implements HasTable
{
    use InteractsWithTable;
//    use HasShieldAccess;

    /** إعدادات الواجهة والتنقل */
    protected static ?string $navigationIcon  = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'طلبات الخامات';
    protected static ?string $title           = 'طلبات الخامات (المشتريات)';
    protected static ?string $slug            = 'purchasing/materials-requests';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static ?int    $navigationSort  = 10;

    protected static string $view = 'filament.pages.purchasing.materials-requests';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['purchasing_manager','admin','super-admin']);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public static function getNavigationBadge(): ?string
    {
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
                        'task.project.productionRequest',
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
                TextColumn::make('estimated_cost')
                    ->label('الميزانية '),
                TextColumn::make('requested_at')
                    ->label('تاريخ الطلب')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('expected_delivery_at')
                    ->label('موعد التوريد (متوقّع)')
                    ->dateTime('Y-m-d H:i')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('status')
                    ->label('حالة طلب الخامات')
                    ->badge()
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
                // عرض تفاصيل الطلب
                Action::make('viewDetails')
                    ->label('عرض ')
                    ->icon('heroicon-o-eye')
                    ->url(fn (MaterialRequest $record) => ViewMaterialRequest::getUrl(['record' => $record])),
            ])
            ->bulkActions([
                /**
                 * تأكيد توفير مجمّع:
                 * - لا يجمع بيانات الفاتورة (منطقيًا يحتاج إدخال يدوي لكل سجل).
                 * - يحوّل المهام إلى materials_done وينقل الملكية لمدير القسم.
                 */
                Tables\Actions\BulkAction::make('bulkConfirm')
                    ->label('تأكيد توفير (مجمع)')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            /** @var MaterialRequest $record */
                            DB::transaction(function () use ($record) {
                                if ($record->status !== 'approved' || $record->provided_at) {
                                    return;
                                }

                                // 1) Fulfill طلب الخامات (بدون بيانات فاتورة في المجمع)
                                $record->update([
                                    'status'      => 'fulfilled',
                                    'provided_by' => Auth::id(),
                                    'provided_at' => now(),
                                ]);

                                // 2) نقل المهمة إلى materials_done + ملكية مدير القسم
                                /** @var ProductionTask|null $task */
                                $task = $record->task()->lockForUpdate()->first();
                                if (! $task || in_array($task->status, ['completed','cancelled'])) {
                                    return;
                                }

                                $dept            = $task->department;
                                $departmentOwner = $dept?->manager_id ?? $dept?->head_user_id ?? null;

                                $this->updateTaskStateAndOwnership($task, [
                                    'status'             => 'materials_done',
                                    'current_owner_role' => 'department_manager',
                                    'current_owner_user' => $departmentOwner,
                                ]);
                            });
                        }

                        Notification::make()
                            ->title('تم تأكيد التوفير للمهام المحددة')
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('لا توجد طلبات خامات قيد المعالجة');
    }

    /**
     * تحديث حالة وملكية المهمة بشكل آمن (يتحقق من الأعمدة قبل التحديث).
     *
     * @param ProductionTask $task
     * @param array{
     *   status?: string,
     *   current_owner_role?: string,
     *   current_owner_user?: int|null
     * } $values
     */
    protected function updateTaskStateAndOwnership(ProductionTask $task, array $values): void
    {
        $payload = [];

        if (isset($values['status'])) {
            $payload['status'] = $values['status'];
        }

        if (
            isset($values['current_owner_role']) &&
            Schema::hasColumn($task->getTable(), 'current_owner_role')
        ) {
            $payload['current_owner_role'] = $values['current_owner_role'];
        }

        if (
            array_key_exists('current_owner_user', $values) &&
            Schema::hasColumn($task->getTable(), 'current_owner_user_id')
        ) {
            $payload['current_owner_user_id'] = $values['current_owner_user'];
        }

        if (! empty($payload)) {
            $task->update($payload);
        }
    }

    /** إرسال تنبيه داخلي لدور معين (لا يوقف المعاملة عند الفشل) */
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
        }
    }
}
