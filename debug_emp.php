<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$task = \App\Models\ProductionTask::find(485);
if (!$task) {
    echo "Task 485 not found\n";
    exit;
}

echo "Task 485 Department: " . ($task->department ? $task->department->dept_name : 'NULL') . "\n";
echo "Task 485 assigned_to_user_id: " . $task->assigned_to_user_id . "\n";
echo "Task 485 current_owner_user_id: " . $task->current_owner_user_id . "\n";

if ($task->assigned_to_user_id) {
    $e = \App\Models\Employee::where('user_id', $task->assigned_to_user_id)->first();
    echo "Assigned Employee Name: " . ($e ? $e->employee_name : 'NULL') . "\n";
    if ($e && $e->department) {
        echo "Assigned Employee Dept: " . $e->department->dept_name . "\n";
    }
}

if ($task->current_owner_user_id) {
    $e = \App\Models\Employee::where('user_id', $task->current_owner_user_id)->first();
    echo "Current Owner Employee Name: " . ($e ? $e->employee_name : 'NULL') . "\n";
    if ($e && $e->department) {
        echo "Current Owner Employee Dept: " . $e->department->dept_name . "\n";
    }
}
