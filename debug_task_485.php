<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\ProductionTask;
use App\Models\ProductionRequest;

// Sometimes it's ProductionTask, sometimes ProductionRequest
$task = ProductionTask::find(485) ?? ProductionRequest::find(485);

if (!$task) {
    echo "Task/Request 485 NOT FOUND!\n";
    exit;
}

echo "=== Task 485 Details ===\n";
echo "Type: " . get_class($task) . "\n";
echo "Department ID: " . ($task->department_id ?? 'NULL') . "\n";
if ($task->department) {
    echo "Department Name: " . $task->department->name . "\n";
    echo "Department Manager ID: " . $task->department->manager_id . "\n";
    if ($task->department->manager) {
        echo "Department Manager Name: " . $task->department->manager->name . "\n";
    }
}

echo "Current Owner Role: " . ($task->current_owner_role ?? 'NULL') . "\n";
echo "Current Owner User ID: " . ($task->current_owner_user_id ?? 'NULL') . "\n";
if ($task->currentOwnerUser) {
    echo "Current Owner User Name: " . $task->currentOwnerUser->name . "\n";
}

if ($task->assigned_to_user_id) {
    echo "Assigned To User ID: " . $task->assigned_to_user_id . "\n";
    echo "Assigned To User Name: " . ($task->assignedToUser->name ?? 'NULL') . "\n";
}

echo "\nHow the UI shows responsible person:\n";
// Let's check Filament resource for the column.
