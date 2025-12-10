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

// Task Action Classes
use App\Filament\Actions\Task\Comment\AddCommentAction;
use App\Filament\Actions\Task\Assignment\AssignToDeptManagerAction;
use App\Filament\Actions\Task\DepartmentManager\AcknowledgeTaskAction;
use App\Filament\Actions\Task\DepartmentManager\RejectToFactoryAction;
use App\Filament\Actions\Task\Materials\RequestMaterialsAction;
use App\Filament\Actions\Task\Materials\PurchasingReceiveAction;
use App\Filament\Actions\Task\Materials\MaterialsProvidedAction;
use App\Filament\Actions\Task\Materials\MaterialsReceiptAction;
use App\Filament\Actions\Task\Manufacturing\StartProductionAction;
use App\Filament\Actions\Task\Manufacturing\FinishManufacturingAction;
use App\Filament\Actions\Task\Manufacturing\ManufacturingAcknowledgeReworkAction;
use App\Filament\Actions\Task\QA\QAAcknowledgeManufacturingAction;
use App\Filament\Actions\Task\QA\ApproveManufacturingQAAction;
use App\Filament\Actions\Task\QA\RejectManufacturingQAAction;
use App\Filament\Actions\Task\QA\QAAcknowledgeInstallationAction;
use App\Filament\Actions\Task\QA\ApproveInstallationQAAction;
use App\Filament\Actions\Task\QA\RejectInstallationQAAction;
use App\Filament\Actions\Task\Installation\InstallationAcknowledgeAfterQAAction;
use App\Filament\Actions\Task\Installation\StartInstallationAction;
use App\Filament\Actions\Task\Installation\FinishInstallationAction;
use App\Filament\Actions\Task\Installation\InstallationAcknowledgeReworkAction;
use App\Filament\Actions\Task\TaskManagement\HoldTaskAction;
use App\Filament\Actions\Task\TaskManagement\ResumeTaskAction;
use App\Filament\Actions\Task\Completion\UploadClientReceiptAction;

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
            'assignedUser.employee',
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
        $record = $this->record;
        $redirectCallback = fn() => $this->redirect($this->getRedirectUrl());

        return [
            // Comment
            AddCommentAction::make($record),

            // Assignment
            AssignToDeptManagerAction::make($record, $redirectCallback),

            // Department Manager
            AcknowledgeTaskAction::make($record, $redirectCallback),
            RejectToFactoryAction::make($record, $redirectCallback),

            // Materials
            RequestMaterialsAction::make($record, $redirectCallback),
            PurchasingReceiveAction::make($record, $redirectCallback),
            MaterialsProvidedAction::make($record, $redirectCallback),
            MaterialsReceiptAction::make($record, $redirectCallback),

            // Manufacturing
            StartProductionAction::make($record),
            FinishManufacturingAction::make($record, $redirectCallback),
            ManufacturingAcknowledgeReworkAction::make($record),

            // QA - Manufacturing
            QAAcknowledgeManufacturingAction::make($record),
            ApproveManufacturingQAAction::make($record, $redirectCallback),
            RejectManufacturingQAAction::make($record, $redirectCallback),

            // Installation
            InstallationAcknowledgeAfterQAAction::make($record),
            StartInstallationAction::make($record),
            FinishInstallationAction::make($record, $redirectCallback),
            InstallationAcknowledgeReworkAction::make($record),

            // QA - Installation
            QAAcknowledgeInstallationAction::make($record),
            ApproveInstallationQAAction::make($record),
            RejectInstallationQAAction::make($record, $redirectCallback),

            // Task Management
            HoldTaskAction::make($record),
            ResumeTaskAction::make($record),

            // Completion
            UploadClientReceiptAction::make($record, $redirectCallback),
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
                            $logs = \App\Models\TaskLog::where('task_id', $record->id)
                                ->with('causer')
                                ->orderByRaw('COALESCE(happened_at, created_at) DESC')
                                ->take(500)
                                ->get();

                            return $logs; // تأكد من إرجاع Collection
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
