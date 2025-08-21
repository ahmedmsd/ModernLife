<?php

namespace App\Filament\Resources\TaskResource\Pages;

use App\Filament\Resources\TaskResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;

// موديلات
use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Models\MaterialRequest;
use App\Models\Employee;

class ViewTask extends ViewRecord
{
    protected static string $resource = TaskResource::class;

    protected static ?string $title           = 'عرض المهمة';
    protected static ?string $navigationLabel = 'المهام';
    protected static ?string $label           = 'المهام';
    protected static ?string $pluralLabel     = 'المهام';
    protected static ?string $modelLabel      = 'مهمة';

    public function mount($record): void
    {
        parent::mount($record);

        // تحميل العلاقات المهمة
        $this->record->load([
            'project:id,project_name,production_request_id',
            'project.productionRequest:id,total_price',
            'department:dept_id,dept_name',
            'employee:employee_id,employee_name,user_id',
            'logs.causer:id,name',
            'materialRequests:id,task_id,status,requested_at,approved_at,provided_at,expected_delivery_at,estimated_cost,po_number',
        ]);
    }

    /* ===============================
     * مسارات رجوع الخبز/التوجيه
     * ===============================*/
    protected function getParentTasksUrl(): string
    {
        $user = Auth::user();

        if ($user && $user->hasAnyRole(['super-admin', 'admin', 'project_manager'])) {
            if ($this->record?->project_id) {
                return url("/admin/projects/{$this->record->project_id}/manage-tasks");
            }
            return url('/admin/tasks');
        }

        return url('/admin/my-tasks');
    }

    protected function getParentTasksLabel(): string
    {
        $user = Auth::user();

        return ($user && $user->hasAnyRole(['super-admin', 'admin', 'project_manager']))
            ? 'مهام المشروع'
            : 'مهامي';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getParentTasksUrl();
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getParentTasksUrl() => $this->getParentTasksLabel(),
            $this->getBreadcrumb(),
        ];
    }

    /* ===============================
     * أدوات الحالة/الألوان (الحالات الجديدة)
     * ===============================*/
    private function normalizeStatus(?string $s): ?string
    {
        // دعم قيَم قديمة لو كانت موجودة في اللوج/السجلات
        return match ($s) {
            'assigned'     => 'pending',
            'acknowledged' => 'received',
            'blocked'      => 'on_hold',
            'rework'       => 'rejected',
            'closed'       => 'completed',
            default        => $s,
        };
    }

    private function statusAr(?string $val): ?string
    {
        if ($val === null) return null;
        $val = $this->normalizeStatus($val);

        return match ($val) {
            'pending'         => 'بانتظار التأكيد',
            'received'        => 'تم الاستلام',
            'under_review'    => 'قيد المراجعة',
            'approved'        => 'معتمد',
            'rejected'        => 'مرفوض',
            'in_progress'     => 'قيد التنفيذ',
            'materials_wait'  => 'بانتظار اعتماد المشتريات',
            'materials_prep'  => 'جارٍ تجهيز الخامات',
            'materials_done'  => 'تم توفير الخامات',
            'on_hold'         => 'متوقفة مؤقتًا',
            'completed'       => 'مكتملة',
            'cancelled'       => 'ملغاة',
            default           => $val,
        };
    }

    private function statusColor(?string $val): string
    {
        $val = $this->normalizeStatus($val);

        return match ($val) {
            'pending'         => 'warning',
            'received'        => 'info',
            'under_review'    => 'cyan',
            'approved'        => 'success',
            'rejected'        => 'danger',
            'in_progress'     => 'primary',
            'materials_wait'  => 'warning',
            'materials_prep'  => 'primary',
            'materials_done'  => 'success',
            'on_hold'         => 'gray',
            'completed'       => 'success',
            'cancelled'       => 'gray',
            default           => 'secondary',
        };
    }

    private function statusHex(?string $status): string
    {
        $status = $this->normalizeStatus($status);
        return match ($status) {
            'pending'         => '#f59e0b', // amber
            'received'        => '#3b82f6', // blue
            'under_review'    => '#06b6d4', // cyan
            'approved'        => '#10b981', // emerald
            'rejected'        => '#ef4444', // red
            'in_progress'     => '#0ea5e9', // sky
            'materials_wait'  => '#f59e0b', // amber
            'materials_prep'  => '#0ea5e9', // sky
            'materials_done'  => '#22c55e', // green
            'on_hold'         => '#6b7280', // gray
            'completed'       => '#22c55e', // green
            'cancelled'       => '#9ca3af', // gray-400
            default           => '#6b7280',
        };
    }

    private function statusVal(): string
    {
        $s = $this->record->status;
        return $s instanceof \BackedEnum ? $s->value : (string) $s;
    }

    /* ===============================
     * ملكية/إرسال/استلام (للـ 3 أيام تذكير)
     * ===============================*/
    private function setOwner(?string $role, ?int $userId = null, bool $touchSent = true, ?string $note = null): void
    {
        $payload = [
            'current_owner_role'    => $role,
            'current_owner_user_id' => $userId,
        ];
        if ($touchSent) {
            $payload['sent_to_owner_at']     = now();
            $payload['received_by_owner_at'] = null;
        }

        $this->record->forceFill($payload)->save();

        $this->record->logs()->create([
            'type'        => 'owner_changed',
            'data'        => ['owner_role' => $role, 'owner_user_id' => $userId, 'note' => $note],
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);
    }

    private function markOwnerReceived(?string $note = null): void
    {
        $this->record->update(['received_by_owner_at' => now()]);

        $this->record->logs()->create([
            'type'        => 'owner_received',
            'data'        => [
                'owner_role' => $this->record->current_owner_role,
                'owner_user_id' => $this->record->current_owner_user_id,
                'note' => $note,
            ],
            'causer_id'   => Auth::id(),
            'happened_at' => now(),
        ]);
    }

    /* ===============================
     * مواد/مشتريات
     * ===============================*/
    protected function hasOpenMaterialsRequest(): bool
    {
        return $this->record->materialRequests()
            ->whereNull('provided_at')
            ->whereIn('status', ['requested','approved'])
            ->exists();
    }

    /* ===============================
     * أزرار رأس الصفحة
     * ===============================*/
    protected function getHeaderActions(): array
    {
        $task = $this->record;

        return [
            // 1) إسناد لمدير القسم => pending + تعيين المالك
            Action::make('assign_to_dept_manager')
                ->label('إسناد لمدير القسم')
                ->icon('heroicon-o-user-plus')
                ->visible(fn () => Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin']))
                ->form([
                    Forms\Components\Select::make('employee_id')
                        ->label('المسؤول')
                        ->options(fn () => Employee::query()
                            ->whereHas('roles', fn($q)=> $q->where('name','department_manager'))
                            ->orderBy('employee_name')
                            ->pluck('employee_name','employee_id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\DateTimePicker::make('due_date')->label('تاريخ التسليم المتوقع')->required(),
                ])
                ->action(function (array $data) use ($task) {
                    $from = $this->statusVal();

                    $employee = Employee::with('user:id')->find($data['employee_id']);
                    $ownerUserId = $employee?->user?->id;

                    $task->update([
                        'assigned_to_employee_id' => $data['employee_id'],
                        'status'                  => 'pending',
                        'assigned_at'             => now(),
                        'due_date'                => $data['due_date'],
                    ]);

                    $this->setOwner('department_manager', $ownerUserId, true, 'إسناد من المصنع');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'assigned_changed',
                        'data'        => ['from' => $from, 'to' => 'pending', 'to_employee' => $data['employee_id']],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم الإسناد')->success()->send();
                }),

            // 2) تأكيد الاستلام (مدير القسم) => received + توقيت الاستلام
            Action::make('acknowledge')
                ->label('تأكيد استلام المهمة')
                ->icon('heroicon-o-hand-thumb-up')
                ->color('success')
                ->visible(fn () => Auth::user()?->hasAnyRole(['department_manager','admin','super-admin'])
                    && $this->statusVal() === 'pending')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update([
                        'status'      => 'received',
                        'received_at' => now(),
                    ]);

                    $this->markOwnerReceived('تأكيد استلام المهمة');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'received'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم تأكيد الاستلام')->success()->send();
                }),

            // 3) طلب خامات => materials_wait + تحويل المالك للمشتريات
            Action::make('request_materials')
                ->label('طلب خامات')
                ->icon('heroicon-o-truck')
                ->color('warning')
                ->visible(fn () => Auth::user()?->hasAnyRole(['department_manager','admin','super-admin']))
                ->form([
                    Forms\Components\Textarea::make('note')->label('تفاصيل المطلوب')->rows(3)->required(),
                ])
                ->action(function (array $data) {
                    $task = $this->record;
                    $from = $this->statusVal();

                    MaterialRequest::create([
                        'task_id'       => $task->id,
                        'department_id' => $task->department_id,
                        'requested_by'  => Auth::id(),
                        'requested_at'  => now(),
                        'status'        => 'requested',
                        'note'          => $data['note'],
                    ]);

                    $task->update(['status' => 'materials_wait']);

                    $this->setOwner('purchasing_manager', null, true, 'طلب خامات');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'materials_wait'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم إرسال طلب الخامات')->success()->send();
                }),

            // 4) اعتماد المشتريات + سقف الشراء => materials_prep
            Action::make('purchasing_approve')
                ->label('اعتماد طلب الخامات')
                ->icon('heroicon-o-check-badge')
                ->color('primary')
                ->visible(fn () => Auth::user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && $this->hasOpenMaterialsRequest())
                ->form([
                    Forms\Components\TextInput::make('po_number')->label('رقم الطلب/المرجع'),
                    Forms\Components\TextInput::make('estimated_cost')->label('التكلفة المتوقعة')->numeric()->required(),
                    Forms\Components\DateTimePicker::make('expected_delivery_at')->label('موعد التوريد المتوقع')->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->action(function (array $data) {
                    $task = $this->record;
                    $mr   = $task->materialRequests()->whereNull('provided_at')->latest()->first();

                    if (! $mr) {
                        Notification::make()->title('لا يوجد طلب خامات مفتوح')->warning()->send();
                        return;
                    }

                    // سقف الشراء
                    $percent = (float) config('manufacturing.purchase_ceiling_percent', 50);
                    $orderPrice = (float) ($task->project?->productionRequest?->total_price ?? 0);
                    $ceiling = $orderPrice * ($percent / 100);

                    if (($data['estimated_cost'] ?? 0) > $ceiling && $orderPrice > 0) {
                        foreach (Role::whereIn('name', ['factory_manager','super-admin'])->get() as $role) {
                            foreach ($role->users as $user) {
                                Notification::make()
                                    ->title('تنبيه: تجاوز حد المشتريات')
                                    ->body("التكلفة {$data['estimated_cost']} تجاوزت {$percent}% من سعر الطلب.")
                                    ->sendToDatabase($user);
                            }
                        }
                        Notification::make()->warning()
                            ->title('تم الاعتماد مع تحذير الميزانية')
                            ->body('التكلفة التقديرية تخطّت الحد المسموح.')
                            ->send();
                    } else {
                        Notification::make()->success()
                            ->title('تم اعتماد طلب الشراء')
                            ->send();
                    }

                    $mr->update([
                        'po_number'            => $data['po_number'] ?? $mr->po_number,
                        'estimated_cost'       => $data['estimated_cost'],
                        'expected_delivery_at' => $data['expected_delivery_at'],
                        'note'                 => trim(($mr->note ? $mr->note . "\n" : '') . ($data['note'] ?? '')),
                        'status'               => 'approved',
                        'approved_at'          => now(),
                        'approved_by'          => Auth::id(),
                    ]);

                    $task->update(['status' => 'materials_prep']);
                    $this->setOwner('purchasing_manager', null, false, 'اعتماد المشتريات');
                }),

            // 5) تأكيد توفير الخامات => materials_done + إعادة المالك لمدير القسم
            Action::make('materials_provided')
                ->label('تأكيد توفير الخامات')
                ->icon('heroicon-o-box')
                ->color('success')
                ->visible(fn () => Auth::user()?->hasAnyRole(['purchasing_manager','admin','super-admin'])
                    && $this->hasOpenMaterialsRequest())
                ->action(function () {
                    $task = $this->record;
                    $mr   = $task->materialRequests()->whereNull('provided_at')->latest()->first();

                    if (! $mr) {
                        Notification::make()->title('لا يوجد طلب خامات مفتوح')->warning()->send();
                        return;
                    }

                    $mr->update([
                        'provided_by' => Auth::id(),
                        'provided_at' => now(),
                        'status'      => 'fulfilled',
                    ]);

                    $task->update(['status' => 'materials_done']);

                    // إعادة المالك لمدير القسم
                    $deptManagerEmp = Employee::whereHas('roles', fn($q)=>$q->where('name','department_manager'))
                        ->where('department_id', $task->department_id)
                        ->first();
                    $this->setOwner('department_manager', $deptManagerEmp?->user_id, true, 'توفير الخامات');

                    Notification::make()->title('تم توفير الخامات')->success()->send();
                }),

            // 6) تأكيد استلام الخامات (مدير القسم) => in_progress
            Action::make('materials_received_ok')
                ->label('تأكيد استلام الخامات')
                ->icon('heroicon-o-hand-thumb-up')
                ->color('success')
                ->visible(fn () => Auth::user()?->hasAnyRole(['department_manager','admin','super-admin'])
                    && $this->statusVal() === 'materials_done')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'in_progress']);
                    $this->markOwnerReceived('تم استلام الخامات');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'in_progress'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('بدأ التنفيذ')->success()->send();
                }),

            // 7) مواعيد التنفيذ المتوقع
            Action::make('set_plan_dates')
                ->label('تحديد المواعيد المتوقعة')
                ->icon('heroicon-o-calendar-days')
                ->visible(fn () => Auth::user()?->hasAnyRole(['department_manager','admin','super-admin']))
                ->form([
                    Forms\Components\DatePicker::make('planned_start')->label('بداية التصنيع (متوقعة)')->required(),
                    Forms\Components\DatePicker::make('planned_end')->label('نهاية التصنيع (متوقعة)')->required(),
                    Forms\Components\DatePicker::make('planned_install')->label('موعد التركيب (متوقع)'),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'planned_start_at'   => $data['planned_start'],
                        'planned_end_at'     => $data['planned_end'],
                        'planned_install_at' => $data['planned_install'] ?? null,
                    ]);

                    TaskLog::create([
                        'task_id'     => $this->record->id,
                        'type'        => 'plan_set',
                        'data'        => $data,
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم حفظ المواعيد')->success()->send();
                }),

            // 8) إرسال للجودة (بعد التصنيع) => under_review + تحويل المالك للجودة
            Action::make('send_to_qa_after_manu')
                ->label('إرسال للجودة (بعد التصنيع)')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->visible(fn () => Auth::user()?->hasAnyRole(['department_manager','admin','super-admin'])
                    && $this->statusVal() === 'in_progress')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'under_review']);
                    $this->setOwner('quality_manager', null, true, 'مراجعة ما بعد التصنيع');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'under_review'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم إرسال المهمة للجودة')->success()->send();
                }),

            // 9) اعتماد/رفض الجودة (بعد التصنيع)
            Action::make('qa_approve_after_manu')
                ->label('اعتماد الجودة (ما بعد التصنيع)')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => Auth::user()?->hasAnyRole(['quality_manager','admin','super-admin'])
                    && $this->statusVal() === 'under_review')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'approved']);
                    $this->markOwnerReceived('اعتماد الجودة بعد التصنيع');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'approved'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم اعتماد الجودة')->success()->send();
                }),

            Action::make('qa_reject_after_manu')
                ->label('رفض الجودة (إعادة تصنيع)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => Auth::user()?->hasAnyRole(['quality_manager','admin','super-admin'])
                    && $this->statusVal() === 'under_review')
                ->form([Forms\Components\Textarea::make('reason')->label('سبب الرفض')->required()->rows(3)])
                ->action(function (array $data) {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'rejected']);
                    // إعادة المالك لمدير القسم لإعادة العمل
                    $deptManagerEmp = Employee::whereHas('roles', fn($q)=>$q->where('name','department_manager'))
                        ->where('department_id', $task->department_id)
                        ->first();
                    $this->setOwner('department_manager', $deptManagerEmp?->user_id, true, 'إعادة تصنيع');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'rejected',
                        'data'        => ['from' => $from, 'to' => 'rejected', 'reason' => $data['reason']],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم الرفض وإرجاع المهمة للتصنيع')->warning()->send();
                }),

            // 10) إرسال للتركيب => in_progress + تحويل المالك للتركيب
            Action::make('send_to_install')
                ->label('إرسال للتركيب')
                ->icon('heroicon-o-truck')
                ->visible(fn () => Auth::user()?->hasAnyRole(['factory_manager','admin','super-admin'])
                    && $this->statusVal() === 'approved')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'in_progress']);
                    $this->setOwner('installation_manager', null, true, 'بدء التركيب');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'in_progress', 'phase' => 'installation'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم إرسال المهمة للتركيب')->success()->send();
                }),

            // 11) إرسال للجودة (بعد التركيب) => under_review + تحويل المالك للجودة
            Action::make('send_to_qa_after_install')
                ->label('إرسال للجودة (بعد التركيب)')
                ->icon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->visible(fn () => Auth::user()?->hasAnyRole(['installation_manager','factory_manager','admin','super-admin'])
                    && $this->statusVal() === 'in_progress')
                ->action(function () {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update(['status' => 'under_review']);
                    $this->setOwner('quality_manager', null, true, 'مراجعة ما بعد التركيب');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'status_changed',
                        'data'        => ['from' => $from, 'to' => 'under_review', 'phase' => 'post_install_qa'],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم إرسال المهمة للجودة (بعد التركيب)')->success()->send();
                }),

            // 12) اعتماد الجودة بعد التركيب وإغلاق => completed (+ سند العميل)
            Action::make('qa_after_install_approve')
                ->label('اعتماد الجودة بعد التركيب وإغلاق')
                ->icon('heroicon-o-badge-check')
                ->color('success')
                ->visible(fn () => Auth::user()?->hasAnyRole(['quality_manager','admin','super-admin'])
                    && $this->statusVal() === 'under_review')
                ->form([
                    Forms\Components\FileUpload::make('client_receipt')
                        ->label('سند استلام العميل (موقّع)')
                        ->disk('public')->directory('client_receipts/'.now()->format('Y/m'))
                        ->openable()->downloadable()->required(),
                ])
                ->action(function (array $data) {
                    $task = $this->record;
                    $from = $this->statusVal();

                    $task->update([
                        'status'         => 'completed',
                        'completed_at'   => now(),
                        'client_receipt' => $data['client_receipt'],
                    ]);

                    // إغلاق الملكية
                    $this->setOwner(null, null, false, 'إغلاق المهمة');

                    TaskLog::create([
                        'task_id'     => $task->id,
                        'type'        => 'closed',
                        'data'        => ['from' => $from, 'to' => 'completed', 'client_receipt' => $data['client_receipt']],
                        'causer_id'   => Auth::id(),
                        'happened_at' => now(),
                    ]);

                    Notification::make()->title('تم الإغلاق وتوثيق سند العميل')->success()->send();
                }),
        ];
    }

    /* ===============================
     * مدد المراحل (حسب اللوج + الحالات الجديدة)
     * ===============================*/
    private function buildStageDurations(ProductionTask $task): array
    {
        $logs = $task->logs()->orderBy('happened_at')->get(['type','data','happened_at','created_at']);

        $start = $task->created_at
            ? Carbon::parse($task->created_at)
            : ($logs->first()?->happened_at ? Carbon::parse($logs->first()->happened_at) : now());

        $endRaw = $task->completed_at ?? $logs->last()?->happened_at ?? now();
        $end    = $endRaw instanceof Carbon ? $endRaw : Carbon::parse($endRaw);

        $firstChange = $logs->firstWhere('type', 'status_changed');
        $initial     = is_array($firstChange?->data ?? null) ? ($this->normalizeStatus($firstChange->data['from'] ?? null)) : null;
        $current     = $initial ?? $this->normalizeStatus($this->statusVal()) ?? 'pending';

        $cursor  = $start->clone();
        $seconds = [];

        $add = function (?string $status, Carbon $from, Carbon $to) use (&$seconds) {
            $status = $status ?: 'unknown';
            $delta  = max(0, $from->diffInSeconds($to));
            $seconds[$status] = ($seconds[$status] ?? 0) + $delta;
        };

        foreach ($logs as $log) {
            $tRaw = $log->happened_at ?? $log->created_at;
            if (! $tRaw) continue;
            $t = $tRaw instanceof Carbon ? $tRaw : Carbon::parse($tRaw);
            if ($t->lessThan($cursor)) continue;

            $add($current, $cursor, $t);
            $cursor = $t->clone();

            if ($log->type === 'status_changed' && is_array($log->data ?? null)) {
                $to = $this->normalizeStatus($log->data['to'] ?? null);
                if ($to) $current = $to;
            }
        }

        if ($end->greaterThan($cursor)) {
            $add($current, $cursor, $end);
        }

        $total = array_sum($seconds);

        $order = ['pending','received','materials_wait','materials_prep','materials_done','in_progress','under_review','approved','rejected','on_hold','completed','cancelled','unknown'];

        $rows = collect($seconds)
            ->map(fn ($sec, $status) => [
                'status'  => $status,
                'label'   => $this->statusAr($status) ?? $status,
                'seconds' => $sec,
            ])
            ->sortBy(fn ($row) => ($i = array_search($row['status'], $order, true)) === false ? 999 : $i)
            ->values()
            ->map(function ($row) use ($total) {
                $row['human']   = $row['seconds'] > 0
                    ? Carbon::now()->subSeconds($row['seconds'])->diffForHumans(null, true)
                    : '0 ث';
                $row['percent'] = $total > 0 ? round($row['seconds'] * 100 / $total, 1) : 0.0;
                return $row;
            })
            ->all();

        return compact('rows','total','start','end');
    }

    private function renderStageDurationsHtml(ProductionTask $task): string
    {
        $stats   = $this->buildStageDurations($task);
        $rows    = $stats['rows'];
        $totalH  = $stats['start']->diffForHumans($stats['end'], true);
        $start   = $stats['start']->format('Y-m-d H:i');
        $end     = $stats['end']->format('Y-m-d H:i');

        ob_start(); ?>
        <div class="w-full">
            <div class="rounded-xl border bg-white/80 dark:bg-gray-900/70 shadow-sm">
                <div class="px-4 py-3 border-b bg-gray-50/60 dark:bg-gray-800/60 rounded-t-xl">
                    <div class="flex flex-wrap items-center gap-3 text-sm">
                        <div class="font-semibold">الإجمالي منذ الإنشاء حتى الإغلاق/الآن:</div>
                        <div class="px-2 py-0.5 rounded-full bg-gray-900 text-white text-xs dark:bg-white dark:text-gray-900">
                            <?= e($totalH) ?>
                        </div>
                        <div class="ms-auto text-xs text-gray-500 dark:text-gray-400">
                            من <?= e($start) ?> إلى <?= e($end) ?>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-6">
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto text-sm rtl:text-right">
                            <thead class="bg-gray-100 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
                            <tr>
                                <th class="px-3 py-2 font-semibold text-right">المرحلة</th>
                                <th class="px-3 py-2 font-semibold text-right">المدة</th>
                                <th class="px-3 py-2 font-semibold text-right">النسبة</th>
                                <th class="px-3 py-2 font-semibold text-right">تقدّم</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            <?php foreach ($rows as $r):
                                $hex     = $this->statusHex($r['status'] ?? null);
                                $label   = $r['label'] ?? ($r['status'] ?? '—');
                                $human   = $r['human'] ?? '—';
                                $percent = isset($r['percent']) ? (float)$r['percent'] : 0.0;
                                ?>
                                <tr class="odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800">
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="inline-block w-2.5 h-2.5 rounded-full" style="background-color: <?= e($hex) ?>;"></span>
                                            <span class="px-2 py-0.5 rounded text-white text-xs" style="background-color: <?= e($hex) ?>;">
                                                <?= e($label) ?>
                                            </span>
                                        </span>
                                    </td>
                                    <td class="px-3 py-2"><?= e($human) ?></td>
                                    <td class="px-3 py-2"><?= e(number_format($percent, 1)) ?>%</td>
                                    <td class="px-3 py-2 w-64">
                                        <div class="w-full h-2 rounded bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                            <div class="h-2 rounded" style="width: <?= e(max(0,min(100,$percent))) ?>%; background-color: <?= e($hex) ?>;"></div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php return (string) ob_get_clean();
    }

    /* ===============================
     * عرض صفحة المعلومات
     * ===============================*/
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('بيانات المهمة')
                ->schema([
                    TextEntry::make('id')->label('رقم المهمة'),
                    TextEntry::make('project.project_name')->label('المشروع')->placeholder('—'),
                    TextEntry::make('department.dept_name')->label('القسم')->placeholder('—'),
                    TextEntry::make('employee.employee_name')->label('المسؤول')->placeholder('—'),

                    TextEntry::make('status')
                        ->label('الحالة')
                        ->formatStateUsing(fn ($state) => $this->statusAr($state instanceof \BackedEnum ? $state->value : $this->normalizeStatus((string)$state)))
                        ->badge()
                        ->color(fn ($state) => $this->statusColor($state instanceof \BackedEnum ? $state->value : $this->normalizeStatus((string)$state)))
                        ->placeholder('—'),

                    TextEntry::make('due_date')
                        ->label('تاريخ التسليم')
                        ->date()
                        ->badge()
                        ->color(function ($state) {
                            if (blank($state)) return 'gray';
                            $due = $state instanceof Carbon ? $state : Carbon::parse($state);
                            return now()->gt($due) ? 'danger' : 'success';
                        })
                        ->placeholder('—'),

                    TextEntry::make('assigned_at')->label('تاريخ الإسناد')->dateTime()->placeholder('—'),

                    TextEntry::make('current_owner_role')
                        ->label('المالك الحالي (الدور)')
                        ->formatStateUsing(fn($s) => $s
                            ? match ($s) {
                                'department_manager'  => 'مدير القسم',
                                'purchasing_manager'  => 'مدير المشتريات',
                                'quality_manager'     => 'مدير الجودة',
                                'installation_manager'=> 'مسؤول التركيب',
                                'factory_manager'     => 'مدير المصنع',
                                default               => $s,
                            }
                            : '—'
                        ),
                    TextEntry::make('sent_to_owner_at')->label('أُرسل للمالك')->dateTime()->placeholder('—'),
                    TextEntry::make('received_by_owner_at')->label('مؤكد الاستلام')->dateTime()->placeholder('—'),
                ])->columns(2),

            Section::make('مدد المراحل')
                ->columns(1)
                ->schema([
                    TextEntry::make('stage_durations_html')
                        ->label('تفصيل مدد كل مرحلة')
                        ->html()
                        ->state(fn ($record) => $this->renderStageDurationsHtml($record))
                        ->columnSpanFull(),
                ]),

            Section::make('الخط الزمني')
                ->schema([
                    TextEntry::make('timeline_html')
                        ->label('')
                        ->html()
                        ->columnSpanFull()
                        ->state(function (ProductionTask $record) {
                            $logs = $record->logs()
                                ->with('causer:id,name')
                                ->orderByDesc('happened_at')
                                ->orderByDesc('created_at')
                                ->get(['id','task_id','type','data','causer_id','happened_at','created_at']);

                            if ($logs->isEmpty()) {
                                return '<div style="opacity:.7">لا توجد عمليات بعد.</div>';
                            }

                            $statusMap = [
                                'pending'         => 'بانتظار التأكيد',
                                'received'        => 'تم الاستلام',
                                'under_review'    => 'قيد المراجعة',
                                'approved'        => 'معتمد',
                                'rejected'        => 'مرفوض',
                                'in_progress'     => 'قيد التنفيذ',
                                'materials_wait'  => 'بانتظار اعتماد المشتريات',
                                'materials_prep'  => 'جارٍ تجهيز الخامات',
                                'materials_done'  => 'تم توفير الخامات',
                                'on_hold'         => 'متوقفة مؤقتًا',
                                'completed'       => 'مكتملة',
                                'cancelled'       => 'ملغاة',
                            ];

                            $colorBy = function (?string $to, ?string $type): string {
                                $to = $this->normalizeStatus($to);
                                return match ($to ?? '') {
                                    'completed'         => '#22c55e',
                                    'approved'          => '#10b981',
                                    'on_hold','cancelled'=> '#6b7280',
                                    'under_review'      => '#06b6d4',
                                    'in_progress'       => '#f59e0b',
                                    'rejected'          => '#ef4444',
                                    'received'          => '#3b82f6',
                                    'pending'           => '#f59e0b',
                                    'materials_wait'    => '#f59e0b',
                                    'materials_prep'    => '#0ea5e9',
                                    'materials_done'    => '#22c55e',
                                    default => match ($type ?? '') {
                                        'assigned_changed'  => '#f59e0b',
                                        'due_changed'       => '#a855f7',
                                        'status_changed'    => '#38bdf8',
                                        default             => '#9ca3af',
                                    },
                                };
                            };

                            $cards = [];
                            foreach ($logs as $i => $curr) {
                                $next   = $logs[$i + 1] ?? null;
                                $tsCurr = $curr->happened_at ?: $curr->created_at;
                                $tsNext = $next ? ($next->happened_at ?: $next->created_at) : now();

                                $data = is_array($curr->data) ? $curr->data
                                    : (is_string($curr->data)
                                        ? (json_decode($curr->data, true) ?? ['raw' => $curr->data])
                                        : (array) $curr->data);

                                $type   = $curr->type ?? 'event';
                                $from   = $this->normalizeStatus($data['from'] ?? null);
                                $to     = $this->normalizeStatus($data['to']   ?? null);
                                $note   = $data['note'] ?? ($data['reason'] ?? ($data['message'] ?? ($data['raw'] ?? null)));
                                $byName = $curr->causer?->name;

                                $date     = $tsCurr ? Carbon::parse($tsCurr)->format('H:i:s Y-m-d') : '—';
                                $duration = $tsCurr
                                    ? Carbon::parse($tsCurr)->diffForHumans(Carbon::parse($tsNext), true)
                                    : '—';

                                $details = '—';
                                if ($type === 'status_changed' && ($from || $to)) {
                                    $fromAr = $from ? ($statusMap[$from] ?? $from) : '—';
                                    $toAr   = $to   ? ($statusMap[$to]   ?? $to)   : '—';
                                    $details = "من: {$fromAr} → إلى: {$toAr}";
                                }
                                if ($note) {
                                    $details = ($details !== '—' ? $details.'<br>' : '') . e($note);
                                }

                                $badgeColor = $colorBy($to, $type);
                                $badgeText  = e($type);
                                $byHtml     = $byName ? '<span style="opacity:.8">بواسطة: '.e($byName).'</span>' : '';

                                $cards[] = <<<HTML
                                    <div style="padding:16px 18px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:14px;">
                                        <div style="display:flex; gap:14px; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:8px;">
                                            <div style="display:flex; gap:10px; align-items:center;">
                                                <span style="display:inline-block; padding:4px 10px; border-radius:9999px; background:{$badgeColor}22; color:{$badgeColor}; font-size:12px; font-weight:600;">
                                                    {$badgeText}
                                                </span>
                                                <div style="font-weight:700;">{$date}</div>
                                            </div>
                                            {$byHtml}
                                        </div>
                                        <div style="opacity:.95; margin-bottom:8px; line-height:1.7;">{$details}</div>
                                        <div style="opacity:.7; font-size:12px;">المدة حتى الحدث التالي: {$duration}</div>
                                    </div>
                                HTML;
                            }

                            return '<div style="display:grid; grid-template-columns:repeat(3, 1fr); gap:16px;">'
                                . implode('', $cards) . '</div>';
                        }),
                ]),

            Section::make('إحصائيات')
                ->schema([
                    TextEntry::make('total_time')
                        ->label('إجمالي الوقت منذ أول حدث')
                        ->state(function (ProductionTask $record) {
                            $firstAt = $record->logs()->min('happened_at');
                            if (! $firstAt) return '—';
                            $lastAt = $record->logs()->max('happened_at') ?? now();
                            return Carbon::parse($firstAt)->diffForHumans(Carbon::parse($lastAt), true);
                        }),

                    TextEntry::make('is_late')
                        ->label('هل المهمة متأخرة؟')
                        ->badge()
                        ->state(function (ProductionTask $record) {
                            if (blank($record->due_date)) return '—';
                            $val = $this->normalizeStatus($record->status instanceof \BackedEnum ? $record->status->value : $record->status);
                            if (in_array($val, ['completed','cancelled'], true)) {
                                return $val === 'completed' ? 'مكتملة' : 'ملغاة';
                            }
                            $due = $record->due_date instanceof Carbon ? $record->due_date : Carbon::parse($record->due_date);
                            return now()->gt($due) ? 'متأخرة' : 'ضمن الوقت';
                        })
                        ->color(fn ($state) => match ($state) {
                            'متأخرة'     => 'danger',
                            'مكتملة'     => 'gray',
                            'ملغاة'      => 'gray',
                            'ضمن الوقت'  => 'success',
                            default       => 'gray',
                        }),
                ])->columns(2),
        ]);
    }
}
