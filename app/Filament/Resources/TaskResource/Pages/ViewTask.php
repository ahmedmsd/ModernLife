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
            Section::make('مدد مراحل التصنيع')->schema([
                TextEntry::make('stage_durations_html')
                    ->label(false)
                    ->html()
                    ->columnSpanFull()
                    ->extraAttributes(['class' => 'w-full'])
                    ->state(function (ProductionTask $record) {
                        $logs = TaskLog::where('task_id', $record->id)
                            ->orderByRaw('COALESCE(happened_at, created_at) ASC')
                            ->orderBy('id')
                            ->get();

                        if ($logs->isEmpty()) {
                            return '<div style="padding: 16px; text-align: center; color: #6b7280;">لا توجد بيانات كافية لحساب مدد المراحل</div>';
                        }

                        // Helper to get log time
                        $getTime = fn($log) => $log->happened_at ?? $log->created_at;

                        // Helper to find log by types (after a certain log if provided)
                        $findLog = function($types, $afterLog = null) use ($logs, $getTime) {
                            return $logs->first(function($log) use ($types, $afterLog, $getTime) {
                                if (!in_array($log->type, (array)$types)) return false;
                                if ($afterLog) {
                                    // Only compare time, not ID (logs might not be in perfect order)
                                    $afterTime = $getTime($afterLog);
                                    $logTime = $getTime($log);
                                    return $logTime >= $afterTime;
                                }
                                return true;
                            });
                        };

                        // Helper to format duration
                        $formatDuration = function($startTime, $endTime) {
                            $start = Carbon::parse($startTime);
                            $end = $endTime ? Carbon::parse($endTime) : Carbon::now();
                            $diffInMinutes = (int) $start->diffInMinutes($end); // Force integer

                            if ($diffInMinutes < 60) {
                                return $diffInMinutes . ' دقيقة';
                            } elseif ($diffInMinutes < 1440) {
                                $hours = floor($diffInMinutes / 60);
                                $mins = $diffInMinutes % 60;
                                return $hours . ' ساعة' . ($mins > 0 ? ' و ' . $mins . ' دقيقة' : '');
                            } else {
                                $days = floor($diffInMinutes / 1440);
                                $remainingMins = $diffInMinutes % 1440;
                                $hours = floor($remainingMins / 60);
                                return $days . ' يوم' . ($hours > 0 ? ' و ' . $hours . ' ساعة' : '');
                            }
                        };


                        $phases = [];
                        $currentStatus = $record->status;
                        $currentOwnerRole = $record->current_owner_role;

                        // Phase 1: Department Assignment to Acknowledgment
                        $assignLog = $findLog(['assign_to_dept_manager', 'assigned_to_dept_manager', 'assigned_to_department_manager', 'ownership_changed', 'created']);
                        if (!$assignLog) {
                            $assignLog = $logs->first(); // Fallback to first log
                        }
                        if ($assignLog) {
                            // Try to find explicit acknowledgment log
                            $ackLog = $findLog(['dept_acknowledge', 'dept_acknowledged', 'owner_received'], $assignLog);
                            
                            // Fallback: if no ack log but task moved to next phase (manufacturing started, materials, etc.)
                            // Consider the dept phase completed when the next phase started
                            $nextPhaseLog = $findLog([
                                'materials_request_opened', 
                                'manufacturing_started',
                                'materials_received_ok',
                                'materials_received_partial'
                            ], $assignLog);
                            
                            if (!$ackLog && $nextPhaseLog) {
                                // Use the next phase start as the end of dept phase
                                $ackLog = $nextPhaseLog;
                            }
                            
                            $startTime = $getTime($assignLog);
                            $endTime = $ackLog ? $getTime($ackLog) : null;
                            $isCompleted = $ackLog !== null;
                            
                            // Check if this is current phase
                            $isCurrent = !$isCompleted && in_array($currentStatus, ['pending', 'received']) && $currentOwnerRole === 'department_manager';
                            
                            $phases[] = [
                                'name' => 'انتظار استلام القسم',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                            ];
                        }

                        // Phase 2: Materials (Optional - only if materials request exists)
                        $materialsStartLog = $findLog(['materials_request_opened', 'materials_followup_opened', 'purchasing_ack', 'sent_to_purchasing']);
                        if ($materialsStartLog) {
                            $materialsEndLog = $findLog(['materials_received_ok', 'materials_received_partial', 'materials_provided_note'], $materialsStartLog);
                            
                            // Fallback: if task returned to department or manufacturing started, consider materials done
                            if (!$materialsEndLog) {
                                // Look for any event that indicates materials phase ended
                                $nextPhaseLog = $findLog([
                                    'manufacturing_started',
                                    'sent_to_department',  // Task returned to dept after materials
                                ], $materialsStartLog);
                                if ($nextPhaseLog) {
                                    $materialsEndLog = $nextPhaseLog;
                                }
                            }
                            
                            $startTime = $getTime($materialsStartLog);
                            $endTime = $materialsEndLog ? $getTime($materialsEndLog) : null;
                            $isCompleted = $materialsEndLog !== null;
                            
                            // Additional check: if status is materials_done or beyond, and ownership back to dept, mark complete
                            if (!$isCompleted && in_array($currentStatus, ['materials_done', 'waiting_production', 'in_progress', 'under_review', 'approved', 'completed']) && $currentOwnerRole === 'department_manager') {
                                $isCompleted = true;
                                // Find the last log in materials phase as end time
                                $lastMaterialsLog = $logs->last(function($log) use ($materialsStartLog, $getTime) {
                                    $startT = $getTime($materialsStartLog);
                                    $logT = $getTime($log);
                                    return $logT >= $startT && in_array($log->type, ['sent_to_department', 'status_changed', 'ownership_changed']);
                                });
                                if ($lastMaterialsLog) {
                                    $endTime = $getTime($lastMaterialsLog);
                                }
                            }
                            
                            $isCurrent = !$isCompleted && in_array($currentStatus, ['materials_wait', 'materials_prep']);
                            
                            $phases[] = [
                                'name' => 'تجهيز الخامات',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                            ];
                        }

                        // Helper to find last log by types
                        $findLastLog = function($types) use ($logs, $getTime) {
                            return $logs->last(function($log) use ($types) {
                                return in_array($log->type, (array)$types);
                            });
                        };

                        // Phase 3: Manufacturing (or waiting for manufacturing)
                        $mfgStartLog = $findLog(['manufacturing_started']);
                        if ($mfgStartLog) {
                            $mfgEndLog = $findLog(['manufacturing_sent_to_qa', 'manufacturing_finished'], $mfgStartLog);
                            
                            // Fallback: if QA started or installation started, consider manufacturing done
                            if (!$mfgEndLog) {
                                $nextPhaseLog = $findLog(['qa_ack_manufacturing', 'qa_approved_manufacturing', 'install_acknowledged', 'installation_started'], $mfgStartLog);
                                if ($nextPhaseLog) {
                                    $mfgEndLog = $nextPhaseLog;
                                }
                            }
                            
                            $startTime = $getTime($mfgStartLog);
                            $endTime = $mfgEndLog ? $getTime($mfgEndLog) : null;
                            $isCompleted = $mfgEndLog !== null;
                            
                            // Also use actual_end_at from task if available
                            if ($record->actual_end_at && !$endTime) {
                                $endTime = $record->actual_end_at;
                                $isCompleted = true;
                            }
                            
                            $isCurrent = !$isCompleted && $currentStatus === 'in_progress' && $currentOwnerRole === 'department_manager';
                            
                            $phases[] = [
                                'name' => 'التصنيع',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                            ];
                        } else {
                            // No manufacturing log yet - check if task is waiting for manufacturing to start
                            if (in_array($currentStatus, ['materials_done', 'waiting_production']) && $currentOwnerRole === 'department_manager') {
                                // Find the time when materials phase ended as the start of waiting
                                // Use findLastLog to get the most recent event (e.g. sent_to_department happens multiple times)
                                $waitStartLog = $findLastLog(['sent_to_department', 'materials_received_ok', 'materials_received_partial']);
                                $waitStartTime = $waitStartLog ? $getTime($waitStartLog) : Carbon::now();
                                
                                $phases[] = [
                                    'name' => 'انتظار بدء التصنيع',
                                    'startTime' => $waitStartTime,
                                    'endTime' => null,
                                    'duration' => $formatDuration($waitStartTime, null),
                                    'isCompleted' => false,
                                    'isCurrent' => true,
                                ];
                            }
                        }

                        // Phase 4: QA after Manufacturing
                        $qaStartLog = $findLog(['manufacturing_sent_to_qa', 'qa_ack_manufacturing']);
                        if ($qaStartLog) {
                            $qaEndLog = $findLog(['qa_approved_manufacturing', 'qa_rejected_manufacturing'], $qaStartLog);
                            
                            // Fallback: if installation started, consider QA done (approved)
                            if (!$qaEndLog) {
                                $nextPhaseLog = $findLog(['install_acknowledged', 'installation_started', 'sent_to_install'], $qaStartLog);
                                if ($nextPhaseLog) {
                                    $qaEndLog = $nextPhaseLog;
                                }
                            }
                            
                            $startTime = $getTime($qaStartLog);
                            $endTime = $qaEndLog ? $getTime($qaEndLog) : null;
                            $isCompleted = $qaEndLog !== null;
                            
                            $isCurrent = !$isCompleted && $currentStatus === 'under_review' && $currentOwnerRole === 'quality_manager';
                            
                            // Check if rejected (rework)
                            $wasRejected = $qaEndLog && $qaEndLog->type === 'qa_rejected_manufacturing';
                            
                            $phases[] = [
                                'name' => 'فحص الجودة (تصنيع)',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                                'wasRejected' => $wasRejected,
                            ];
                        }

                        // Phase 5: Installation
                        $installStartLog = $findLog(['install_acknowledged', 'installation_started']);
                        if ($installStartLog) {
                            $installEndLog = $findLog(['installation_sent_to_qa', 'installation_finished'], $installStartLog);
                            
                            // Fallback: if installation QA started, consider installation done
                            if (!$installEndLog) {
                                $nextPhaseLog = $findLog(['qa_ack_installation', 'qa_approved_installation'], $installStartLog);
                                if ($nextPhaseLog) {
                                    $installEndLog = $nextPhaseLog;
                                }
                            }
                            
                            $startTime = $getTime($installStartLog);
                            $endTime = $installEndLog ? $getTime($installEndLog) : null;
                            $isCompleted = $installEndLog !== null;
                            
                            $isCurrent = !$isCompleted && in_array($currentStatus, ['approved', 'in_progress']) && $currentOwnerRole === 'installation_manager';
                            
                            $phases[] = [
                                'name' => 'التركيب',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                            ];
                        } else {
                            // No installation log yet - check if task is waiting for installation to start
                            // Status 'approved' usually means approved by QA (Manufacturing) and ready for next step
                            if (in_array($currentStatus, ['approved', 'in_progress']) && $currentOwnerRole === 'installation_manager') {
                                // Find the time when Manufacturing QA ended as the start of waiting
                                $waitStartLog = $findLastLog(['qa_approved_manufacturing']);
                                $waitStartTime = $waitStartLog ? $getTime($waitStartLog) : Carbon::now();
                                
                                $phases[] = [
                                    'name' => 'انتظار بدء التركيب',
                                    'startTime' => $waitStartTime,
                                    'endTime' => null,
                                    'duration' => $formatDuration($waitStartTime, null),
                                    'isCompleted' => false,
                                    'isCurrent' => true,
                                ];
                            }
                        }

                        // Phase 6: QA after Installation
                        $qaInstallStartLog = $findLog(['installation_sent_to_qa', 'qa_ack_installation']);
                        if ($qaInstallStartLog) {
                            $qaInstallEndLog = $findLog(['qa_approved_installation', 'qa_rejected_installation'], $qaInstallStartLog);
                            
                            // Fallback: if task completed or client receipt uploaded, consider QA done
                            if (!$qaInstallEndLog) {
                                $nextPhaseLog = $findLog(['upload_client_receipt_and_complete', 'task_completed', 'client_receipt_uploaded'], $qaInstallStartLog);
                                if ($nextPhaseLog) {
                                    $qaInstallEndLog = $nextPhaseLog;
                                }
                            }
                            
                            $startTime = $getTime($qaInstallStartLog);
                            $endTime = $qaInstallEndLog ? $getTime($qaInstallEndLog) : null;
                            $isCompleted = $qaInstallEndLog !== null;
                            
                            $isCurrent = !$isCompleted && $currentStatus === 'under_review' && $currentOwnerRole === 'quality_manager';
                            
                            $wasRejected = $qaInstallEndLog && $qaInstallEndLog->type === 'qa_rejected_installation';
                            
                            $phases[] = [
                                'name' => 'فحص الجودة (تركيب)',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                                'wasRejected' => $wasRejected,
                            ];
                        }

                        // Phase 7: Final Completion
                        $completionStartLog = $findLog(['qa_approved_installation']);
                        if ($completionStartLog) {
                            $completionEndLog = $findLog(['upload_client_receipt_and_complete', 'task_completed', 'client_receipt_uploaded', 'project_completed'], $completionStartLog);
                            $startTime = $getTime($completionStartLog);
                            $endTime = $completionEndLog ? $getTime($completionEndLog) : null;
                            $isCompleted = $completionEndLog !== null || $currentStatus === 'completed';
                            
                            // Use completed_at from task if available
                            if ($record->completed_at && $isCompleted && !$endTime) {
                                $endTime = $record->completed_at;
                            }
                            
                            $isCurrent = !$isCompleted && in_array($currentStatus, ['qa_approved', 'approved']);
                            
                            $phases[] = [
                                'name' => 'التسليم والإنهاء',
                                'startTime' => $startTime,
                                'endTime' => $endTime,
                                'duration' => $formatDuration($startTime, $endTime),
                                'isCompleted' => $isCompleted,
                                'isCurrent' => $isCurrent && !$isCompleted,
                            ];
                        }

                        if (empty($phases)) {
                            return '<div style="padding: 16px; text-align: center; color: #6b7280;">لم تبدأ أي مرحلة بعد</div>';
                        }

                        // Build HTML table with Tailwind classes for Dark Mode support
                        $html = '<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">';
                        $html .= '<table class="w-full text-sm text-right text-gray-500 dark:text-gray-400">';
                        $html .= '<thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-800 dark:text-gray-200 border-b border-gray-200 dark:border-gray-700">';
                        $html .= '<tr>';
                        $html .= '<th scope="col" class="px-4 py-3">المرحلة</th>';
                        $html .= '<th scope="col" class="px-4 py-3">بدأت في</th>';
                        $html .= '<th scope="col" class="px-4 py-3">انتهت في</th>';
                        $html .= '<th scope="col" class="px-4 py-3">المدة</th>';
                        $html .= '<th scope="col" class="px-4 py-3 text-center">الحالة</th>';
                        $html .= '</tr></thead><tbody>';

                        foreach ($phases as $index => $phase) {
                            $rowClass = 'border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 ' . 
                                ($index % 2 === 0 ? 'bg-white dark:bg-gray-900' : 'bg-gray-50 dark:bg-gray-800');
                                
                            $startFormatted = Carbon::parse($phase['startTime'])->format('Y-m-d H:i');
                            $endFormatted = $phase['endTime'] 
                                ? Carbon::parse($phase['endTime'])->format('Y-m-d H:i') 
                                : '<span class="text-gray-400 dark:text-gray-600">—</span>';
                            
                            // Status badge
                            if ($phase['isCompleted']) {
                                if (!empty($phase['wasRejected'])) {
                                    $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300';
                                    $label = '✗ رُفضت';
                                } else {
                                    $badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300';
                                    $label = '✓ مكتملة';
                                }
                            } elseif (!empty($phase['isCurrent'])) {
                                $badgeClass = 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300';
                                $label = '● حالية';
                            } else {
                                $badgeClass = 'bg-yellow-200 text-yellow-800 dark:bg-yellow-900/60 dark:text-yellow-300';
                                $label = '⏳ جاري';
                            }
                            
                            $statusBadge = "<span class=\"$badgeClass text-xs font-medium px-2.5 py-0.5 rounded border border-transparent\">$label</span>";

                            $html .= "<tr class=\"$rowClass\">";
                            $html .= '<td class="px-4 py-3 font-medium text-gray-900 dark:text-white">' . e($phase['name']) . '</td>';
                            $html .= '<td class="px-4 py-3 font-mono text-xs" dir="ltr">' . $startFormatted . '</td>';
                            $html .= '<td class="px-4 py-3 font-mono text-xs" dir="ltr">' . $endFormatted . '</td>';
                            $html .= '<td class="px-4 py-3 text-gray-700 dark:text-white font-semibold">' . e($phase['duration']) . '</td>';
                            $html .= '<td class="px-4 py-3 text-center">' . $statusBadge . '</td>';
                            $html .= '</tr>';
                        }

                        $html .= '</tbody></table></div>';

                        return $html;
                    }),
            ])->columnSpanFull(),

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


        ]);
    }
}
