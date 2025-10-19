<?php

namespace App\Filament\Pages\Purchasing;

use App\Models\MaterialRequest;
use App\Models\ProductionTask;
use App\Models\TaskLog;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
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

    private function encodeUrlPath(string $url): string
    {
        $parts = parse_url($url);
        $scheme = $parts['scheme'] ?? null;
        $host   = $parts['host']   ?? null;
        $port   = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path   = $parts['path']   ?? '';
        $query  = isset($parts['query']) ? '?' . $parts['query'] : '';
        $frag   = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $segments = array_map(fn ($s) => rawurlencode(urldecode($s)), explode('/', ltrim($path, '/')));
        $encodedPath = '/' . implode('/', $segments);

        return $scheme && $host
            ? "{$scheme}://{$host}{$port}{$encodedPath}{$query}{$frag}"
            : "{$encodedPath}{$query}{$frag}";
    }

    private function fileUrl(string $path): ?string
    {
        if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return $this->encodeUrlPath($path);
        }
        try {
            if (\Storage::disk('public')->exists($path)) {
                return $this->encodeUrlPath(\Storage::disk('public')->url($path));
            }
            if (\Storage::exists($path)) {
                return $this->encodeUrlPath(\Storage::url($path));
            }
        } catch (\Throwable $e) {}
        return null;
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
                    Forms\Components\DatePicker::make('expected_delivery_at')->label('التاريخ المتوقع للتسليم')->required(),
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
                ->color('success')
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

            Action::make('purchasingAcknowledgeHold')
                ->label('تأكيد استلام المشتريات (إيقاف جزئي)')
                ->icon('heroicon-o-document-check')
                ->color('primary')
                ->visible(function () {
                    if (($this->record->current_owner_role ?? null) !== 'purchasing_manager') return false;
                    if (strtolower((string) $this->record->status) !== 'on_hold') return false;

                    // المرجع: أحدث استلام جزئي/مشكلة
                    $anchor = TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['materials_received_partial','materials_received_issue'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $anchor) return false;

                    $t  = $anchor->happened_at ?? $anchor->created_at;
                    $id = $anchor->id;

                    // لا يوجد purchasing_hold_ack بعد هذا المرجع
                    $ackAfter = TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'purchasing_hold_ack')
                        ->where(function ($q) use ($t, $id) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $id) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $id);
                                });
                        })
                        ->exists();

                    return ! $ackAfter;
                })
                ->form([
                    Forms\Components\TextInput::make('eta')->label('موعد التوريد المتوقع')->placeholder('YYYY-MM-DD'),
                    Forms\Components\TextInput::make('estimated_cost')->label('تكلفة تقديرية')->numeric(),
                    Forms\Components\Textarea::make('note')->label('ملاحظات')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    // لوج تأكيد استلام الإيقاف
                    $this->record->logs()->create([
                        'type'        => 'purchasing_hold_ack',
                        'data'        => [
                            'eta'            => $data['eta'] ?? null,
                            'estimated_cost' => isset($data['estimated_cost']) ? (float)$data['estimated_cost'] : null,
                            'note'           => $data['note'] ?? null,
                            'by'             => auth()->id(),
                        ],
                        'causer_id'   => auth()->id(),
                        'happened_at' => now(),
                    ]);

                    \Filament\Notifications\Notification::make()
                        ->success()->title('تم تأكيد استلام الإيقاف من المشتريات')->send();

                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),

            Action::make('materialsProvidedAfterHold')
                ->label('توفير وتوريد باقي الخامات')
                ->icon('heroicon-o-truck')
                ->color('success')
                ->visible(function () {
                    if (($this->record->current_owner_role ?? null) !== 'purchasing_manager') return false;
                    if (strtolower((string) $this->record->status) !== 'on_hold') return false;

                    $anchor = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['materials_received_partial','materials_received_issue'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $anchor) return false;
                    $t  = $anchor->happened_at ?? $anchor->created_at;
                    $id = $anchor->id;

                    $ackExists = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'purchasing_hold_ack')
                        ->where(function ($q) use ($t, $id) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $id) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $id);
                                });
                        })
                        ->exists();
                    if (! $ackExists) return false;

                    // لم يتم التوريد بعد هذا المرجع
                    $providedAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['materials_provided','materials_provided_after_hold'])
                        ->where(function ($q) use ($t, $id) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $id) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $id);
                                });
                        })
                        ->exists();
                    return ! $providedAfter;
                })
                ->form([
                    Forms\Components\DateTimePicker::make('provided_at')->label('تاريخ/وقت التوريد')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظات')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    // سجّل التوريد (اسم موحّد أو مخصص بعد الإيقاف)
                    $this->record->logs()->create([
                        'type'        => 'materials_provided',
                        'data'        => [
                            'provided_at' => $data['provided_at'],
                            'note'        => $data['note'] ?? null,
                            'by'          => auth()->id(),
                        ],
                        'causer_id'   => auth()->id(),
                        'happened_at' => now(),
                    ]);

                    // أعِد الحالة والملكية للقسم ليتابع
                    $this->record->forceFill([
                        'status'                => 'waiting_production',
                        'current_owner_role'    => 'department_manager',
                        // (اختياري) لو تريد تعيين مستخدم مدير القسم:
                        // 'current_owner_user_id' => $this->record->department?->manager_user_id,
                    ])->save();

                    \Filament\Notifications\Notification::make()
                        ->success()->title('تم توريد الخامات وإعادة المهمة للقسم')->send();

                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),



            // 3) إلغاء/رفض الطلب
            Action::make('cancelRequest')
                ->label('رفض الطلب')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () =>
                    auth()->user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && in_array($this->record->status, ['requested'])
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
            'requested'            => 'بانتظار اعتماد المشتريات',
            'approved'             => 'بانتظار الإصدار/التوريد',
            'ordered'              => 'تم إصدار أمر شراء',
            'partially_fulfilled'  => 'توريد جزئي',
            'on_hold'              => 'موقوف مؤقتًا',
            'fulfilled'            => 'مورَّد (مغلق)',
            'cancelled'            => 'ملغى',
            default                => '—',
        };
    }

    protected function statusColor(?string $s): string
    {
        return match ($s) {
            'requested'            => 'warning',
            'approved'             => 'info',
            'ordered'              => 'primary',
            'partially_fulfilled'  => 'warning',
            'on_hold'              => 'gray',
            'fulfilled'            => 'success',
            'cancelled'            => 'danger',
            default                => 'secondary',
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
                        TextEntry::make('department.dept_name')->label('القسم')->placeholder('—')->color('primary'),
                        TextEntry::make('task.id')->label('رقم المهمة')->placeholder('—')->color('primary'),
                        TextEntry::make('task.project.project_name')->label('المشروع')->placeholder('—')->color('primary'),
                        TextEntry::make('task.project.ProductionRequest.showroom.name')->label('المعرض')->placeholder('—')->color('primary'),

                        TextEntry::make('requestedBy.name')->label('مقدّم الطلب')
                            ->getStateUsing(fn () => ($r->requestedBy?->name) ?? ($r->task?->employee?->employee_name) ?? '—')->color('primary'),
                        TextEntry::make('requested_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i')->color('primary'),
                        TextEntry::make('task.estimated_cost')->label('الميزانية')->color('primary'),
                        TextEntry::make('status')->label('الحالة')->badge()
                            ->color(fn ($state) => $this->statusColor($state))
                            ->formatStateUsing(fn ($state) => $this->statusLabel($state)),
                        TextEntry::make('po_file')->label('ملف أمر الشراء المُعتمد من مدير المصنع')
                            ->formatStateUsing(fn ($state) => $state ? 'تنزيل' : '—')
                            ->url(fn ($state) => $state ? \Storage::url($state) : null, true)
                            ->icon(fn ($state) => $state ? 'heroicon-o-arrow-down-tray' : null)->badge(),
                        TextEntry::make('note')->label('المطلوبات/ملاحظات')->columnSpanFull()->markdown()->color('primary'),
                    ]),
                Section::make('استلام القسم للخامات')
                    ->columns(4)
                    ->visible(function () {
                        // ظهور هذا القسم لمن يهمه الاطلاع (مدير النظام + المشتريات)
                        return auth()->check()
                            && auth()->user()->hasAnyRole(['admin','super-admin','purchasing_manager'], 'web');
                    })
                    ->schema([
                        TextEntry::make('dept_receipt_result')
                            ->label('نتيجة الاستلام')
                            ->state(function (MaterialRequest $r) {
                                $log = TaskLog::query()
                                    ->with('causer')
                                    ->where('task_id', $r->task_id)
                                    ->whereIn('type', [
                                        'materials_received_ok',       // تم الاستلام (سليم)
                                        'materials_received_partial',  // استلام جزئي
                                        'materials_received_issue',    // استلام مع ملاحظات
                                    ])
                                    ->when($r->provided_at, function ($q) use ($r) {
                                        $q->where(function($qq) use ($r) {
                                            $qq->whereNotNull('happened_at')->where('happened_at', '>=', $r->provided_at)
                                                ->orWhere(function($qqq) use ($r) {
                                                    $qqq->whereNull('happened_at')->where('created_at', '>=', $r->provided_at);
                                                });
                                        });
                                    })
                                    ->latest('id')
                                    ->first();

                                if (! $log) return '—';

                                return match ($log->type) {
                                    'materials_received_ok'      => 'تم الاستلام',
                                    'materials_received_partial' => 'استلام جزئي',
                                    'materials_received_issue'   => 'استلام مع ملاحظات',
                                    default                      => '—',
                                };
                            })
                            ->badge()
                            ->color(function ($state) {
                                return match ($state) {
                                    'تم الاستلام'       => 'success',
                                    'استلام جزئي'       => 'warning',
                                    'استلام مع ملاحظات' => 'danger',
                                    default              => 'gray',
                                };
                            }),

                        TextEntry::make('dept_receipt_note')
                            ->label('ملاحظات مدير القسم')
                            ->columnSpan(3)
                            ->markdown()
                            ->state(function (MaterialRequest $r) {
                                $log = TaskLog::query()
                                    ->where('task_id', $r->task_id)
                                    ->whereIn('type', [
                                        'materials_received_ok',
                                        'materials_received_partial',
                                        'materials_received_issue',
                                    ])
                                    ->when($r->provided_at, function ($q) use ($r) {
                                        $q->where(function($qq) use ($r) {
                                            $qq->whereNotNull('happened_at')->where('happened_at', '>=', $r->provided_at)
                                                ->orWhere(function($qqq) use ($r) {
                                                    $qqq->whereNull('happened_at')->where('created_at', '>=', $r->provided_at);
                                                });
                                        });
                                    })
                                    ->latest('id')
                                    ->first();

                                return $log?->note
                                    ?? data_get($log, 'data.note')
                                    ?? '—';
                            }),

                        TextEntry::make('dept_receipt_by')
                            ->label('أكّد الاستلام')
                            ->state(function (MaterialRequest $r) {
                                $log = TaskLog::query()
                                    ->with('causer')
                                    ->where('task_id', $r->task_id)
                                    ->whereIn('type', [
                                        'materials_received_ok',
                                        'materials_received_partial',
                                        'materials_received_issue',
                                    ])
                                    ->when($r->provided_at, function ($q) use ($r) {
                                        $q->where(function($qq) use ($r) {
                                            $qq->whereNotNull('happened_at')->where('happened_at', '>=', $r->provided_at)
                                                ->orWhere(function($qqq) use ($r) {
                                                    $qqq->whereNull('happened_at')->where('created_at', '>=', $r->provided_at);
                                                });
                                        });
                                    })
                                    ->latest('id')
                                    ->first();

                                return $log?->causer?->name ?? '—';
                            }),

                        TextEntry::make('dept_receipt_at')
                            ->label('تاريخ التأكيد')
                            ->dateTime('Y-m-d H:i')
                            ->state(function (MaterialRequest $r) {
                                $log = TaskLog::query()
                                    ->where('task_id', $r->task_id)
                                    ->whereIn('type', [
                                        'materials_received_ok',
                                        'materials_received_partial',
                                        'materials_received_issue',
                                    ])
                                    ->when($r->provided_at, function ($q) use ($r) {
                                        $q->where(function($qq) use ($r) {
                                            $qq->whereNotNull('happened_at')->where('happened_at', '>=', $r->provided_at)
                                                ->orWhere(function($qqq) use ($r) {
                                                    $qqq->whereNull('happened_at')->where('created_at', '>=', $r->provided_at);
                                                });
                                        });
                                    })
                                    ->latest('id')
                                    ->first();

                                return ($log?->happened_at ?? $log?->created_at) ?: null;
                            }),
                    ]),

                Section::make('ملفات الطلب')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('agreement_file_link')
                            ->label('ملف الاتفاقية')
                            ->html()
                            ->state(function (MaterialRequest $r) {
                                $path = $r->task?->project?->productionRequest?->agreement_file;
                                if (blank($path)) return '<span style="opacity:.7">—</span>';
                                $url = $this->fileUrl($path);
                                if (!$url) return '<span style="opacity:.7">—</span>';
                                $name = e(basename($path));
                                return '<a href="'.e($url).'" target="_blank" style="color:#2563eb;text-decoration:underline;font-weight:600;">'.$name.' ▸</a>';
                            }),

                        TextEntry::make('manufacturing_file_link')
                            ->label('ملف التصنيع (للقسم)')
                            ->html()
                            ->state(function (MaterialRequest $r) {
                                $task = $r->task;
                                $pr   = $task?->project?->productionRequest;
                                if (!$task || !$pr) return '<span style="opacity:.7">—</span>';

                                $file = $pr->files()->where('department_id', $task->department_id)->latest()->first();
                                if (!$file || blank($file->file_path)) return '<span style="opacity:.7">—</span>';

                                $url  = $this->fileUrl($file->file_path);
                                if (!$url) return '<span style="opacity:.7">—</span>';

                                $name = e($file->file_name ?? basename($file->file_path));
                                return '<a href="'.e($url).'" target="_blank" style="color:#16a34a;text-decoration:underline;font-weight:600;">'.$name.' ▸</a>';
                            }),
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
