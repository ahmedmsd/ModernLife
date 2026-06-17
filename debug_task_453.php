<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductionTask;
use App\Models\TaskLog;

$task = ProductionTask::find(453);

if (!$task) {
    echo "Task 453 NOT FOUND!\n";
    exit;
}

echo "=== Task #453 Details ===\n";
echo "Status: " . $task->status . "\n";
echo "Current Owner Role: " . ($task->current_owner_role ?? 'NULL') . "\n";
echo "Current Owner User ID: " . ($task->current_owner_user_id ?? 'NULL') . "\n";
echo "Department ID: " . ($task->department_id ?? 'NULL') . "\n";
echo "Assigned To User ID: " . ($task->assigned_to_user_id ?? 'NULL') . "\n";
echo "Planned Start: " . optional($task->planned_start_at)->toDateTimeString() . "\n";
echo "Planned End: " . optional($task->planned_end_at)->toDateTimeString() . "\n";
echo "Planned Install: " . optional($task->planned_install_at)->toDateTimeString() . "\n";
echo "materials_state: " . ($task->materials_state ?? 'NULL') . "\n";

echo "\n=== All Task Logs (Chronological) ===\n";
$logs = TaskLog::where('task_id', 453)
    ->orderByRaw('COALESCE(happened_at, created_at) ASC, id ASC')
    ->get();

foreach ($logs as $log) {
    $time = $log->happened_at ?? $log->created_at;
    $data = $log->data ? json_encode($log->data, JSON_UNESCAPED_UNICODE) : '';
    echo sprintf(
        "[%s] ID=%d type=%-40s causer=%s data=%s\n",
        $time,
        $log->id,
        $log->type,
        $log->causer_id ?? 'NULL',
        $data
    );
}

echo "\n=== Open Material Requests ===\n";
$mrs = $task->materialRequests()->get();
foreach ($mrs as $mr) {
    echo sprintf(
        "MR#%d status=%s requested_at=%s provided_at=%s\n",
        $mr->id,
        $mr->status,
        $mr->requested_at,
        $mr->provided_at ?? 'NULL'
    );
}

// Now simulate canStartProduction logic
echo "\n=== canStartProduction Debug ===\n";
$helper = app(\App\Support\Tasks\TaskPageHelper::class);
$statusVal = $helper->statusVal($task);
echo "Normalized status: " . $statusVal . "\n";
echo "Owner is department_manager: " . ($helper->ownerIs($task, 'department_manager') ? 'YES' : 'NO') . "\n";
echo "Status in allowed list: " . (in_array($statusVal, ['waiting_production', 'rework', 'received'], true) ? 'YES' : 'NO') . "\n";

// Check anchor
$anchor = TaskLog::query()
    ->where('task_id', 453)
    ->whereIn('type', ['manufacturing_ack_rework', 'dept_acknowledge', 'dept_acknowledged'])
    ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
    ->first();

if ($anchor) {
    echo "Primary anchor found: ID={$anchor->id} type={$anchor->type}\n";
} else {
    echo "No primary anchor (ack_rework/dept_acknowledge) found.\n";
    
    $anchor = TaskLog::query()
        ->where('task_id', 453)
        ->where(function ($q) {
            $q->where('type', 'materials_received_ok')
                ->orWhere(function ($q2) {
                    $q2->where('type', 'materials_received_partial')
                        ->where('data->allow_start', true);
                })
                ->orWhere('type', 'planning_hint_set');
        })
        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
        ->first();
    
    if ($anchor) {
        echo "Secondary anchor found: ID={$anchor->id} type={$anchor->type}\n";
    } else {
        echo "NO ANCHOR FOUND AT ALL! This is why the button doesn't show.\n";
    }
}

if ($anchor) {
    $anchorTime = $anchor->happened_at ?? $anchor->created_at;
    $anchorId = $anchor->id;
    echo "Anchor time: {$anchorTime}, Anchor ID: {$anchorId}\n";
    
    $startedAfter = TaskLog::query()
        ->where('task_id', 453)
        ->where('type', 'manufacturing_started')
        ->where(function ($q) use ($anchorTime, $anchorId) {
            $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                    $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                        ->where('id', '>', $anchorId);
                });
        })
        ->exists();
    
    echo "Manufacturing started after anchor: " . ($startedAfter ? 'YES (button hidden)' : 'NO (button should show)') . "\n";
}

// Also check canMaterialsReceivedOk
echo "\n=== canMaterialsReceivedOk Debug ===\n";
$lastReceived = TaskLog::query()
    ->where('task_id', 453)
    ->whereIn('type', ['materials_received_ok', 'materials_received_partial'])
    ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
    ->first();

if ($lastReceived) {
    echo "Last materials received log: ID={$lastReceived->id} type={$lastReceived->type}\n";
} else {
    echo "No materials_received log found.\n";
}
