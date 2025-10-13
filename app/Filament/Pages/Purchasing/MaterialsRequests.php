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
                /**
                 * (1) اعتماد المشتريات:
                 * - MaterialRequest: requested -> approved
                 * - ProductionTask : status -> materials_wait, owner -> purchasing_manager
                 */
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
                        DB::transaction(function () use ($record, $data) {
                            // 1) تحديث طلب الخامات إلى approved
                            $record->update([
                                'status'               => 'approved',
                                'estimated_cost'       => (float) $data['estimated_cost'],
                                'expected_delivery_at' => $data['expected_delivery_at'],
                                'approved_at'          => now(),
                                'approved_by'          => auth()->id(),
                                'note'                 => trim(($record->note ? $record->note . "\n\n" : '') . ($data['note'] ?? '')),
                            ]);

                            // 2) نقل المهمة إلى materials_wait + ملكية المشتريات
                            /** @var ProductionTask|null $task */
                            $task = $record->task()->lockForUpdate()->first();
                            if ($task) {
                                $this->updateTaskStateAndOwnership($task, [
                                    'status'             => 'materials_wait',
                                    'current_owner_role' => 'purchasing_manager',
                                    'current_owner_user' => null,
                                ]);
                            }
                        });

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
                                } catch (\Throwable $e) {}
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


                Action::make('confirmMaterials')
                    ->label('تأكيد توفير الخامات')
                    ->icon('heroicon-o-check-badge')
                    ->color('primary')
                    ->visible(fn (MaterialRequest $record) =>
                        auth()->user()?->hasAnyRole(['purchasing_manager', 'admin', 'super-admin'])
                        && $record->status === 'approved'
                        && is_null($record->provided_at)
                    )
                    ->form([
                        Forms\Components\TextInput::make('po_number')
                            ->label('رقم الطلب/مرجع (اختياري)'),

                        Forms\Components\TextInput::make('invoice_no')
                            ->label('رقم فاتورة الشراء')
                            ->required(),

                        Forms\Components\TextInput::make('actual_cost')
                            ->label('مبلغ الفاتورة')
                            ->numeric()
                            ->required(),

                        Forms\Components\DatePicker::make('invoice_date')
                            ->label('تاريخ الفاتورة')
                            ->displayFormat('Y-m-d')
                            ->native(false)
                            ->required(),

                        Forms\Components\FileUpload::make('invoice_file')
                            ->label('فاتورة الشراء (PDF/صورة)')
                            ->disk('public')                       // استخدم قرص التخزين "public"
                            ->directory('materials_invoices')      // مجلد الحفظ
                            ->preserveFilenames()
                            ->openable()
                            ->downloadable()
                            ->maxSize(10240)                       // 10MB
                            ->acceptedFileTypes(['application/pdf','image/*'])
                            ->helperText('ارفع صورة أو PDF بحد أقصى 10MB.'),

                        Forms\Components\Textarea::make('note')
                            ->label('ملاحظة (اختياري)')
                            ->rows(3),
                    ])
                    ->requiresConfirmation()
                    ->action(function (MaterialRequest $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            // ــ حفظ ملف الفاتورة يدويًا عند استخدام Action form (غير Model form)
                            $invoicePath = $record->invoice_file;
                            if (! empty($data['invoice_file'])) {
                                if ($data['invoice_file'] instanceof UploadedFile) {
                                    $invoicePath = $data['invoice_file']->store('materials_invoices', 'public');
                                } elseif (is_string($data['invoice_file'])) {
                                    // في بعض الحالات قد يعود مسار جاهز من FileUpload
                                    $invoicePath = $data['invoice_file'];
                                }
                            }

                            // 1) تحديث طلب الخامات إلى fulfilled + بيانات الفاتورة
                            $payload = [
                                'status'       => 'fulfilled',
                                'po_number'    => $data['po_number'] ?: $record->po_number,
                                'provided_by'  => auth()->id(),
                                'provided_at'  => now(),
                                'note'         => trim(($record->note ? $record->note . "\n\n" : '') . ($data['note'] ?? '')),
                            ];

                            // نحدّث حقول الفاتورة فقط إذا كانت الأعمدة موجودة
                            if (Schema::hasColumn($record->getTable(), 'actual_cost')) {
                                $payload['actual_cost'] = (float) $data['actual_cost'];
                            }
                            if (Schema::hasColumn($record->getTable(), 'invoice_date')) {
                                $payload['invoice_date'] = $data['invoice_date'];
                            }
                            if (Schema::hasColumn($record->getTable(), 'invoice_no')) {
                                $payload['invoice_no'] = $data['invoice_no'];
                            }
                            if (Schema::hasColumn($record->getTable(), 'invoice_file')) {
                                $payload['invoice_file'] = $invoicePath;
                            }

                            $record->update($payload);

                            // 2) تحويل ملكية المهمة لمدير القسم + الحالة materials_done
                            /** @var ProductionTask|null $task */
                            $task = $record->task()->lockForUpdate()->first();
                            if ($task && ! in_array($task->status, ['completed','cancelled'])) {
                                $dept            = $task->department;
                                $departmentOwner = $dept?->manager_id ?? $dept?->head_user_id ?? null;

                                $this->updateTaskStateAndOwnership($task, [
                                    'status'             => 'materials_done',
                                    'current_owner_role' => 'department_manager',
                                    'current_owner_user' => $departmentOwner,
                                ]);
                            }
                        });

                        Notification::make()
                            ->title('تم تأكيد توفير الخامات وتسجيل بيانات الفاتورة')
                            ->success()
                            ->send();
                    }),

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
