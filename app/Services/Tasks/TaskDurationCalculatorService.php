<?php

namespace App\Services\Tasks;

use App\Models\ProductionTask;
use App\Models\TaskLog;
use Illuminate\Support\Carbon;

class TaskDurationCalculatorService
{
    /**
     * حساب جميع مراحل المهمة ومدتها وإرجاعها كمصفوفة
     *
     * @param ProductionTask $record
     * @return array
     */
    public function calculatePhases(ProductionTask $record): array
    {
        $logs = TaskLog::where('task_id', $record->id)
            ->orderByRaw('COALESCE(happened_at, created_at) ASC')
            ->orderBy('id')
            ->get();

        if ($logs->isEmpty()) {
            return [];
        }

        // Helper to get log time
        $getTime = fn($log) => $log->happened_at ?? $log->created_at;

        // Helper to find log by types (after a certain log if provided)
        $findLog = function ($types, $afterLog = null) use ($logs, $getTime) {
            return $logs->first(function ($log) use ($types, $afterLog, $getTime) {
                if (!in_array($log->type, (array)$types)) return false;
                if ($afterLog) {
                    $afterTime = $getTime($afterLog);
                    $logTime = $getTime($log);
                    return $logTime >= $afterTime;
                }
                return true;
            });
        };

        // Helper to find last log by types
        $findLastLog = function ($types) use ($logs) {
            return $logs->last(function ($log) use ($types) {
                return in_array($log->type, (array)$types);
            });
        };

        // Helper to format duration
        $formatDuration = function ($startTime, $endTime) {
            $start = Carbon::parse($startTime);
            $end = $endTime ? Carbon::parse($endTime) : Carbon::now();
            $diffInMinutes = (int) $start->diffInMinutes($end);

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
            $assignLog = $logs->first();
        }
        if ($assignLog) {
            $ackLog = $findLog(['dept_acknowledge', 'dept_acknowledged', 'owner_received'], $assignLog);
            
            $nextPhaseLog = $findLog([
                'materials_request_opened', 
                'manufacturing_started',
                'materials_received_ok',
                'materials_received_partial'
            ], $assignLog);
            
            if (!$ackLog && $nextPhaseLog) {
                $ackLog = $nextPhaseLog;
            }
            
            $startTime = $getTime($assignLog);
            $endTime = $ackLog ? $getTime($ackLog) : null;
            $isCompleted = $ackLog !== null;
            
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

        // Phase 2: Materials
        $materialsStartLog = $findLog(['materials_request_opened', 'materials_followup_opened', 'purchasing_ack', 'sent_to_purchasing']);
        if ($materialsStartLog) {
            $materialsEndLog = $findLog(['materials_received_ok', 'materials_received_partial', 'materials_provided_note'], $materialsStartLog);
            
            if (!$materialsEndLog) {
                $nextPhaseLog = $findLog([
                    'manufacturing_started',
                    'sent_to_department',
                ], $materialsStartLog);
                if ($nextPhaseLog) {
                    $materialsEndLog = $nextPhaseLog;
                }
            }
            
            $startTime = $getTime($materialsStartLog);
            $endTime = $materialsEndLog ? $getTime($materialsEndLog) : null;
            $isCompleted = $materialsEndLog !== null;
            
            if (!$isCompleted && in_array($currentStatus, ['materials_done', 'waiting_production', 'in_progress', 'under_review', 'approved', 'completed']) && $currentOwnerRole === 'department_manager') {
                $isCompleted = true;
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

        // Phase 3: Manufacturing
        $mfgStartLog = $findLog(['manufacturing_started']);
        if ($mfgStartLog) {
            $mfgEndLog = $findLog(['manufacturing_sent_to_qa', 'manufacturing_finished'], $mfgStartLog);
            
            if (!$mfgEndLog) {
                $nextPhaseLog = $findLog(['qa_ack_manufacturing', 'qa_approved_manufacturing', 'install_acknowledged', 'installation_started'], $mfgStartLog);
                if ($nextPhaseLog) {
                    $mfgEndLog = $nextPhaseLog;
                }
            }
            
            $startTime = $getTime($mfgStartLog);
            $endTime = $mfgEndLog ? $getTime($mfgEndLog) : null;
            $isCompleted = $mfgEndLog !== null;
            
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
            if (in_array($currentStatus, ['materials_done', 'waiting_production']) && $currentOwnerRole === 'department_manager') {
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
            if (in_array($currentStatus, ['approved', 'in_progress']) && $currentOwnerRole === 'installation_manager') {
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

        return $phases;
    }
}
