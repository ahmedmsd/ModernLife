<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use App\Models\TaskLog;
use App\Models\User;
use App\Services\Tasks\TaskTimerService;
use App\Services\Tasks\TaskWorkflowService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\Concerns\HasRelationManagers;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ProductionTask;
use App\Models\TaskComment;
use App\Support\Tasks\TaskPageHelper;
use Illuminate\Support\Str;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Notifications\Notification as FNotification;

class ViewTask extends ViewRecord
{
    use HasRelationManagers;

    protected static string $resource = TaskResource::class;
    protected static ?string $title = 'عرض مهمة التصنيع ';

    protected ?TaskPageHelper $helper = null;
    protected ?TaskWorkflowService $workflow = null;

    private function helper(): TaskPageHelper
    {
        return $this->helper ??= app(TaskPageHelper::class);
    }

    private function workflow(): TaskWorkflowService
    {
        return $this->workflow ??= app(TaskWorkflowService::class);
    }

    public function mount($record): void
    {
        parent::mount($record);

        $this->helper();
        $this->workflow();

        $this->record->load([
            'project.productionRequest.showroom',
            'project.client',
            'department',
            'assignedUser.employee',   // بدل employee مباشرة
            'logs.causer',
            'materialRequests',
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->helper()->parentTasksUrl(Auth::user(), $this->record);
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->helper()->parentTasksUrl(Auth::user(), $this->record) => $this->helper()->parentTasksLabel(Auth::user()),
            $this->getBreadcrumb(),
        ];
    }

    protected function getHeaderActions(): array
    {
        $u = Auth::user();

        return [
            /* تعليق سريع */
            Action::make('addComment')
                ->label('تعليق سريع')->icon('heroicon-m-chat-bubble-left-right')
                ->form([
                    Textarea::make('body')->label('نص التعليق')->required()->autosize(),
                    FileUpload::make('attachments')->label('مرفقات (اختياري)')
                        ->multiple()->directory('task-comments')->downloadable()->openable(),
                ])
                ->action(function (array $data) {
                    $comment = TaskComment::create([
                        'task_id'     => $this->record->id,
                        'user_id'     => auth()->id(),
                        'body'        => $data['body'],
                        'attachments' => isset($data['attachments']) ? array_values((array) $data['attachments']) : null,
                    ]);

                    $this->record->loadMissing([
                        'department.managerUser',
                        'project.productionRequest.showroom.manager',
                    ]);

                    $taskUrl = \App\Filament\Resources\TaskResource::getUrl('view', ['record' => $this->record]);

                    $recipients = collect();

                    if ($this->record->created_by && ($u = User::find($this->record->created_by))) {
                        $recipients->push($u);
                    }

                    if ($this->record->current_owner_user_id && ($owner = User::find($this->record->current_owner_user_id))) {
                        $recipients->push($owner);
                    }

                    $deptManagerUser = optional(optional($this->record->department)->manager)->user;
                    if ($deptManagerUser) {
                        $recipients->push($deptManagerUser);
                    }

                    $showroomManagerUser = optional(
                        optional(
                            optional($this->record->project)->productionRequest
                        )->showroom
                    )->manager?->user;

                    if ($showroomManagerUser) {
                        $recipients->push($showroomManagerUser);
                    }

                    $factoryManagers = User::role('factory_manager')->get();
                    if ($factoryManagers->isNotEmpty()) {
                        $recipients = $recipients->merge($factoryManagers);
                    }

                    $recipients = $recipients
                        ->filter()
                        ->unique('id')
                        ->reject(fn ($user) => (int) $user->id === (int) auth()->id())
                        ->values();

                    if ($recipients->isNotEmpty()) {
                        \Filament\Notifications\Notification::make()
                            ->title("تعليق جديد على المهمة #{$this->record->id}")
                            ->icon('heroicon-m-chat-bubble-left-right')
                            ->body(\Illuminate\Support\Str::limit(strip_tags((string) $data['body']), 180))
                            ->actions([
                                NotificationAction::make('عرض المهمة')
                                    ->button()
                                    ->url($taskUrl)
                                    ->openUrlInNewTab(),
                            ])
                            ->sendToDatabase($recipients);
                    }

                    \Filament\Notifications\Notification::make()
                        ->title('تم إضافة التعليق')
                        ->success()
                        ->send();
                }),

            /* إسناد لمدير القسم (بناءً على user_id) */
            Action::make('assign_to_dept_manager')
                ->label('إسناد لمدير القسم')
                ->icon('heroicon-o-user-plus')
                ->visible(function () {
                    $u = Auth::user();
                    return $u?->hasAnyRole(['factory_manager','admin','super-admin'])
                        && blank($this->record->assigned_to_user_id);
                })
                ->form([
                    Forms\Components\Select::make('user_id')
                        ->label('المسؤول')
                        ->searchable()
                        ->preload()
                        ->options(function () {
                            $deptId = $this->record->department_id;

                            return User::query()
                                ->role('department_manager')
//                                ->when($deptId, function ($q) use ($deptId) {
//                                    $q->whereHas('employee', fn ($q2) => $q2->where('department_id', $deptId));
//                                })
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->required(),
                    Forms\Components\DatePicker::make('due_date')
                        ->label('تاريخ التسليم المتوقع')
                        ->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->assignToDeptManager(
                        $this->record,
                        (int) $data['user_id'],
                        $data['due_date']
                    );
                    \Filament\Notifications\Notification::make()->success()->title('تم الإسناد')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* مدير القسم يؤكد الاستلام */
            Action::make('acknowledge')
                ->label('تأكيد استلام المهمة (مدير القسم)')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(fn () => $this->helper()->canDeptAcknowledge($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->deptAcknowledge($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* طلب خامات */
            Action::make('request_materials')
                ->label('طلب خامات')->icon('heroicon-o-truck')->color('info')
                ->visible(fn () => $this->helper()->canRequestMaterials($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات / تفاصيل المطلوب')->rows(3)->required(),
                    Forms\Components\FileUpload::make('po_file')
                        ->label('ملف أمر الشراء (PO) المُعتمد من مدير المصنع')->disk('public')
                        ->directory('purchase_orders/' . now()->format('Y/m'))
                        ->acceptedFileTypes(['application/pdf','image/*'])->maxSize(20_480)
                        ->openable()->downloadable()->moveFiles()->visibility('public')->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->requestMaterials($this->record, $data['note'], $data['po_file']);
                    FNotification::make()->success()->title('تم إرسال طلب الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('deptRejectToFactory')
                ->label('رفض المهمة وإعادتها للمصنع')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn () => $this->helper()->canDeptReject($this->record, Auth::user()))
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('سبب الإعادة')
                        ->required()
                        ->rows(3),
                ])
                ->action(function ($record, array $data) {
                    $this->workflow()->deptRejectToFactory($record, (string) $data['reason']);
                    FNotification::make()->title('تمت إعادة المهمة إلى مدير المصنع')->success()->send();
                    $this->redirect(request()->header('Referer') ?? url()->current());
                }),

            /* المشتريات تؤكد التوريد */
            Action::make('materials_provided')
                ->label('تأكيد توفر الخامات')->icon('heroicon-o-archive-box')->color('success')
                ->visible(fn () => $this->helper()->canMaterialsProvided($this->record, Auth::user()))
                ->form([
                    Forms\Components\TextInput::make('actual_cost')->label('قيمة الشراء الفعلية')->numeric()->required(),
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->materialsProvided(
                        $this->record,
                        (float) $data['actual_cost'],
                        $data['note'] ?? null
                    );
                    Notification::make()->success()->title('تم توفير الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* المشتريات تؤكد الاستلام */
            Action::make('purchasing_receive')
                ->label('تأكيد استلام طلب الخامات (المشتريات)')->icon('heroicon-o-check-badge')->color('primary')
                ->visible(fn () => $this->helper()->canPurchasingReceive($this->record, Auth::user()))
                ->form([
                    Forms\Components\TextInput::make('po_number')->label('رقم الطلب/المرجع'),
                    Forms\Components\DateTimePicker::make('expected_delivery_at')->label('موعد التوريد المتوقع')->required(),
                    Forms\Components\TextInput::make('estimated_cost')->label('التكلفة المتوقعة')->numeric(),
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->purchasingReceive($this->record, $data);
                    Notification::make()->success()->title('تم تسجيل استلام طلب الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('materials_receipt')
                ->label('تسجيل استلام الخامات (مدير القسم)')
                ->icon('heroicon-o-archive-box')
                ->color('success')
                ->visible(fn () => $this->helper()->canMaterialsReceivedOk($this->record, Auth::user()))
                ->form([
                    Forms\Components\Select::make('receipt_type')
                        ->label('حالة الاستلام')
                        ->options([
                            'ok'             => 'استلام كلي (جاهز لبدء التصنيع)',
                            'partial_allow'  => 'استلام جزئي — مع السماح ببدء التصنيع',
                            'partial_hold'   => 'استلام جزئي — إيقاف حتى استكمال النواقص',
                            'issue'          => 'استلام به مشكلة — إيقاف وتحويل للمشتريات',
                        ])
                        ->native(false)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state) {
                            $needPlan = in_array($state, ['ok','partial_allow'], true);

                            if (! $needPlan) {
                                return;
                            }

                            $currentStart   = optional($this->record->planned_start_at)->toDateString();
                            $currentEnd     = optional($this->record->planned_end_at)->toDateString();
                            $currentInstall = optional($this->record->planned_install_at)->toDateString();

                            if (!$get('planned_start') && $currentStart)   $set('planned_start', $currentStart);
                            if (!$get('planned_end') && $currentEnd)       $set('planned_end', $currentEnd);
                            if (!$get('planned_install') && $currentInstall) $set('planned_install', $currentInstall);
                        }),

                    Forms\Components\DatePicker::make('planned_start')
                        ->label('بداية التصنيع (متوقعة)')
                        ->native(false)
                        ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->default(fn () => optional($this->record->planned_start_at)->toDateString())
                        ->afterStateHydrated(function (Get $get, Set $set, $state) {
                            if (!$state && in_array($get('receipt_type'), ['ok','partial_allow'], true)) {
                                $val = optional($this->record->planned_start_at)->toDateString();
                                if ($val) $set('planned_start', $val);
                            }
                        }),

                    Forms\Components\DatePicker::make('planned_end')
                        ->label('نهاية التصنيع (متوقعة)')
                        ->native(false)
                        ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->default(fn () => optional($this->record->planned_end_at)->toDateString())
                        ->afterStateHydrated(function (Get $get, Set $set, $state) {
                            if (!$state && in_array($get('receipt_type'), ['ok','partial_allow'], true)) {
                                $val = optional($this->record->planned_end_at)->toDateString();
                                if ($val) $set('planned_end', $val);
                            }
                        }),

                    Forms\Components\DatePicker::make('planned_install')
                        ->label('موعد التركيب (متوقع)')
                        ->native(false)
                        ->visible(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->required(fn (Get $get) => in_array($get('receipt_type'), ['ok','partial_allow'], true))
                        ->default(fn () => optional($this->record->planned_install_at)->toDateString())
                        ->afterStateHydrated(function (Get $get, Set $set, $state) {
                            if (!$state && in_array($get('receipt_type'), ['ok','partial_allow'], true)) {
                                $val = optional($this->record->planned_install_at)->toDateString();
                                if ($val) $set('planned_install', $val);
                            }
                        }),

                    Forms\Components\Textarea::make('note')
                        ->label('ملاحظات (اختياري)')
                        ->rows(3)
                        ->maxLength(1000),

                    Forms\Components\Textarea::make('missing_items')
                        ->label('تفاصيل البنود الناقصة')
                        ->rows(3)
                        ->visible(fn (Get $get) => in_array($get('receipt_type'), ['partial_allow','partial_hold'], true)),

                    Forms\Components\Textarea::make('issue_details')
                        ->label('تفاصيل المشكلة')
                        ->rows(3)
                        ->visible(fn (Get $get) => $get('receipt_type') === 'issue'),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $type = $data['receipt_type'];

                    $needPlan = in_array($type, ['ok','partial_allow'], true);
                    if ($needPlan) {
                        $start = Carbon::parse($data['planned_start']);
                        $end   = Carbon::parse($data['planned_end']);
                        $ins   = Carbon::parse($data['planned_install']);

                        if ($end->lt($start) || $ins->lt($end)) {
                            Notification::make()
                                ->danger()
                                ->title('تسلسل التواريخ غير صحيح')
                                ->body('يجب أن تكون نهاية التصنيع بعد بدايته، وموعد التركيب بعد نهاية التصنيع.')
                                ->send();
                            return;
                        }
                    }

                    switch ($type) {
                        case 'ok':
                            $this->workflow()->materialsReceivedOk(
                                $this->record,
                                $data['planned_start'],
                                $data['planned_end'],
                                $data['planned_install'],
                                $data['note'] ?? null
                            );
                            Notification::make()
                                ->success()->title('تم الاستلام الكلي — المهمة بانتظار بدء التصنيع')->send();
                            break;

                        case 'partial_allow':
                            $this->workflow()->materialsReceivedPartialAllowStart(
                                $this->record,
                                $data['planned_start'],
                                $data['planned_end'],
                                $data['planned_install'],
                                $data['note'] ?? null,
                                $data['missing_items'] ?? null
                            );
                            Notification::make()
                                ->success()->title('استلام جزئي (السماح بالبدء) — تم فتح طلب تكميلي')->send();
                            break;

                        case 'partial_hold':
                            $this->workflow()->materialsReceivedPartialHold(
                                $this->record,
                                $data['note'] ?? null,
                                $data['missing_items'] ?? null
                            );
                            Notification::make()
                                ->warning()->title('استلام جزئي — المهمة موقوفة حتى استكمال النواقص')->send();
                            break;

                        case 'issue':
                            $this->workflow()->materialsReceivedIssue(
                                $this->record,
                                $data['note'] ?? null,
                                $data['issue_details'] ?? null
                            );
                            Notification::make()
                                ->warning()->title('استلام به مشكلة — تم تحويل المهمة للمشتريات لمعالجة المشكلة')->send();
                            break;
                    }

                    $this->redirect($this->getRedirectUrl());
                }),

            /* بدء التصنيع */
            Action::make('start_production')
                ->label('بدء التصنيع')
                ->icon('heroicon-o-play')
                ->color('info')
                ->visible(function () {
                    $u = auth()->user();
                    if (! $u || ! $u->hasRole('department_manager','web')) return false;

                    if (($this->record->current_owner_role ?? null) !== 'department_manager') return false;

                    $status = strtolower((string) ($this->record->status ?? ''));
                    if (! in_array($status, ['waiting_production','rework'], true)) return false;

                    $anchor = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'manufacturing_ack_rework')
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $anchor) {
                        $anchor = \App\Models\TaskLog::query()
                            ->where('task_id', $this->record->id)
                            ->where(function ($q) {
                                $q->where('type', 'materials_received_ok')
                                    ->orWhere(function ($q2) {
                                        $q2->where('type', 'materials_received_partial')
                                            ->where('data->allow_start', true);
                                    })
                                    ->orWhere('type','planning_hint_set');
                            })
                            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                            ->first();
                    }

                    if (! $anchor) return false;

                    $anchorTime = $anchor->happened_at ?? $anchor->created_at;
                    $anchorId   = $anchor->id;

                    $startedAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'manufacturing_started')
                        ->where(function ($q) use ($anchorTime, $anchorId) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                                ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                                        ->where('id', '>', $anchorId);
                                });
                        })
                        ->exists();

                    return ! $startedAfter;
                })
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ البدء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->startProduction($this->record, $data['started_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم بدء التصنيع')->send();
                    $this->record->refresh();
                }),

            /* إنهاء التصنيع وإرسال للجودة */
            Action::make('finish_manufacturing_send_to_qa')
                ->label('إنهاء التصنيع وإرسال للجودة')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->helper()->canFinishManufacturing($this->record, Auth::user()))
                ->form([
                    Forms\Components\DateTimePicker::make('actual_finished_at')
                        ->label('تاريخ/وقت الانتهاء الفعلي')
                        ->native(false)
                        ->default(now())
                        ->required(),
                    Forms\Components\Textarea::make('note')
                        ->label('ملاحظات (اختياري)')
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $finished = Carbon::parse($data['actual_finished_at']);

                    $startField = $this->record->actual_start_at
                        ?? $this->record->started_at
                        ?? null;

                    if ($startField) {
                        $started = $startField instanceof Carbon
                            ? $startField
                            : Carbon::parse($startField);
                    } else {
                        $startLog = \App\Models\TaskLog::query()
                            ->where('task_id', $this->record->id)
                            ->where('type', 'manufacturing_started')
                            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                            ->first();

                        $started = $startLog
                            ? (($startLog->data['started_at'] ?? null)
                                ? Carbon::parse($startLog->data['started_at'])
                                : ($startLog->happened_at ?? $startLog->created_at))
                            : null;
                    }

                    if ($started && $finished->lt($started)) {
                        Notification::make()
                            ->danger()
                            ->title('تاريخ الانتهاء أقدم من تاريخ البدء')
                            ->body('يرجى ضبط وقت/تاريخ الانتهاء ليكون بعد وقت البدء.')
                            ->send();
                        return;
                    }

                    $this->workflow()->finishManufacturingAndSendToQA(
                        $this->record,
                        $data['actual_finished_at'],
                        $data['note'] ?? null
                    );

                    Notification::make()
                        ->success()
                        ->title('تم إنهاء التصنيع وإرسال المهمة للجودة')
                        ->send();

                    $this->redirect($this->getRedirectUrl());
                }),

            /* QA (بعد التصنيع) */
            Action::make('qaAcknowledgeManufacturing')
                ->label('تأكيد استلام الجودة (بعد التصنيع)')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                ->visible(fn () => $this->helper()->canQaAcknowledgeManufacturing($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->qaAcknowledgeManufacturing($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام الجودة')->send();
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),

            Action::make('approveManufacturingQA')
                ->label('اعتماد الجودة (بعد التصنيع)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn () => $this->helper()->canApproveManufacturingQA($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->approveManufacturingQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم اعتماد الجودة وتحويل المهمة للتركيب')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('rejectManufacturingQA')
                ->label('رفض الجودة (بعد التصنيع)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->helper()->canRejectManufacturingQA($this->record, Auth::user()))
                ->form([
                    Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->rejectManufacturingQA($this->record, $data['reason']);
                    Notification::make()
                        ->warning()->title('تم رفض الجودة وأعيدت للتصنيع')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* تأكيد استلام التصنيع (إعادة عمل) */
            Action::make('manufacturingAcknowledgeRework')
                ->label('تأكيد استلام التصنيع (إعادة عمل)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn () => $this->helper()->canManufacturingAcknowledgeRework($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->manufacturingAcknowledgeRework($this->record, $data['note'] ?? null);

                    Notification::make()
                        ->success()->title('تم تأكيد استلام التصنيع (إعادة عمل)')->send();

                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),

            /* تأكيد استلام التركيب (بعد اعتماد جودة التصنيع) */
            Action::make('installationAcknowledgeAfterQAApprove')
                ->label('تأكيد استلام التركيب (بعد اعتماد جودة التصنيع)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn () => $this->helper()->canInstallationAcknowledgeAfterQAApprove($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->installationAcknowledge($this->record);
                    Notification::make()
                        ->success()
                        ->title('تم تأكيد استلام قسم التركيب')
                        ->send();
                }),

            /* بدء التركيب */
            Action::make('startInstallation')
                ->label('بدء التركيب')->icon('heroicon-o-wrench-screwdriver')->color('info')
                ->visible(fn () => $this->helper()->canStartInstallation($this->record, Auth::user()))
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ/وقت البدء')->default(now())->required(),
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->startInstallation($this->record, $data['started_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم بدء التركيب')->send();
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),

            /* إنهاء التركيب وإرساله للجودة */
            Action::make('finishInstallationAndSendQA')
                ->label('إنهاء التركيب وإرسال للجودة')->icon('heroicon-o-paper-airplane')->color('success')
                ->visible(fn () => $this->helper()->canFinishInstallationToQA($this->record, Auth::user()))
                ->form([
                    Forms\Components\DateTimePicker::make('finished_at')->label('تاريخ/وقت الإنهاء')->default(now())->required(),
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->finishInstallationToQA($this->record, $data['finished_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم إرسال التركيب للجودة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* QA (بعد التركيب) */
            Action::make('qaAcknowledgeInstallation')
                ->label('تأكيد استلام الجودة (التركيب)')->icon('heroicon-o-inbox-arrow-down')->color('info')
                ->visible(fn () => $this->helper()->canQaAcknowledgeInstallation($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->qaAcknowledgeInstallation($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام الجودة للتركيب')->send();
                }),

            Action::make('approveInstallationQA')
                ->label('اعتماد الجودة (بعد التركيب)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn () => $this->helper()->canApproveInstallationQA($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->approveInstallationQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم اعتماد الجودة لما بعد التركيب')->send();
                }),

            Action::make('rejectInstallationQA')
                ->label('رفض الجودة (التركيب)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->helper()->canRejectInstallationQA($this->record, Auth::user()))
                ->form([
                    Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->rejectInstallationQA($this->record, $data['reason']);
                    Notification::make()
                        ->warning()->title('تم رفض الجودة وأُعيدت المهمة للتركيب')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* تأكيد استلام التركيب (إعادة عمل) */
            Action::make('installationAcknowledgeRework')
                ->label('تأكيد استلام التركيب (إعادة عمل)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(fn () => $this->helper()->canInstallationAcknowledgeRework($this->record, Auth::user()))
                ->form([
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->installationAcknowledgeRework($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم تأكيد استلام التركيب (إعادة عمل)')->send();
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),

            Action::make('hold')
                ->label('تعليق المهمة')
                ->icon('heroicon-o-pause-circle')
                ->color('warning')
                ->visible(function (ProductionTask $record): bool {
                    if (! auth()->check()) return false;
                    $u = auth()->user();

                    $isDeptManagerAndOwner =
                        $u->hasRole('department_manager')
                        && $record->current_owner_role === 'department_manager'
                        && (int) $record->current_owner_user_id === (int) $u->id;

                    $isPureFactoryManager =
                        $u->hasRole('factory_manager')
                        && $u->getRoleNames()->count() === 1;

                    return ! in_array($record->status, ['completed','closed'])
                        && ($isDeptManagerAndOwner || $isPureFactoryManager);
                })
                ->form([
                    Forms\Components\Select::make('type')
                        ->label('نوع التعليق')
                        ->options([
                            'awaiting_dependency' => 'بانتظار مهمة أخرى',
                            'awaiting_materials'  => 'بانتظار خامات',
                            'client_feedback'     => 'بانتظار رد العميل',
                            'other'               => 'أخرى',
                        ])->required(),
                    Forms\Components\Select::make('related_task_id')
                        ->label('المهمة المرتبطة')
                        ->searchable()
                        ->preload()
                        ->options(ProductionTask::query()
                            ->orderByDesc('id')
                            ->limit(200)->pluck('id','id'))
                        ->visible(fn ($get) => $get('type') === 'awaiting_dependency'),
                    Forms\Components\Textarea::make('reason')->label('السبب')->rows(2),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->action(function (array $data, ProductionTask $record) {
                    $data['created_by'] = auth()->id();
                    app(TaskTimerService::class)->startHold($record, $data);

                    Notification::make()
                        ->title('تم تعليق المهمة وإيقاف العدّ.')
                        ->success()
                        ->send();
                }),

            Action::make('resume')
                ->label('استئناف المهمة')
                ->icon('heroicon-o-play-circle')
                ->color('success')
                ->visible(function (ProductionTask $record): bool {
                    if (! auth()->check()) return false;
                    $u = auth()->user();

                    $isDeptManagerAndOwner =
                        $u->hasRole('department_manager')
                        && $record->current_owner_role === 'department_manager'
                        && (int) $record->current_owner_user_id === (int) $u->id;

                    $isPureFactoryManager =
                        $u->hasRole('factory_manager')
                        && $u->getRoleNames()->count() === 1;

                    return in_array($record->status, ['on_hold','blocked'])
                        && ($isDeptManagerAndOwner || $isPureFactoryManager);
                })
                ->action(function (ProductionTask $record) {
                    app(TaskTimerService::class)->endHold($record, 'Manual resume');

                    Notification::make()
                        ->title('تم استئناف المهمة وتشغيل العدّ.')
                        ->success()
                        ->send();
                }),

            /* سند استلام العميل → اكتمال */
            Action::make('uploadClientReceipt')
                ->label('رفع سند استلام العميل وإكمال المهمة')
                ->icon('heroicon-o-arrow-up-on-square')
                ->color('success')
                ->visible(fn () =>
                    Auth::user()?->hasRole('quality_manager')
                    && ! $this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->hasLog($this->record, 'qa_approved_installation')
                    && empty($this->record->client_receipt)
                )
                ->form([
                    Forms\Components\FileUpload::make('client_receipt')
                        ->label('سند استلام العميل')
                        ->disk('public')
                        ->directory(fn () => 'client-receipts/' . now()->format('Y/m'))
                        ->downloadable()
                        ->openable()
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(10240)
                        ->required(),
                    Forms\Components\DateTimePicker::make('actual_finished_at')->label('تاريخ/وقت الانتهاء الفعلي للمهمة')->native(false)->default(now())->required(),
                    Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->uploadClientReceiptAndComplete(
                        $this->record,
                        Arr::get($data, 'client_receipt'),
                        Arr::get($data, 'actual_finished_at'),
                        Arr::get($data, 'note')
                    );
                    Notification::make()->success()->title('اكتملت المهمة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        $h = $this->helper();

        return $infolist->schema([
            Section::make('ملاحظات العميل الخاصة بالدفع')
                ->description('يرجى مراجعتها قبل أي إجراء')
                ->icon('heroicon-o-exclamation-triangle')
                ->collapsible(false)
                ->visible(fn ($record) => filled(optional($record->project?->client)->notes))
                ->schema([
                    TextEntry::make('project.client.notes')
                        ->label(false)
                        ->state(fn ($record) => nl2br(e(optional($record->project?->client)->notes)))
                        ->visible(fn ($record) => filled(optional($record->project?->client)->notes))
                        ->html()
                        ->extraAttributes([
                            'style' => '
                                        display: block;
                                        background: linear-gradient(90deg, #ffecb3, #fff8e1);
                                        border-left: 6px solid #f59e0b;
                                        padding: 16px 20px;
                                        font-size: 1.1rem;
                                        font-weight: 600;
                                        color: #92400e;
                                        border-radius: 8px;
                                        margin-bottom: 20px;
                                    ',
                        ]),
                ]),

            Section::make('بيانات المهمة')->schema([
                TextEntry::make('id')->label('رقم المهمة')->color('primary'),
                TextEntry::make('project.project_name')->label('المشروع')->placeholder('—')->color('primary'),
                TextEntry::make('department.dept_name')->label('القسم')->placeholder('—')->color('primary'),

                // عرض المسؤول اعتمادًا على assignedUser
                TextEntry::make('responsible_name')
                    ->label('المسؤول')
                    ->state(function (ProductionTask $record) {
                        $user = $record->assignedUser;
                        if (! $user) {
                            return '—';
                        }

                        if ($user->employee && $user->employee->employee_name) {
                            return $user->employee->employee_name;
                        }

                        return $user->name ?? $user->email ?? '—';
                    })
                    ->placeholder('—')
                    ->color('primary'),

                TextEntry::make('showroom_name')
                    ->label('المعرض')
                    ->getStateUsing(fn (ProductionTask $record) =>
                    $record->project?->productionRequest?->showroom?->name ?: '—'
                    )
                    ->placeholder('—')->color('primary'),
                TextEntry::make('status')->label('الحالة')
                    ->formatStateUsing(fn ($state) => $h->statusAr($state instanceof \BackedEnum ? $state->value : $h->normalizeStatus((string) $state)))
                    ->badge()
                    ->color(fn ($state) => $h->statusColor($state instanceof \BackedEnum ? $state->value : $h->normalizeStatus((string) $state)))
                    ->placeholder('—'),
                TextEntry::make('project.productionRequest.created_at')->label('تاريخ طلب التصنيع')->date()->badge()->color('success'),
                TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('—')->color('primary'),
                TextEntry::make('planned_start_at')->label('بداية التصنيع (خطة)')->date()->placeholder('—')->color('primary'),
                TextEntry::make('planned_end_at')->label('نهاية التصنيع (خطة)')->date()->placeholder('—')->color('primary'),
                TextEntry::make('planned_install_at')->label('التركيب المتوقع')->date()->placeholder('—')->color('primary'),
                TextEntry::make('current_owner_role')->label('المالك الحالي (الدور)')
                    ->formatStateUsing(fn ($state) => $state
                        ? match (strtolower(trim($state))) {
                            'department_manager'   => 'مدير القسم',
                            'purchasing_manager'   => 'مدير المشتريات',
                            'quality_manager'      => 'مدير الجودة',
                            'installation_manager' => 'مسؤول التركيب',
                            'factory_manager'      => 'التصنيع',
                            default                => $state,
                        }
                        : '—')->color('primary'),
                TextEntry::make('sent_to_owner_at')->label('أُرسل للمالك')->dateTime()->placeholder('—')->color('primary'),
                TextEntry::make('received_by_owner_at')->label('مؤكد الاستلام')->dateTime()->placeholder('—')->color('primary'),
            ])->columns(2),

            Section::make('سجل الحركات')
                ->schema([
                    ViewEntry::make('logs_timeline')
                        ->view('filament.task.logs-timeline')
                        ->state(function (ProductionTask $record) {
                            $logs = TaskLog::query()
                                ->where('task_id', $record->id)
                                ->core()
                                ->with('causer')
                                ->orderByRaw('COALESCE(happened_at, created_at) DESC')
                                ->take(500)
                                ->get();

                            $deduped = $logs->unique(function ($log) {
                                $sec = optional($log->happened_at ?: $log->created_at)->format('Y-m-d H:i:s');
                                return "{$log->type}|{$log->causer_id}|{$sec}";
                            })->values();

                            return $deduped;
                        }),
                ])
                ->columnSpanFull(),

            Section::make('ملفات المهمة')
                ->schema([
                    TextEntry::make('agreement_file')
                        ->label('ملف الاتفاقية')
                        ->html()
                        ->visible(fn () =>
                            auth()->check() &&
                            auth()->user()->hasAnyRole([
                                'admin',
                                'super-admin',
                                'super_admin',
                                'factory_manager',
                                'purchasing_manager',
                            ])
                        )
                        ->state(function (ProductionTask $record) {
                            $pr = $record->project?->productionRequest;
                            if (! $pr || blank($pr->agreement_file)) {
                                return '<span style="opacity:.7">—</span>';
                            }
                            $url  = Storage::disk('public')->url($pr->agreement_file);
                            $name = e(basename($pr->agreement_file));
                            return '<a href="'.e($url).'" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                        }),

                    TextEntry::make('manufacturing_file')
                        ->label('ملف التصنيع (للقسم)')
                        ->html()
                        ->state(function (ProductionTask $record) {
                            $path = $record->file_path ?? null;
                            $disk = $record->file_disk ?? 'public';

                            if (blank($path)) {
                                $file = $record->project?->productionRequest?->files()
                                    ->where('department_id', $record->department_id)
                                    ->latest()
                                    ->first();

                                if ($file && filled($file->file_path)) {
                                    $path = $file->file_path;
                                    $disk = $file->file_disk ?? 'public';
                                }
                            }

                            if (blank($path)) {
                                return '<span style="opacity:.7">—</span>';
                            }

                            $url = Str::startsWith($path, ['http://', 'https://'])
                                ? $path
                                : Storage::disk($disk)->url($path);

                            $basename = parse_url($path, PHP_URL_PATH) ?: $path;
                            $name = e(basename($basename));

                            return '<a href="'.e($url).'" target="_blank" style="color:#16a34a; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                        }),
                    TextEntry::make('additional_work_file_link')
                        ->label('ملف الأعمال الإضافية')
                        ->html()
                        ->visible(fn () =>
                            auth()->check() &&
                            auth()->user()->hasAnyRole([
                                'admin',
                                'super-admin',
                                'super_admin',
                                'factory_manager',
                                'purchasing_manager',
                                'installation_manager',
                                'department_manager',
                                'sales',
                                'showroom_manager',
                            ])
                        )
                        ->state(function (ProductionTask $record) {
                            $pr = $record->project?->productionRequest;
                            $value = (string) ($pr?->additional_work_file ?? '');
                            if (blank($value)) {
                                return '<span style="opacity:.7">—</span>';
                            }

                            $isUrl = Str::startsWith($value, ['http://', 'https://']);
                            $url   = $isUrl ? $value : Storage::disk('public')->url($value);

                            $basename = parse_url($value, PHP_URL_PATH) ?: $value;
                            $name = e(basename($basename));

                            return '<a href="'.e($url).'" target="_blank" style="color:#0ea5e9; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                        }),
                    TextEntry::make('client_receipt_link')
                        ->label('سند استلام العميل')
                        ->html()
                        ->visible(fn () =>
                            auth()->check() &&
                            auth()->user()->hasAnyRole([
                                'admin', 'super-admin', 'super_admin',
                                'factory_manager', 'purchasing_manager',
                                'installation_manager', 'quality_manager','sales', 'showroom_manager'
                            ])
                        )
                        ->state(function (ProductionTask $record) {
                            $value = (string) ($record->client_receipt ?? '');

                            if (blank($value)) {
                                return '<span style="opacity:.7">—</span>';
                            }

                            $url = Str::startsWith($value, ['http://', 'https://'])
                                ? $value
                                : Storage::disk('public')->url($value);

                            $basename = parse_url($value, PHP_URL_PATH) ?: $value;
                            $name = e(basename($basename));

                            return '<a href="'.e($url).'" target="_blank" style="color:#d97706; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                        }),

                ])->columns(1),

            Section::make('التعليقات')
                ->schema([
                    ViewEntry::make('comments_list')
                        ->view('filament.task.comments-list')
                        ->state(fn (ProductionTask $record) =>
                        $record->comments()->with('author')->orderByDesc('id')->take(10)->get()
                        ),
                ])
                ->columnSpanFull()
                ->visible(fn (ProductionTask $record) => $record->comments()->exists()),

            Section::make('سلسلة طلبات الخامات')
                ->columns(3)
                ->schema([
                    TextEntry::make('kind')
                        ->label('نوع الطلب')
                        ->state(fn(\App\Models\MaterialRequest $r) => $r->parent_id ? 'تكميلي' : 'أساسي')
                        ->badge()
                        ->color(fn($state) => $state === 'تكميلي' ? 'warning' : 'success'),

                    TextEntry::make('parent_link')
                        ->label('الطلب الأصلي')
                        ->html()
                        ->state(function (\App\Models\MaterialRequest $r) {
                            if (! $r->parent) return '<span style="opacity:.6">—</span>';
                            $url = route('filament.admin.pages.purchasing.materials-requests.view', ['record' => $r->parent->id]);
                            return '<a href="'.e($url).'" target="_blank" style="color:#2563eb;text-decoration:underline;">طلب #'.$r->parent->id.' ▸</a>';
                        }),

                    TextEntry::make('children_links')
                        ->label('طلبات تكميلية')
                        ->html()
                        ->state(function (\App\Models\MaterialRequest $r) {
                            if ($r->children->isEmpty()) return '<span style="opacity:.6">—</span>';
                            $links = $r->children->map(function ($c) {
                                $url = route('filament.admin.pages.purchasing.materials-requests.view', ['record' => $c->id]);
                                return '<a href="'.e($url).'" target="_blank" style="color:#16a34a;text-decoration:underline;">#'.$c->id.'</a>';
                            })->implode(' , ');
                            return $links;
                        }),
                ]),

            Section::make('المشتريات')->schema([
                TextEntry::make('po_file_link')
                    ->label('أمر الشراء (PO)')
                    ->html()
                    ->state(function (ProductionTask $record) {
                        $mr = $record->materialRequests()->latest()->first();
                        if (! $mr) return '<span style="opacity:.7">—</span>';

                        $kind = $mr->parent_id ? ' (تكميلي)' : ' (أساسي)';
                        $po   = $mr->po_file ? '<a href="'.e(Storage::disk('public')->url($mr->po_file)).'" target="_blank" style="color:#2563eb;text-decoration:underline;font-weight:600;">'.e(basename($mr->po_file)).' ▸</a>' : '—';

                        return $po . ' <span style="opacity:.8">'. $kind .'</span>';
                    }),
            ])->columns(1)->visible(fn () =>
                auth()->check() &&
                auth()->user()->hasAnyRole([
                    'admin', 'super-admin', 'super_admin',
                    'factory_manager', 'purchasing_manager',
                ])
            ),

            Section::make('مدد المراحل')->schema([
                TextEntry::make('stage_durations_html')
                    ->label('تفصيل مدد كل مرحلة')
                    ->html()
                    ->columnSpanFull(),
            ])->columns(1),

        ]);
    }
}
