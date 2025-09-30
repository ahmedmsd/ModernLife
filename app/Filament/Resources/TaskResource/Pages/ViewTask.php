<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
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

use App\Models\ProductionTask;
use App\Models\TaskComment;
use App\Support\Tasks\TaskPageHelper;
use App\Services\Tasks\TaskWorkflowService;

class ViewTask extends ViewRecord
{
    use HasRelationManagers;

    protected static string $resource = TaskResource::class;

    /** اجعلها nullable مع قيمة ابتدائية لتفادي Access قبل التهيئة */
    protected ?TaskPageHelper $helper = null;
    protected ?TaskWorkflowService $workflow = null;

    /** مُهيئات كسولة */
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

        // تهيئة فورية (اختياري؛ الlazy init يكفي لوحده)
        $this->helper();
        $this->workflow();

        // تحميل علاقات خفيفة
        $this->record->load([
            'project:id,project_name,production_request_id',
            'project.productionRequest:id',
            'department:dept_id,dept_name',
            'employee:employee_id,employee_name,user_id',
            'logs','materialRequests',
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

    /* =============================== أزرار ===============================*/
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
                        ->multiple()->directory('task-comments')->preserveFilenames()->downloadable()->openable(),
                ])
                ->action(function (array $data) {
                    TaskComment::create([
                        'task_id'     => $this->record->id,
                        'user_id'     => auth()->id(),
                        'body'        => $data['body'],
                        'attachments' => isset($data['attachments']) ? array_values((array) $data['attachments']) : null,
                    ]);
                    Notification::make()->title('تم إضافة التعليق')->success()->send();
                }),

            /* إسناد لمدير القسم */
            Action::make('assign_to_dept_manager')
                ->label('إسناد لمدير القسم')->icon('heroicon-o-user-plus')
                ->visible(fn() => $u?->hasAnyRole(['factory_manager','admin','super-admin']) && blank($this->record->assigned_to_employee_id))
                ->form([
                    Forms\Components\Select::make('employee_id')->label('المسؤول')
                        ->options(fn()=> \App\Models\Employee::query()
                            ->whereHas('roles', fn($q)=>$q->where('name','department_manager'))
                            ->orderBy('employee_name')->pluck('employee_name','employee_id'))
                        ->searchable()->required(),
                    Forms\Components\DateTimePicker::make('due_date')->label('تاريخ التسليم المتوقع')->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->assignToDeptManager($this->record, (int)$data['employee_id'], $data['due_date']);
                    Notification::make()->success()->title('تم الإسناد')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* مدير القسم يؤكد الاستلام */
            Action::make('acknowledge')
                ->label('تأكيد استلام المهمة (مدير القسم)')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(fn()=> $this->helper()->canDeptAcknowledge($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->deptAcknowledge($this->record);
                    Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* طلب خامات */
            Action::make('request_materials')
                ->label('طلب خامات')->icon('heroicon-o-truck')->color('warning')
                ->visible(fn()=> $this->helper()->canRequestMaterials($this->record, Auth::user()))
                ->form([
                    Forms\Components\Textarea::make('note')->label('تفاصيل المطلوب')->rows(3)->required(),
                    Forms\Components\FileUpload::make('po_file')
                        ->label('ملف أمر الشراء (PO)')->disk('public')
                        ->directory('purchase_orders/' . now()->format('Y/m'))
                        ->acceptedFileTypes(['application/pdf','image/*'])->maxSize(20_480)
                        ->openable()->downloadable()->moveFiles()->visibility('public')->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->requestMaterials($this->record, $data['note'], $data['po_file']);
                    Notification::make()->success()->title('تم إرسال طلب الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* المشتريات تؤكد الاستلام */
            Action::make('purchasing_receive')
                ->label('تأكيد استلام طلب الخامات (المشتريات)')->icon('heroicon-o-check-badge')->color('primary')
                ->visible(fn()=> $this->helper()->canPurchasingReceive($this->record, Auth::user()))
                ->form([
                    Forms\Components\TextInput::make('po_number')->label('رقم الطلب/المرجع'),
                    Forms\Components\DateTimePicker::make('expected_delivery_at')->label('موعد التوريد المتوقع')->required(),
                    Forms\Components\TextInput::make('estimated_cost')->label('التكلفة المتوقعة')->numeric(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->purchasingReceive($this->record, $data);
                    Notification::make()->success()->title('تم تسجيل استلام طلب الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* المشتريات تؤكد التوريد */
            Action::make('materials_provided')
                ->label('تأكيد توفر الخامات')->icon('heroicon-o-archive-box')->color('success')
                ->visible(fn()=> $this->helper()->canMaterialsProvided($this->record, Auth::user()))
                ->form([
                    Forms\Components\TextInput::make('actual_cost')->label('قيمة الشراء الفعلية')->numeric()->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->materialsProvided($this->record, (float)$data['actual_cost'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم توفير الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* تأكيد استلام الخامات + التخطيط */
            Action::make('materials_received_ok')
                ->label('تأكيد استلام الخامات (مدير القسم)')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(fn()=> $this->helper()->canMaterialsReceivedOk($this->record, Auth::user()))
                ->form([
                    Forms\Components\DatePicker::make('planned_start')->label('بداية التصنيع (متوقعة)')->native(false)->required(),
                    Forms\Components\DatePicker::make('planned_end')->label('نهاية التصنيع (متوقعة)')->native(false)->required(),
                    Forms\Components\DatePicker::make('planned_install')->label('موعد التركيب (متوقع)')->native(false)->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $start = Carbon::parse($data['planned_start']); $end = Carbon::parse($data['planned_end']); $ins = Carbon::parse($data['planned_install']);
                    if ($end->lt($start) || $ins->lt($end)) {
                        Notification::make()->danger()->title('تسلسل التواريخ غير صحيح')->send(); return;
                    }
                    $this->workflow()->materialsReceivedOk($this->record, $data['planned_start'], $data['planned_end'], $data['planned_install']);
                    Notification::make()->success()->title('تم تحديد المواعيد وتحويل المهمة للتصنيع')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* التصنيع يؤكد استلامه */
            Action::make('productionAcknowledge')
                ->label('تأكيد استلام التصنيع')->icon('heroicon-o-clipboard-document-check')->color('primary')
                ->visible(fn()=> $this->helper()->canProductionAcknowledge($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->productionAcknowledge($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام التصنيع')->send();
                }),

            /* بدء التصنيع */
            Action::make('start_production')
                ->label('بدء التصنيع')->icon('heroicon-o-play-circle')->color('primary')
                ->visible(fn()=> $this->helper()->canStartProduction($this->record, Auth::user()))
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ/وقت البدء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->startProduction($this->record, $data['started_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('بدأ التصنيع')->send();
                }),

            /* إنهاء التصنيع وإرسال للجودة */
            Action::make('finishManufacturing')
                ->label('إنهاء التصنيع وإرسال للجودة')->icon('heroicon-o-paper-airplane')->color('warning')
                ->visible(fn()=> $this->helper()->canFinishManufacturing($this->record, Auth::user()))
                ->form([ Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3), ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->finishManufacturingToQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم إرسال التصنيع للجودة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* QA (بعد التصنيع) */
            Action::make('qaAcknowledgeManufacturing')
                ->label('تأكيد استلام الجودة (بعد التصنيع)')->icon('heroicon-o-inbox-arrow-down')->color('primary')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'manufacturing_sent_to_qa')
                    && !$this->helper()->hasLog($this->record,'qa_ack_manufacturing'))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->qaAcknowledgeManufacturing($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام الجودة')->send();
                }),

            Action::make('approveManufacturingQA')
                ->label('اعتماد الجودة (بعد التصنيع)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'qa_ack_manufacturing')
                    && !$this->helper()->hasLog($this->record,'qa_approved_manufacturing')
                    && !$this->helper()->hasLog($this->record,'qa_rejected_manufacturing'))
                ->form([ Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3), ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->approveManufacturingQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم اعتماد الجودة وتحويل المهمة للتركيب')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('rejectManufacturingQA')
                ->label('رفض الجودة (بعد التصنيع)')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'qa_ack_manufacturing')
                    && !$this->helper()->hasLog($this->record,'qa_approved_manufacturing')
                    && !$this->helper()->hasLog($this->record,'qa_rejected_manufacturing'))
                ->form([ Forms\Components\Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(), ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->rejectManufacturingQA($this->record, $data['reason']);
                    Notification::make()->warning()->title('تم رفض الجودة وأعيدت للتصنيع')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* تدفقات التركيب + QA بعد التركيب */
            Action::make('installationAcknowledge')
                ->label('تأكيد استلام التركيب')->icon('heroicon-o-clipboard-document-check')->color('primary')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'installation_manager')
                    && $this->helper()->hasLog($this->record,'sent_to_install')
                    && !$this->helper()->hasLog($this->record,'install_acknowledged'))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->installationAcknowledge($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام التركيب')->send();
                }),

            Action::make('startInstallation')
                ->label('بدء التركيب')->icon('heroicon-o-wrench-screwdriver')->color('primary')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'installation_manager')
                    && ($this->helper()->hasLog($this->record,'install_acknowledged') || $this->helper()->hasLog($this->record,'install_ack_rework'))
                    && !$this->helper()->hasLog($this->record,'installation_started'))
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ/وقت البدء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->startInstallation($this->record, $data['started_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم بدء التركيب')->send();
                }),

            Action::make('finishInstallationAndSendQA')
                ->label('إنهاء التركيب وإرسال للجودة')->icon('heroicon-o-paper-airplane')->color('warning')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'installation_manager')
                    && $this->helper()->hasLog($this->record,'installation_started')
                    && !$this->helper()->hasLog($this->record,'installation_sent_to_qa'))
                ->form([
                    Forms\Components\DateTimePicker::make('finished_at')->label('تاريخ/وقت الإنهاء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->finishInstallationToQA($this->record, $data['finished_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم إرسال التركيب للجودة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('qaAcknowledgeInstallation')
                ->label('تأكيد استلام الجودة (التركيب)')->icon('heroicon-o-inbox-arrow-down')->color('primary')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'installation_sent_to_qa')
                    && !$this->helper()->hasLog($this->record,'qa_ack_installation'))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->qaAcknowledgeInstallation($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام الجودة للتركيب')->send();
                }),

            Action::make('approveInstallationQA')
                ->label('اعتماد الجودة (بعد التركيب)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'qa_ack_installation')
                    && !$this->helper()->hasLog($this->record,'qa_approved_installation')
                    && !$this->helper()->hasLog($this->record,'qa_rejected_installation'))
                ->form([ Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3), ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->approveInstallationQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم اعتماد الجودة لما بعد التركيب')->send();
                }),

            Action::make('rejectInstallationQA')
                ->label('رفض الجودة (التركيب)')->icon('heroicon-o-x-circle')->color('danger')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'quality_manager')
                    && $this->helper()->hasLog($this->record,'qa_ack_installation')
                    && !$this->helper()->hasLog($this->record,'qa_approved_installation')
                    && !$this->helper()->hasLog($this->record,'qa_rejected_installation'))
                ->form([ Forms\Components\Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(), ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->rejectInstallationQA($this->record, $data['reason']);
                    Notification::make()->warning()->title('تم رفض الجودة وأُعيدت المهمة للتركيب')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('installationAcknowledgeRework')
                ->label('تأكيد استلام التركيب (إعادة عمل)')->icon('heroicon-o-clipboard-document-check')->color('primary')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->ownerIs($this->record,'installation_manager')
                    && $this->helper()->hasLog($this->record,'sent_back_to_install')
                    && !$this->helper()->hasLog($this->record,'install_ack_rework'))
                ->requiresConfirmation()
                ->action(function(){
                    $this->workflow()->installationAcknowledgeRework($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام التركيب (إعادة عمل)')->send();
                }),

            /* سند استلام العميل → اكتمال */
            Action::make('uploadClientReceipt')
                ->label('رفع سند استلام العميل وإكمال المهمة')->icon('heroicon-o-arrow-up-on-square')->color('success')
                ->visible(fn()=> !$this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->hasLog($this->record,'qa_approved_installation')
                    && empty($this->record->client_receipt))
                ->form([
                    Forms\Components\FileUpload::make('client_receipt')->label('سند استلام العميل')
                        ->disk('public')->directory(fn()=> 'client-receipts/' . now()->format('Y/m'))
                        ->preserveFilenames()->downloadable()->openable()->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->uploadClientReceiptAndComplete($this->record, Arr::get($data,'client_receipt'));
                    Notification::make()->success()->title('اكتملت المهمة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),
        ];
    }

    /* =============================== Infolist ===============================*/
    public function infolist(Infolist $infolist): Infolist
    {
        $h = $this->helper();

        return $infolist->schema([
            Section::make('بيانات المهمة')->schema([
                TextEntry::make('id')->label('رقم المهمة'),
                TextEntry::make('project.project_name')->label('المشروع')->placeholder('—'),
                TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                TextEntry::make('employee.employee_name')->label('المسؤول')->placeholder('—'),

                TextEntry::make('status')->label('الحالة')
                    ->formatStateUsing(fn ($state) => $h->statusAr($state instanceof \BackedEnum ? $state->value : $h->normalizeStatus((string) $state)))
                    ->badge()
                    ->color(fn ($state) => $h->statusColor($state instanceof \BackedEnum ? $state->value : $h->normalizeStatus((string) $state)))
                    ->placeholder('—'),

                TextEntry::make('due_date')->label('تاريخ التسليم')->date()->badge()
                    ->color(function ($state) {
                        if (blank($state)) return 'gray';
                        $due = $state instanceof \Illuminate\Support\Carbon ? $state : \Illuminate\Support\Carbon::parse($state);
                        return now()->gt($due) ? 'danger' : 'success';
                    }),

                TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('—'),
                TextEntry::make('planned_start_at')->label('بداية التصنيع (خطة)')->date()->placeholder('—'),
                TextEntry::make('planned_end_at')->label('نهاية التصنيع (خطة)')->date()->placeholder('—'),
                TextEntry::make('planned_install_at')->label('التركيب المتوقع')->date()->placeholder('—'),

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
                        : '—'),
                TextEntry::make('sent_to_owner_at')->label('أُرسل للمالك')->dateTime()->placeholder('—'),
                TextEntry::make('received_by_owner_at')->label('مؤكد الاستلام')->dateTime()->placeholder('—'),
            ])->columns(2),

            Section::make('ملفات المهمة')->schema([
                // ملف الاتفاقية
                TextEntry::make('agreement_file')
                    ->label('ملف الاتفاقية')
                    ->html()
                    ->state(function (\App\Models\ProductionTask $record) {
                        $pr = $record->project?->productionRequest;
                        if (! $pr || blank($pr->agreement_file)) {
                            return '<span style="opacity:.7">—</span>';
                        }
                        $url  = Storage::disk('public')->url($pr->agreement_file);
                        $name = e(basename($pr->agreement_file));
                        return '<a href="'.e($url).'" target="_blank"
                        style="color:#2563eb; text-decoration:underline; font-weight:600;">
                        '.$name.' ▸
                    </a>';
                    }),

                // ملف التصنيع الخاص بالقسم
                TextEntry::make('manufacturing_file')
                    ->label('ملف التصنيع (للقسم)')
                    ->html()
                    ->state(function (\App\Models\ProductionTask $record) {
                        $file = $record->project?->productionRequest?->files()
                            ->where('department_id', $record->department_id)
                            ->latest()->first();

                        if (! $file || blank($file->file_path)) {
                            return '<span style="opacity:.7">—</span>';
                        }

                        $url  = Storage::disk('public')->url($file->file_path);
                        $name = e(basename($file->file_path));
                        return '<a href="'.e($url).'" target="_blank"
                        style="color:#16a34a; text-decoration:underline; font-weight:600;">
                        '.$name.' ▸
                    </a>';
                    }),
            ])->columns(1),
            
            Section::make('التعليقات')
                ->schema([
                    ViewEntry::make('comments_list')
                        ->view('filament.task.comments-list')
                        ->state(fn (\App\Models\ProductionTask $record) =>
                        $record->comments()->with('author')->orderByDesc('id')->take(10)->get()
                        ),
                ])
                ->columnSpanFull()
                ->visible(fn (\App\Models\ProductionTask $record) => $record->comments()->exists()),

            Section::make('المشتريات')->schema([
                TextEntry::make('po_file_link')->label('أمر الشراء (PO)')->html()
                    ->state(function (\App\Models\ProductionTask $record) {
                        $mr = $record->materialRequests()->orderByDesc('id')->first();
                        if (! $mr || blank($mr->po_file)) {
                            return '<span style="opacity:.7">—</span>';
                        }
                        $url  = \Illuminate\Support\Facades\Storage::disk('public')->url($mr->po_file);
                        $name = e(basename($mr->po_file));
                        return '<a href="'.e($url).'" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                    }),
            ])->columns(1),

            Section::make('مدد المراحل')->schema([
                TextEntry::make('stage_durations_html')
                    ->label('تفصيل مدد كل مرحلة')
                    ->html()
                    ->state(fn (\App\Models\ProductionTask $record) => $this->helper()->renderStageDurationsHtml($record))
                    ->columnSpanFull(),
            ])->columns(1),

            Section::make('إحصائيات')->schema([
                TextEntry::make('total_time')->label('إجمالي الوقت منذ أول حدث')
                    ->state(function (\App\Models\ProductionTask $record) {
                        $firstAt = $record->logs()->min('happened_at');
                        if (! $firstAt) return '—';
                        $lastAt = $record->logs()->max('happened_at') ?? now();
                        return \Illuminate\Support\Carbon::parse($firstAt)->diffForHumans(\Illuminate\Support\Carbon::parse($lastAt), true);
                    }),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) =>
                    $h->statusAr(
                        $state instanceof \BackedEnum
                            ? $state->value
                            : $h->normalizeStatus((string) $state)
                    )
                    )
                    ->badge()
                    ->color(fn ($state) =>
                    $h->statusColor(
                        $state instanceof \BackedEnum
                            ? $state->value
                            : $h->normalizeStatus((string) $state)
                    )
                    )
                    ->placeholder('—'),
            ])->columns(2),
        ]);
    }

}
