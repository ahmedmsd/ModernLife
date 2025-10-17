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
            'department',
            'employee',
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
                ->visible(fn () => $u?->hasAnyRole(['factory_manager','admin','super-admin']) && blank($this->record->assigned_to_employee_id))
                ->form([
                    Forms\Components\Select::make('employee_id')->label('المسؤول')
                        ->options(function () {
                            return \App\Models\Employee::query()
                                ->whereHas('user', function ($q) {
                                    $q->whereHas('roles', function ($r) {
                                        $r->where('name', 'department_manager')
                                            ->where('guard_name');
                                    });
                                })
                                ->orderBy('employee_name')
                                ->pluck('employee_name', 'employee_id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                    Forms\Components\DatePicker::make('due_date')->label('تاريخ التسليم المتوقع')->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->assignToDeptManager($this->record, (int) $data['employee_id'], $data['due_date']);
                    Notification::make()->success()->title('تم الإسناد')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* مدير القسم يؤكد الاستلام */
            Action::make('acknowledge')
                ->label('تأكيد استلام المهمة (مدير القسم)')->icon('heroicon-o-hand-thumb-up')->color('success')
                ->visible(fn () => $this->helper()->canDeptAcknowledge($this->record, Auth::user()))
                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->deptAcknowledge($this->record);
                    Notification::make()->success()->title('تم تأكيد الاستلام')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* طلب خامات */
            Action::make('request_materials')
                ->label('طلب خامات')->icon('heroicon-o-truck')->color('info')
                ->visible(fn () => $this->helper()->canRequestMaterials($this->record, Auth::user()))
                ->form([
                    Forms\Components\Textarea::make('note')->label('تفاصيل المطلوب')->rows(3)->required(),
                    Forms\Components\FileUpload::make('po_file')
                        ->label('ملف أمر الشراء (PO) المُعتمد من مدير المصنع')->disk('public')
                        ->directory('purchase_orders/' . now()->format('Y/m'))
                        ->acceptedFileTypes(['application/pdf','image/*'])->maxSize(20_480)
                        ->openable()->downloadable()->moveFiles()->visibility('public')->required(),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->requestMaterials($this->record, $data['note'], $data['po_file']);
                    Notification::make()->success()->title('تم إرسال طلب الخامات')->send();
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
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->purchasingReceive($this->record, $data);
                    Notification::make()->success()->title('تم تسجيل استلام طلب الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* المشتريات تؤكد التوريد */
            Action::make('materials_provided')
                ->label('تأكيد توفر الخامات')->icon('heroicon-o-archive-box')->color('success')
                ->visible(fn () => $this->helper()->canMaterialsProvided($this->record, Auth::user()))
                ->form([
                    Forms\Components\TextInput::make('actual_cost')->label('قيمة الشراء الفعلية')->numeric()->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->materialsProvided($this->record, (float) $data['actual_cost'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم توفير الخامات')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('materials_received_ok')
                ->label('تأكيد استلام الخامات (مدير القسم)')
                ->icon('heroicon-o-hand-thumb-up')
                ->color('success')
                ->visible(fn () => $this->helper()->canMaterialsReceivedOk($this->record, Auth::user()))
                ->form([
                    Forms\Components\DatePicker::make('planned_start')->label('بداية التصنيع (متوقعة)')->native(false)->required(),
                    Forms\Components\DatePicker::make('planned_end')->label('نهاية التصنيع (متوقعة)')->native(false)->required(),
                    Forms\Components\DatePicker::make('planned_install')->label('موعد التركيب (متوقع)')->native(false)->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظات (اختياري)')->rows(3)->maxLength(1000),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
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

                    $this->workflow()->materialsReceivedOk(
                        $this->record,
                        $data['planned_start'],
                        $data['planned_end'],
                        $data['planned_install'],
                        $data['note'] ?? null
                    );

                    Notification::make()
                        ->success()
                        ->title('تم تأكيد استلام الخامات وتحديد المواعيد — المهمة بانتظار بدء التصنيع')
                        ->send();

                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('start_production')
                ->label('بدء التصنيع')
                ->icon('heroicon-o-play')
                ->color('info')
                ->visible(function () {
                    // 1) الملكية والحالة
                    if (($this->record->current_owner_role ?? null) !== 'department_manager') return false;
                    if ($this->record->status !== \App\Enums\TaskStatus::WaitingProduction->value) return false;

                    // 2) هل هناك إرجاع للتصنيع؟
                    $lastBack = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'sent_back_to_manufacturing')
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    // سنحدد "مرجع الدورة" (الزمن + id) الذي بعده يجب ألا يوجد manufacturing_started
                    $anchorTime = null;
                    $anchorId   = null;

                    if ($lastBack) {
                        // آخر ack rework بعد هذا الإرجاع
                        $tBack = $lastBack->happened_at ?? $lastBack->created_at;

                        $ack = \App\Models\TaskLog::query()
                            ->where('task_id', $this->record->id)
                            ->where('type', 'manufacturing_ack_rework')
                            ->where(function ($q) use ($tBack, $lastBack) {
                                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$tBack])
                                    ->orWhere(function ($q2) use ($tBack, $lastBack) {
                                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$tBack])
                                            ->where('id', '>', $lastBack->id);
                                    });
                            })
                            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                            ->first();

                        if (! $ack) {
                            // لم يُؤكَّد استلام إعادة العمل بعد الإرجاع
                            return false;
                        }

                        $anchorTime = $ack->happened_at ?? $ack->created_at;
                        $anchorId   = $ack->id;
                    } else {
                        // الدورة الأولى: نعتمد تأكيد استلام القسم الأول
                        $deptAck = \App\Models\TaskLog::query()
                            ->where('task_id', $this->record->id)
                            ->where('type', 'dept_ack_task')
                            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                            ->first();

                        if (! $deptAck) return false;

                        $anchorTime = $deptAck->happened_at ?? $deptAck->created_at;
                        $anchorId   = $deptAck->id;
                    }

                    // 3) لا بد ألا يكون هناك manufacturing_started بعد مرجع الدورة
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
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ البدء')->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة')->rows(2),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->startProduction($this->record, $data['started_at'], $data['note'] ?? null);
                    \Filament\Notifications\Notification::make()->success()->title('تم بدء التصنيع')->send();
                    $this->record->refresh();
                }),


            /* إنهاء التصنيع وإرسال للجودة */
            Action::make('finish_manufacturing_send_to_qa')
                ->label('إنهاء التصنيع وإرسال للجودة')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn () => $this->helper()->canFinishManufacturing($this->record, Auth::user()))
                ->form([
                    Forms\Components\DateTimePicker::make('actual_finished_at')->label('تاريخ/وقت الانتهاء الفعلي')->native(false)->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->finishManufacturingAndSendToQA($this->record, $data['actual_finished_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم إنهاء التصنيع وإرسال المهمة للجودة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            /* QA (بعد التصنيع) */
            Action::make('qaAcknowledgeManufacturing')
                ->label('تأكيد استلام الجودة (بعد التصنيع)')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('primary')
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    // آخر handoff للجودة (تصنيع/تركيب)
                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    // يجب أن يكون من التصنيع
                    if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') return false;

                    // هل تم ack للتصنيع بعد هذا الـ handoff؟
                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;
                    $ackExistsAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'qa_ack_manufacturing')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $ackExistsAfter;
                })


                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->qaAcknowledgeManufacturing($this->record);
                    \Filament\Notifications\Notification::make()->success()->title('تم تأكيد استلام الجودة')->send();
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),



            Action::make('approveManufacturingQA')
                ->label('اعتماد الجودة (بعد التصنيع)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') return false;

                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;

                    // يلزم وجود ack بعد هذا التحويل
                    $ackAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'qa_ack_manufacturing')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    if (! $ackAfter) return false;

                    // لا يوجد approve/reject بعد هذا التحويل
                    $decisionExistsAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['qa_approved_manufacturing','qa_rejected_manufacturing'])
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $decisionExistsAfter;
                })

                ->form([ Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3), ])
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
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastHandoff || $lastHandoff->type !== 'manufacturing_sent_to_qa') return false;

                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;

                    // يجب ack بعد هذا التحويل
                    $ackAfter = \App\Models\TaskLog::where('task_id', $this->record->id)
                        ->where('type','qa_ack_manufacturing')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    if (! $ackAfter) return false;

                    // لا يوجد قرار (approve/reject) بعد هذا التحويل
                    $decisionAfter = \App\Models\TaskLog::where('task_id', $this->record->id)
                        ->whereIn('type', ['qa_approved_manufacturing','qa_rejected_manufacturing'])
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $decisionAfter;
                })
                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->rejectManufacturingQA($this->record, $data['reason']);
                    \Filament\Notifications\Notification::make()
                        ->warning()->title('تم رفض الجودة وأعيدت للتصنيع')->send();
                    $this->redirect($this->getRedirectUrl());
                }),


            Action::make('manufacturingAcknowledgeRework')
                ->label('تأكيد استلام التصنيع (إعادة عمل)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(function () {
                    // يجب أن تعود الملكية لمدير القسم بعد رفض جودة التصنيع
                    if (($this->record->current_owner_role ?? null) !== 'department_manager') {
                        return false;
                    }

                    // آخر إرجاع للتصنيع من الجودة
                    $lastBack = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'sent_back_to_manufacturing')
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastBack) {
                        return false;
                    }

                    $t = $lastBack->happened_at ?? $lastBack->created_at;

                    // لا يظهر الزر إن كان قد تم تأكيد استلام إعادة العمل للتصنيع بعد هذا الإرجاع
                    $ackReworkAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type', 'manufacturing_ack_rework')
                        ->where(function ($q) use ($t, $lastBack) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastBack) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id', '>', $lastBack->id);
                                });
                        })
                        ->exists();

                    return ! $ackReworkAfter;
                })
                ->form([
                    Forms\Components\Textarea::make('note')
                        ->label('ملاحظة (اختياري)')
                        ->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->manufacturingAcknowledgeRework($this->record, $data['note'] ?? null);
                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('تم تأكيد استلام التصنيع (إعادة عمل)')
                        ->send();
                    $this->record->refresh();
                }),

            Action::make('installationAcknowledgeAfterQAApprove')
                ->label('تأكيد استلام التركيب (بعد اعتماد جودة التصنيع)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(function () {
                    $user = auth()->user();
                    if (($this->record->current_owner_role ?? null) !== 'installation_manager') return false;

                    // آخر اعتماد للتصنيع
                    $lastApprove = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type','qa_approved_manufacturing')
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastApprove) return false;

                    $t = $lastApprove->happened_at ?? $lastApprove->created_at;

                    // هل يوجد install_acknowledged بعد هذا الاعتماد؟
                    $ackInstallAfter = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type','install_acknowledged')
                        ->where(function ($q) use ($t, $lastApprove) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastApprove) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastApprove->id);
                                });
                        })
                        ->exists();

                    return ! $ackInstallAfter;
                })

                ->requiresConfirmation()
                ->action(function () {
                    // نفس خدمة التدفق الحالية لتأكيد استلام التركيب
                    $this->workflow()->installationAcknowledge($this->record);

                    \Filament\Notifications\Notification::make()
                        ->success()
                        ->title('تم تأكيد استلام قسم التركيب')
                        ->send();
                }),


            Action::make('startInstallation')
                ->label('بدء التركيب')->icon('heroicon-o-wrench-screwdriver')->color('info')
                ->visible(function () {
                    $u = auth()->user();
                    if (($this->record->current_owner_role ?? null) !== 'installation_manager') return false;

                    // لو فيه owner user محدد لازم يطابق
                    if (!blank($this->record->current_owner_user_id) && (int) $this->record->current_owner_user_id !== (int) $u?->id) {
                        return false;
                    }

                    // آخر تأكيد للتركيب (استلام أول أو إعادة عمل)
                    $lastAck = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['install_acknowledged','install_ack_rework'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastAck) return false;

                    $t = $lastAck->happened_at ?? $lastAck->created_at;

                    // لا يوجد installation_started بعد هذا التأكيد
                    $startedAfter = \App\Models\TaskLog::query()
                        ->where('task_id',$this->record->id)
                        ->where('type','installation_started')
                        ->where(function ($q) use ($t, $lastAck) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastAck) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastAck->id);
                                });
                        })
                        ->exists();

                    return ! $startedAfter;
                })
                ->form([
                    Forms\Components\DateTimePicker::make('started_at')->label('تاريخ/وقت البدء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->startInstallation($this->record, $data['started_at'], $data['note'] ?? null);
                    \Filament\Notifications\Notification::make()->success()->title('تم بدء التركيب')->send();

                    // حدّث الصفحة فورًا
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),


            Action::make('finishInstallationAndSendQA')
                ->label('إنهاء التركيب وإرسال للجودة')->icon('heroicon-o-paper-airplane')->color('success')
                ->visible(function () {
                    $u = auth()->user();
                    if (! $u || ! $u->hasRole('installation_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'installation_manager') return false;
                    if (!blank($this->record->current_owner_user_id) && (int) $this->record->current_owner_user_id !== (int) $u->id) return false;

                    $lastAck = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['install_acknowledged','install_ack_rework'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastAck) return false;
                    $t = $lastAck->happened_at ?? $lastAck->created_at;

                    $startedAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->where('type','installation_started')
                        ->where(function ($q) use ($t, $lastAck) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastAck) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastAck->id);
                                });
                        })
                        ->exists();

                    if (! $startedAfter) return false;

                    $sentAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->where('type','installation_sent_to_qa')
                        ->where(function ($q) use ($t, $lastAck) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastAck) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastAck->id);
                                });
                        })
                        ->exists();

                    return ! $sentAfter;
                })

                ->form([
                    Forms\Components\DateTimePicker::make('finished_at')->label('تاريخ/وقت الإنهاء')->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
                ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->finishInstallationToQA($this->record, $data['finished_at'], $data['note'] ?? null);
                    Notification::make()->success()->title('تم إرسال التركيب للجودة')->send();
                    $this->redirect($this->getRedirectUrl());
                }),

            Action::make('qaAcknowledgeInstallation')
                ->label('تأكيد استلام الجودة (التركيب)')->icon('heroicon-o-inbox-arrow-down')->color('info')
                ->visible(function () {
                    $user = auth()->user();
                    if (! $user || ! $user->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastHandoff || $lastHandoff->type !== 'installation_sent_to_qa') return false;

                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;

                    $ackAfter = \App\Models\TaskLog::query()
                        ->where('task_id',$this->record->id)
                        ->where('type','qa_ack_installation')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $ackAfter;
                })

                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->qaAcknowledgeInstallation($this->record);
                    Notification::make()->success()->title('تم تأكيد استلام الجودة للتركيب')->send();
                }),

            Action::make('approveInstallationQA')
                ->label('اعتماد الجودة (بعد التركيب)')->icon('heroicon-o-check-badge')->color('success')
                ->visible(function () {
                    $u = auth()->user();
                    if (! $u || ! $u->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastHandoff || $lastHandoff->type !== 'installation_sent_to_qa') return false;

                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;

                    $ackAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->where('type','qa_ack_installation')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    if (! $ackAfter) return false;

                    $decisionAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->whereIn('type',['qa_approved_installation','qa_rejected_installation'])
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $decisionAfter;
                })

                ->form([ Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3), ])
                ->requiresConfirmation()
                ->action(function (array $data) {
                    $this->workflow()->approveInstallationQA($this->record, $data['note'] ?? null);
                    Notification::make()->success()->title('تم اعتماد الجودة لما بعد التركيب')->send();
                }),

            Action::make('rejectInstallationQA')
                ->label('رفض الجودة (التركيب)')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(function () {
                    $u = auth()->user();
                    if (! $u || ! $u->hasRole('quality_manager')) return false;
                    if (($this->record->current_owner_role ?? null) !== 'quality_manager') return false;

                    $lastHandoff = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->whereIn('type', ['manufacturing_sent_to_qa','installation_sent_to_qa','sent_to_quality'])
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastHandoff || $lastHandoff->type !== 'installation_sent_to_qa') return false;

                    $t = $lastHandoff->happened_at ?? $lastHandoff->created_at;

                    $ackAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->where('type','qa_ack_installation')
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    if (! $ackAfter) return false;

                    $decisionAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->whereIn('type',['qa_approved_installation','qa_rejected_installation'])
                        ->where(function ($q) use ($t, $lastHandoff) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastHandoff) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastHandoff->id);
                                });
                        })
                        ->exists();

                    return ! $decisionAfter;
                })

                ->form([
                    \Filament\Forms\Components\Textarea::make('reason')->label('سبب الرفض')->rows(3)->required(),
                ])
                ->requiresConfirmation()
                ->action(function(array $data){
                    $this->workflow()->rejectInstallationQA($this->record, $data['reason']);
                    \Filament\Notifications\Notification::make()
                        ->warning()->title('تم رفض الجودة وأُعيدت المهمة للتركيب')->send();
                    $this->redirect($this->getRedirectUrl());
                }),


            Action::make('installationAcknowledgeRework')
                ->label('تأكيد استلام التركيب (إعادة عمل)')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->visible(function () {
                    if (($this->record->current_owner_role ?? null) !== 'installation_manager') return false;

                    $lastBack = \App\Models\TaskLog::query()
                        ->where('task_id', $this->record->id)
                        ->where('type','sent_back_to_install')
                        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
                        ->first();

                    if (! $lastBack) return false;
                    $t = $lastBack->happened_at ?? $lastBack->created_at;

                    $ackReworkAfter = \App\Models\TaskLog::where('task_id',$this->record->id)
                        ->where('type','install_ack_rework')
                        ->where(function ($q) use ($t, $lastBack) {
                            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$t])
                                ->orWhere(function ($q2) use ($t, $lastBack) {
                                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$t])
                                        ->where('id','>', $lastBack->id);
                                });
                        })
                        ->exists();

                    return ! $ackReworkAfter;
                })
                ->requiresConfirmation()
                ->action(function () {
                    $this->workflow()->installationAcknowledgeRework($this->record);
                    \Filament\Notifications\Notification::make()->success()->title('تم تأكيد استلام التركيب (إعادة عمل)')->send();

                    // << التحديثات المهمة
                    $this->record->refresh();
                    $this->dispatch('close-modal', id: 'filament.actions.modal');
                    $this->js('$wire.$refresh()');
                }),


            /* سند استلام العميل → اكتمال */
            Action::make('uploadClientReceipt')
                ->label('رفع سند استلام العميل وإكمال المهمة')
                ->icon('heroicon-o-arrow-up-on-square')
                ->color('success')
                ->visible(fn () =>
                    ! $this->helper()->isClosedOrCompleted($this->record)
                    && $this->helper()->hasLog($this->record, 'qa_approved_installation')
                    && empty($this->record->client_receipt)
                )
                ->form([
                    Forms\Components\FileUpload::make('client_receipt')
                        ->label('سند استلام العميل')
                        ->disk('public')
                        ->directory(fn () => 'client-receipts/' . now()->format('Y/m'))
                        ->preserveFilenames()
                        ->downloadable()
                        ->openable()
                        ->acceptedFileTypes(['application/pdf', 'image/*'])
                        ->maxSize(10240) // 10 MB
                        ->required(),
                    Forms\Components\DateTimePicker::make('actual_finished_at')->label('تاريخ/وقت الانتهاء الفعلي للمهمة')->native(false)->default(now())->required(),
                    Forms\Components\Textarea::make('note')->label('ملاحظة (اختياري)')->rows(3),
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

    /* =============================== Infolist ===============================*/
    public function infolist(Infolist $infolist): Infolist
    {
        $h = $this->helper();

        return $infolist->schema([
            Section::make('بيانات المهمة')->schema([
                TextEntry::make('id')->label('رقم المهمة')->color('primary'),
                TextEntry::make('project.project_name')->label('المشروع')->placeholder('—')->color('primary'),
                TextEntry::make('department.dept_name')->label('القسم')->placeholder('—')->color('primary'),
                TextEntry::make('employee.employee_name')->label('المسؤول')->placeholder('—')->color('primary'),
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
                TextEntry::make('due_date')->label('تاريخ التسليم')->date()->badge()
                    ->color(function ($state) {
                        if (blank($state)) return 'gray';
                        $due = $state instanceof \Illuminate\Support\Carbon ? $state : \Illuminate\Support\Carbon::parse($state);
                        return now()->gt($due) ? 'danger' : 'success';
                    }),
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

            /* سجل الحركات الأساسية (مع إزالة التكرار في نفس الثانية) */
            Section::make('سجل الحركات')
                ->schema([
                    ViewEntry::make('logs_timeline')
                        ->view('filament.task.logs-timeline')
                        ->state(function (ProductionTask $record) {
                            $logs = \App\Models\TaskLog::query()
                                ->where('task_id', $record->id)
                                ->core()               // الحركات الأساسية فقط
                                ->with('causer')
                                ->orderByRaw('COALESCE(happened_at, created_at) DESC')
                                ->take(500)
                                ->get();

                            // إزالة التكرار: نفس (النوع + نفس صاحب الحركة + نفس الثانية)
                            $deduped = $logs->unique(function ($log) {
                                $sec = optional($log->happened_at ?: $log->created_at)->format('Y-m-d H:i:s');
                                return "{$log->type}|{$log->causer_id}|{$sec}";
                            })->values();

                            return $deduped;
                        }),
                ])
                ->columnSpanFull(),

            Section::make('ملفات المهمة')
                ->visible(fn () =>
                    auth()->check() &&
                    auth()->user()->hasAnyRole([
                        'admin',
                        'super-admin',
                        'super_admin',
                        'factory_manager',
                        'department_manager',
                        'purchasing_manager',
                        'sales_manager',
                        'showroom_manager',
                    ])
                )
                ->schema([
                    // ملف الاتفاقية
                    TextEntry::make('agreement_file')
                        ->label('ملف الاتفاقية')
                        ->html()
                        ->state(function (ProductionTask $record) {
                            $pr = $record->project?->productionRequest;
                            if (! $pr || blank($pr->agreement_file)) {
                                return '<span style="opacity:.7">—</span>';
                            }
                            $url  = Storage::disk('public')->url($pr->agreement_file);
                            $name = e(basename($pr->agreement_file));
                            return '<a href="'.e($url).'" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                        }),

                    // ملف التصنيع الخاص بالقسم
                    TextEntry::make('manufacturing_file')
                        ->label('ملف التصنيع (للقسم)')
                        ->html()
                        ->state(function (ProductionTask $record) {
                            $file = $record->project?->productionRequest?->files()
                                ->where('department_id', $record->department_id)
                                ->latest()->first();

                            if (! $file || blank($file->file_path)) {
                                return '<span style="opacity:.7">—</span>';
                            }

                            $url  = Storage::disk('public')->url($file->file_path);
                            $name = e(basename($file->file_path));
                            return '<a href="'.e($url).'" target="_blank" style="color:#16a34a; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
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

            Section::make('المشتريات')->schema([
                TextEntry::make('po_file_link')->label('أمر الشراء (PO)')->html()
                    ->state(function (ProductionTask $record) {
                        $mr = $record->materialRequests()->orderByDesc('id')->first();
                        if (! $mr || blank($mr->po_file)) {
                            return '<span style="opacity:.7">—</span>';
                        }
                        $url  = Storage::disk('public')->url($mr->po_file);
                        $name = e(basename($mr->po_file));
                        return '<a href="'.e($url).'" target="_blank" style="color:#2563eb; text-decoration:underline; font-weight:600;">'.$name.' ▸</a>';
                    }),
            ])->columns(1),

            Section::make('مدد المراحل')->schema([
                TextEntry::make('stage_durations_html')
                    ->label('تفصيل مدد كل مرحلة')
                    ->html()
                    ->state(fn (ProductionTask $record) => $this->helper()->renderStageDurationsHtml($record))
                    ->columnSpanFull(),
            ])->columns(1),

            Section::make('إحصائيات')->schema([
                TextEntry::make('total_time')->label('إجمالي الوقت منذ أول حدث')
                    ->state(function (ProductionTask $record) {
                        $firstAt = $record->logs()->min('happened_at');
                        if (! $firstAt) return '—';
                        $lastAt = $record->logs()->max('happened_at') ?? now();
                        return \Illuminate\Support\Carbon::parse($firstAt)->diffForHumans(\Illuminate\Support\Carbon::parse($lastAt), true);
                    }),

                TextEntry::make('status')
                    ->label('الحالة')
                    ->formatStateUsing(fn ($state) =>
                    $this->helper()->statusAr(
                        $state instanceof \BackedEnum
                            ? $state->value
                            : $this->helper()->normalizeStatus((string) $state)
                    )
                    )
                    ->badge()
                    ->color(fn ($state) =>
                    $this->helper()->statusColor(
                        $state instanceof \BackedEnum
                            ? $state->value
                            : $this->helper()->normalizeStatus((string) $state)
                    )
                    )
                    ->placeholder('—'),
            ])->columns(2),
        ]);
    }
}
