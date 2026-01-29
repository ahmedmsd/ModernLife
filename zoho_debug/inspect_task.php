<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionTask;
use App\Models\TaskLog;
use App\Filament\Actions\Task\Manufacturing\StartProductionAction;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

$id = 136;
$task = ProductionTask::find($id);

if (!$task) {
    echo "Task $id not found.\n";
    exit(1);
}

echo "Task ID: {$task->id}\n";
echo "Status: {$task->status}\n";
echo "Owner Role: {$task->current_owner_role}\n";
echo "Owner User ID: {$task->current_owner_user_id}\n";

// Log in as the owner to test visibility
if ($task->current_owner_user_id) {
    Auth::loginUsingId($task->current_owner_user_id);
    echo "Logged in as User ID: " . Auth::id() . "\n";
    $user = Auth::user();
    echo "User roles: " . implode(', ', $user->getRoleNames()->toArray()) . "\n";
} else {
    echo "No owner user set.\n";
}

// Check Logs
echo "\n--- Last 10 Logs ---\n";
$logs = TaskLog::where('task_id', $id)->orderByDesc('created_at')->take(10)->get();
foreach ($logs as $log) {
    echo "[{$log->id}] {$log->type} at " . ($log->happened_at ?? $log->created_at) . "\n";
}

// Check Visibility
$refMethod = new ReflectionMethod(StartProductionAction::class, 'isVisible');
$refMethod->setAccessible(true);
$isVisible = $refMethod->invoke(null, $task);

echo "\nIs StartProductionAction Visible? " . ($isVisible ? 'YES' : 'NO') . "\n";

if (!$isVisible) {
    echo "\nDebugging StartProductionAction Logic:\n";
    
    // 1. Role Check
    $u = Auth::user();
    if (!$u || !$u->hasRole('department_manager', 'web')) {
        echo "- FAIL: User is not department_manager\n";
    } else {
        echo "- PASS: User is department_manager\n";
    }

    // 2. Owner Check
    if (($task->current_owner_role ?? null) !== 'department_manager') {
        echo "- FAIL: Current owner role is not department_manager (is '{$task->current_owner_role}')\n";
    } else {
        echo "- PASS: Current owner role is department_manager\n";
    }

    // 3. Status Check
    $status = strtolower((string) ($task->status ?? ''));
    if (!in_array($status, ['waiting_production', 'rework', 'in_progress'], true)) {
        echo "- FAIL: Status '{$status}' not allowed\n";
    } else {
        echo "- PASS: Status '{$status}' is allowed\n";
    }

    // 4. Anchor Check
    $anchor = TaskLog::query()
        ->where('task_id', $task->id)
        ->where('type', 'manufacturing_ack_rework')
        ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
        ->first();

    if ($anchor) {
        echo "- FOUND Anchor (manufacturing_ack_rework): ID {$anchor->id} at " . ($anchor->happened_at ?? $anchor->created_at) . "\n";
    } else {
        echo "- NO Anchor (manufacturing_ack_rework) found. Trying backup anchors...\n";
        $anchor = TaskLog::query()
            ->where('task_id', $task->id)
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
            echo "- FOUND Backup Anchor ({$anchor->type}): ID {$anchor->id} at " . ($anchor->happened_at ?? $anchor->created_at) . "\n";
        } else {
            echo "- FAIL: No anchor found at all.\n";
        }
    }

    if ($anchor) {
        $anchorTime = $anchor->happened_at ?? $anchor->created_at;
        $anchorId   = $anchor->id;

        $startedAfter = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_started')
            ->where(function ($q) use ($anchorTime, $anchorId) {
                $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                    ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                        $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                            ->where('id', '>', $anchorId);
                    });
            })
            ->exists();
        
        if ($startedAfter) {
            echo "- FAIL: manufacturing_started exists after anchor.\n";
            $starters = TaskLog::query()
                ->where('task_id', $task->id)
                ->where('type', 'manufacturing_started')
                ->where(function ($q) use ($anchorTime, $anchorId) {
                    $q->whereRaw('COALESCE(happened_at, created_at) > ?', [$anchorTime])
                        ->orWhere(function ($q2) use ($anchorTime, $anchorId) {
                            $q2->whereRaw('COALESCE(happened_at, created_at) = ?', [$anchorTime])
                                ->where('id', '>', $anchorId);
                        });
                })->get();
            foreach($starters as $s) {
                 echo "  -> Started Log ID {$s->id} at " . ($s->happened_at ?? $s->created_at) . "\n";
            }
        } else {
            echo "- PASS: No manufacturing_started after anchor.\n";
        }
    }
}
