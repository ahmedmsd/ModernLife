<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Models\ProductionTask;
use Filament\Infolists\Infolist;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Illuminate\Support\Carbon;
use Filament\Infolists\Components\{Section, Grid, TextEntry, IconEntry};
use Filament\Pages\Page;

use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ViewMaterialRequest extends Page implements HasInfolists
{
    use InteractsWithInfolists;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'تفاصيل طلب الخامات';
    protected static ?string $title           = 'تفاصيل طلب الخامات';
    protected static ?string $navigationGroup = 'المشتريات';
    protected static ?string $slug            = 'purchasing/materials-requests/{record}';
    protected static string $view = 'filament.pages.purchasing.view-material-request';

    public MaterialRequest $record;

    public function mount(MaterialRequest $record): void
    {
        $this->record = $record->load([
            'task.project.productionRequest',
            'task.employee',
            'department',
            'requestedBy',
            'providedBy',
        ]);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function getHeading(): string
    {
        return "طلب خامات #{$this->record->id}";
    }

    public function getHeaderActions(): array
    {
        return [
            // 1) اعتماد المشتريات: requested -> approved
            Action::make('approvePurchasing')
                ->label('اعتماد طلب الشراء')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () =>
                    auth()->user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && $this->record->status === 'requested'
                )
                ->form([
                    Forms\Components\TextInput::make('estimated_cost')->label('التكلفة التقديرية')->numeric()->required(),
                    Forms\Components\DateTimePicker::make('expected_delivery_at')->label('التاريخ المتوقع للتسليم')->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(3),
                ])
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $this->record->update([
                            'status'               => 'approved',
                            'estimated_cost'       => (float) $data['estimated_cost'],
                            'expected_delivery_at' => $data['expected_delivery_at'],
                            'approved_at'          => now(),
                            'approved_by'          => auth()->id(),
                            'note'                 => trim(($this->record->note ? $this->record->note."\n\n" : '').($data['note'] ?? '')),
                        ]);

                        /** @var ProductionTask|null $task */
                        $task = $this->record->task()->lockForUpdate()->first();
                        if ($task) {
                            $payload = ['status' => 'materials_wait'];
                            if (Schema::hasColumn($task->getTable(),'current_owner_role')) {
                                $payload['current_owner_role'] = 'purchasing_manager';
                            }
                            if (Schema::hasColumn($task->getTable(),'current_owner_user_id')) {
                                $payload['current_owner_user_id'] = null;
                            }
                            $task->update($payload);
                        }
                    });

                    Notification::make()->success()->title('تم اعتماد طلب الشراء')->send();
                    $this->refreshRecord();
                }),

            // 2) تأكيد توفير الخامات: approved -> fulfilled (+فاتورة)
            Action::make('confirmMaterials')
                ->label('تأكيد توفير الخامات')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () =>
                    auth()->user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && $this->record->status === 'approved'
                    && is_null($this->record->provided_at)
                )
                ->form([
                    Forms\Components\TextInput::make('po_number')->label('رقم الطلب/مرجع'),
                    Forms\Components\TextInput::make('invoice_no')->label('رقم الفاتورة')->required(),
                    Forms\Components\TextInput::make('actual_cost')->label('مبلغ الفاتورة')->numeric()->required(),
                    Forms\Components\DatePicker::make('invoice_date')->label('تاريخ الفاتورة')->displayFormat('Y-m-d')->native(false)->required(),
                    Forms\Components\FileUpload::make('invoice_file')
                        ->label('فاتورة الشراء (PDF/صورة)')
                        ->disk('public')->directory('materials_invoices')
                        ->preserveFilenames()->openable()->downloadable()
                        ->maxSize(10240)->acceptedFileTypes(['application/pdf','image/*']),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $invoicePath = $this->record->invoice_file;
                        if (! empty($data['invoice_file'])) {
                            if ($data['invoice_file'] instanceof UploadedFile) {
                                $invoicePath = $data['invoice_file']->store('materials_invoices', 'public');
                            } elseif (is_string($data['invoice_file'])) {
                                $invoicePath = $data['invoice_file'];
                            }
                        }

                        $payload = [
                            'status'      => 'fulfilled',
                            'po_number'   => $data['po_number'] ?: $this->record->po_number,
                            'provided_by' => auth()->id(),
                            'provided_at' => now(),
                            'note'        => trim(($this->record->note ? $this->record->note."\n\n" : '').($data['note'] ?? '')),
                        ];
                        if (Schema::hasColumn($this->record->getTable(), 'actual_cost'))  $payload['actual_cost']  = (float) $data['actual_cost'];
                        if (Schema::hasColumn($this->record->getTable(), 'invoice_date'))  $payload['invoice_date'] = $data['invoice_date'];
                        if (Schema::hasColumn($this->record->getTable(), 'invoice_no'))    $payload['invoice_no']   = $data['invoice_no'];
                        if (Schema::hasColumn($this->record->getTable(), 'invoice_file'))  $payload['invoice_file'] = $invoicePath;

                        $this->record->update($payload);

                        /** @var ProductionTask|null $task */
                        $task = $this->record->task()->lockForUpdate()->first();
                        if ($task && ! in_array($task->status, ['completed','cancelled'])) {
                            $dept  = $task->department;
                            $owner = $dept?->manager_id ?? $dept?->head_user_id ?? null;

                            $payload = ['status' => 'materials_done'];
                            if (Schema::hasColumn($task->getTable(),'current_owner_role'))    $payload['current_owner_role'] = 'department_manager';
                            if (Schema::hasColumn($task->getTable(),'current_owner_user_id')) $payload['current_owner_user_id'] = $owner;
                            $task->update($payload);
                        }
                    });

                    Notification::make()->success()->title('تم تسجيل التوريد وبيانات الفاتورة')->send();
                    $this->refreshRecord();
                }),

            // 3) إلغاء/رفض الطلب
            // 3) إلغاء/رفض الطلب — محدث ليرجعها لمدير القسم
            Action::make('cancelRequest')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () =>
                    auth()->user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && in_array($this->record->status, ['requested','approved'])
                )
                ->form([
                    Forms\Components\Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    DB::transaction(function () use ($data) {
                        $reason = trim((string)($data['reason'] ?? ''));

                        // (1) حدّث طلب الخامات
                        $mrPayload = [
                            'status' => 'cancelled',
                            'note'   => trim(($this->record->note ? $this->record->note."\n\n" : '') . '[رفض المشتريات]: ' . $reason),
                        ];
                        if (\Illuminate\Support\Facades\Schema::hasColumn($this->record->getTable(), 'cancelled_at')) {
                            $mrPayload['cancelled_at'] = now();
                        }
                        if (\Illuminate\Support\Facades\Schema::hasColumn($this->record->getTable(), 'cancelled_by')) {
                            $mrPayload['cancelled_by'] = auth()->id();
                        }
                        $this->record->update($mrPayload);

                        // (2) أعد المهمة لمدير القسم بحالة on_hold
                        /** @var ProductionTask|null $task */
                        $task = $this->record->task()->lockForUpdate()->first();
                        if ($task && ! in_array($task->status, ['completed','closed','cancelled'], true)) {
                            $dept    = $task->department;
                            $ownerId = $dept?->manager_id ?? $dept?->head_user_id ?? null;

                            $taskPayload = [
                                'status' => 'on_hold', // ← حالة موجودة بالـ ENUM
                            ];
                            if (\Illuminate\Support\Facades\Schema::hasColumn($task->getTable(), 'current_owner_role')) {
                                $taskPayload['current_owner_role'] = 'department_manager';
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn($task->getTable(), 'current_owner_user_id')) {
                                $taskPayload['current_owner_user_id'] = $ownerId;
                            }
                            // أوقات خطية مفيدة
                            if (\Illuminate\Support\Facades\Schema::hasColumn($task->getTable(), 'received_by_owner_at')) {
                                $taskPayload['received_by_owner_at'] = null;
                            }
                            if (\Illuminate\Support\Facades\Schema::hasColumn($task->getTable(), 'sent_to_owner_at')) {
                                $taskPayload['sent_to_owner_at'] = now();
                            }

                            $task->update($taskPayload);

                            if (method_exists($task, 'logs')) {
                                $task->logs()->create([
                                    'type'        => 'materials_request_rejected', // اسم دالّ
                                    'data'        => [
                                        'mr_id'  => $this->record->id,
                                        'by'     => auth()->id(),
                                        'role'   => 'purchasing_manager',
                                        'to_role'=> 'department_manager',
                                        'new_status' => 'on_hold',
                                        'reason' => $reason,
                                    ],
                                    'happened_at' => now(),
                                ]);
                            }
                        }
                    });

                    Notification::make()
                        ->warning()
                        ->title('تم رفض الطلب وإرجاع المهمة لمدير القسم (حالة: موقوفة مؤقتًا)')
                        ->send();

                    $this->refreshRecord();
                }),
        ];
    }

    protected function refreshRecord(): void
    {
        $this->record->refresh()->load([
            'task.project.productionRequest',
            'task.employee',
            'department',
            'requestedBy',
            'providedBy',
        ]);
        $this->dispatch('close-modal', id: 'filament.actions.modal');
        $this->js('$wire.$refresh()');
    }

    // ====== بقية الكلاس (infolist/status helpers) كما هو ======
    protected function statusLabel(?string $s): string
    {
        return match ($s) {
            'requested' => 'بانتظار اعتماد المشتريات',
            'approved'  => 'بانتظار التوريد',
            'fulfilled' => 'مورَّد',
            'cancelled' => 'ملغى',
            default     => '—',
        };
    }
    protected function statusColor(?string $s): string
    {
        return match ($s) {
            'requested' => 'warning',
            'approved'  => 'info',
            'fulfilled' => 'success',
            'cancelled' => 'gray',
            default     => 'secondary',
        };
    }

    public function requestInfolist(Infolist $infolist): Infolist
    {
        $r = $this->record;

        return $infolist
            ->record($r)
            ->schema([
                Section::make('بيانات عامة')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('id')->label('#')->badge()->color('gray'),
                        TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                        TextEntry::make('task.id')->label('رقم المهمة')->placeholder('—'),
                        TextEntry::make('task.project.project_name')->label('المشروع')->placeholder('—'),
                        TextEntry::make('requestedBy.name')->label('مقدّم الطلب')
                            ->getStateUsing(fn () => ($r->requestedBy?->name) ?? ($r->task?->employee?->employee_name) ?? '—'),
                        TextEntry::make('requested_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i'),
                        TextEntry::make('task.estimated_cost')->label('الميزانية'),
                        TextEntry::make('expected_delivery_at')->label('موعد التوريد (متوقّع)')->dateTime('Y-m-d H:i'),
                        TextEntry::make('status')->label('الحالة')->badge()
                            ->color(fn ($state) => $this->statusColor($state))
                            ->formatStateUsing(fn ($state) => $this->statusLabel($state)),
                        TextEntry::make('note')->label('المطلوبات/ملاحظات')->columnSpanFull()->markdown(),
                    ]),
                Section::make('إحصائيات زمنية')
                    ->columns(4)
                    ->schema([
                        // 1) التواريخ الأساسية
                        TextEntry::make('expected_delivery_at')
                            ->label('التاريخ المتوقع للتوريد')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('—'),

                        TextEntry::make('provided_at')
                            ->label('التاريخ الفعلي للتوريد')
                            ->dateTime('Y-m-d H:i')
                            ->placeholder('—'),

                        // 2) الفرق بين المتوقع والفعلي (أبكر/في الموعد/متأخر)
                        TextEntry::make('expected_vs_actual_delta')
                            ->label('الفرق بين المتوقع والفعلي')
                            ->state(function ($record) {
                                if (!$record->expected_delivery_at || !$record->provided_at) {
                                    return '—';
                                }

                                $exp = Carbon::parse($record->expected_delivery_at);
                                $act = Carbon::parse($record->provided_at);

                                // احسب الفرق بالدقائق ثم حوله لأيام/ساعات/دقائق
                                $mins = $exp->diffInMinutes($act, false); // سالب = قبل الموعد
                                if ($mins === 0) {
                                    return 'في الموعد تمامًا';
                                }

                                $abs = abs($mins);
                                $days  = intdiv($abs, 60 * 24);
                                $hours = intdiv($abs % (60 * 24), 60);
                                $m     = $abs % 60;

                                $parts = [];
                                if ($days)  $parts[] = $days.' يوم';
                                if ($hours) $parts[] = $hours.' ساعة';
                                if ($m || (!$days && !$hours)) $parts[] = $m.' دقيقة';

                                return $mins < 0
                                    ? 'قبل الموعد بـ ' . implode(' و ', $parts)
                                    : 'متأخر عن الموعد بـ ' . implode(' و ', $parts);
                            })
                            ->badge()
                            ->color(function ($record) {
                                if (!$record->expected_delivery_at || !$record->provided_at) {
                                    return 'gray';
                                }
                                $exp = Carbon::parse($record->expected_delivery_at);
                                $act = Carbon::parse($record->provided_at);
                                $mins = $exp->diffInMinutes($act, false);
                                if ($mins === 0)   return 'success'; // في الموعد
                                if ($mins < 0)     return 'info';    // أبكر من الموعد
                                return 'danger';                     // متأخر
                            }),

                        // 3) المدة من إنشاء الطلب حتى التوريد
                        TextEntry::make('requested_to_provided_duration')
                            ->label('المدة من إنشاء الطلب حتى التوريد')
                            ->state(function ($record) {
                                if (!$record->requested_at || !$record->provided_at) {
                                    return '—';
                                }
                                $start = Carbon::parse($record->requested_at);
                                $end   = Carbon::parse($record->provided_at);
                                $total = $start->diffInMinutes($end);
                                $days  = intdiv($total, 1440);
                                $hours = intdiv($total % 1440, 60);
                                $mins  = $total % 60;

                                $parts = [];
                                if ($days)  $parts[] = $days.' يوم';
                                if ($hours) $parts[] = $hours.' ساعة';
                                if ($mins || (!$days && !$hours)) $parts[] = $mins.' دقيقة';
                                return implode(' و ', $parts);
                            })
                            ->placeholder('—'),

                        // 4) المدة من اعتماد المشتريات حتى التوريد
                        TextEntry::make('approved_to_provided_duration')
                            ->label('المدة من اعتماد المشتريات حتى التوريد')
                            ->state(function ($record) {
                                if (!$record->approved_at || !$record->provided_at) {
                                    return '—';
                                }
                                $start = Carbon::parse($record->approved_at);
                                $end   = Carbon::parse($record->provided_at);
                                $total = $start->diffInMinutes($end);
                                $days  = intdiv($total, 1440);
                                $hours = intdiv($total % 1440, 60);
                                $mins  = $total % 60;

                                $parts = [];
                                if ($days)  $parts[] = $days.' يوم';
                                if ($hours) $parts[] = $hours.' ساعة';
                                if ($mins || (!$days && !$hours)) $parts[] = $mins.' دقيقة';
                                return implode(' و ', $parts);
                            })
                            ->placeholder('—'),
                    ]),
                Section::make('المشتريات')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('estimated_cost')->label('التكلفة التقديرية')->money('sar'),
                        TextEntry::make('actual_cost')->label('التكلفة الفعلية')->money('sar'),
                        TextEntry::make('po_number')->label('رقم الطلب/المرجع')->placeholder('—'),
                        TextEntry::make('po_file')->label('ملف PO')
                            ->formatStateUsing(fn ($state) => $state ? 'تنزيل' : '—')
                            ->url(fn ($state) => $state ? \Storage::url($state) : null, true)
                            ->icon(fn ($state) => $state ? 'heroicon-o-arrow-down-tray' : null),
                        TextEntry::make('providedBy.name')->label('مُنَفِّذ التوريد')->placeholder('—'),
                        TextEntry::make('provided_at')->label('تاريخ التوريد')->dateTime('Y-m-d H:i'),
                        TextEntry::make('created_at')->label('أُنشئ في')->dateTime('Y-m-d H:i'),
                        TextEntry::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i'),
                    ]),

                Section::make('بيانات الفاتورة')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('invoice_no')
                            ->label('رقم الفاتورة')
                            ->placeholder('—'),

                        TextEntry::make('invoice_date')
                            ->label('تاريخ الفاتورة')
                            ->date('Y-m-d')
                            ->placeholder('—'),

                        TextEntry::make('actual_cost')
                            ->label('مبلغ الفاتورة')
                            ->money('sar')
                            ->placeholder('—'),

                        TextEntry::make('invoice_file_link')
                            ->label('ملف الفاتورة')
                            ->getStateUsing(fn (MaterialRequest $r) => $r->invoice_file ? 'عرض / تنزيل' : '—')
                            ->icon(fn (MaterialRequest $r) => $r->invoice_file ? 'heroicon-o-arrow-down-tray' : 'heroicon-o-document')
                            ->url(fn (MaterialRequest $r) => $r->invoice_file ? \Storage::url($r->invoice_file) : null, true),
                    ])->visible(fn () => filled($this->record->invoice_no)
                        || filled($this->record->invoice_date)
                        || filled($this->record->actual_cost)
                        || filled($this->record->invoice_file)),

                Section::make('روابط سريعة')
                    ->columns(3)
                    ->schema([
                        IconEntry::make('project_link')
                            ->label('عرض المشروع')->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task?->project ? route('filament.admin.resources.projects.view', $r->task->project) : null, true)
                            ->helperText(fn () => $r->task?->project?->project_name ?? '—'),

                        IconEntry::make('task_link')
                            ->label('عرض المهمة')->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task ? route('filament.admin.resources.projects.view', $r->task->project) . '#tasks' : null, true)
                            ->helperText(fn () => $r->task?->id ? ('Task #' . $r->task->id) : '—'),

                        IconEntry::make('pr_link')
                            ->label('عرض طلب التصنيع')->icon('heroicon-o-arrow-top-right-on-square')
                            ->url(fn () => $r->task?->project?->productionRequest
                                ? route('filament.admin.resources.production-requests.view', $r->task->project->productionRequest) : null, true)
                            ->helperText(fn () => $r->task?->project?->productionRequest?->id ? ('PR #' . $r->task->project->productionRequest->id) : '—'),
                    ]),
            ]);
    }
}
