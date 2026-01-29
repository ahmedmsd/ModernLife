<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ProductionTask;
use App\Models\User;
use App\Models\TaskLog;
use App\Filament\Actions\Task\Manufacturing\StartProductionAction;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

// Create a dummy user with department_manager role
$user = User::factory()->create();
$role = Role::firstOrCreate(['name' => 'department_manager', 'guard_name' => 'web']);
$user->assignRole($role);

Auth::login($user);

// Create a ProductionTask
// Create a ProductionTask
$task = new ProductionTask();
$task->status = 'rework';
$task->current_owner_role = 'department_manager';
$task->current_owner_user_id = $user->id;
// Fake IDs to satisfy potential FK checks or just basic requirements
$task->project_id = 1; 
$task->department_id = 1;
// Disable events/validation if possible, but save() works. 
// We might need to ensure IDs exist or use forceCreate if mass assignment protection (it is guarded=[])
$task->saveQuietly(); // saveQuietly avoids events


// 1. Simulate OLD start
$oldStart = TaskLog::create([
    'task_id' => $task->id,
    'type' => 'manufacturing_started',
    'created_at' => now()->subDays(2),
]);

// 2. Simulate Ack Rework (The user said they just acknowledged it)
$ackRework = TaskLog::create([
    'task_id' => $task->id,
    'type' => 'manufacturing_ack_rework',
    'created_at' => now()->subMinutes(5),
]);

// Check visibility
$refMethod = new ReflectionMethod(StartProductionAction::class, 'isVisible');
$refMethod->setAccessible(true);
$isVisible = $refMethod->invoke(null, $task);

echo "Is Visible: " . ($isVisible ? 'YES' : 'NO') . "\n";

if (!$isVisible) {
    echo "Debugging why invisible...\n";
    // Check anchor
    $anchor = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_ack_rework')
            ->orderByRaw('COALESCE(happened_at, created_at) DESC, id DESC')
            ->first();
    
    echo "Anchor found: " . ($anchor ? 'YES' . $anchor->id : 'NO') . "\n";
    
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
            
        echo "Started After: " . ($startedAfter ? 'YES' : 'NO') . "\n";
        
        if ($startedAfter) {
            $logs = TaskLog::query()
            ->where('task_id', $task->id)
            ->where('type', 'manufacturing_started')
            ->get();
            foreach($logs as $l) {
                echo "Start Log: {$l->id} at " . ($l->happened_at ?? $l->created_at) . "\n";
            }
             echo "Anchor at: $anchorTime\n";
        }
    }
}
