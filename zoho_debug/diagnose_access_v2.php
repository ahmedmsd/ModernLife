<?php

use App\Models\User;
use App\Models\Department;
use App\Models\ProductionTask;
use App\Models\Employee;
use Spatie\Permission\Models\Role;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function logMsg($msg) {
    echo $msg . "\n";
}

logMsg("\n--- CHECKING USER 19 SPECIFICALLY ---");
$u19 = User::find(19);
if ($u19) {
    logMsg("User 19: {$u19->name} ({$u19->email})");
    logMsg("Roles: " . $u19->roles->pluck('name')->implode(', '));
    logMsg("Can view_task? " . ($u19->can('view_task') ? 'YES' : 'NO'));
    logMsg("Employee Dept ID: " . ($u19->employee?->department_id ?? 'NULL'));
    logMsg("Managed Dept IDs: " . $u19->managedDepartments->pluck('dept_id')->implode(', '));
} else {
    logMsg("User 19 not found!");
}

logMsg("\n--- CHECKING TASK 2 SPECIFICALLY ---");
$t2 = ProductionTask::find(2);
if ($t2) {
    logMsg("Task 2 ID: {$t2->id}");
    logMsg("Department ID: " . ($t2->department_id ?? 'NULL'));
    logMsg("Current Owner Role: " . ($t2->current_owner_role ?? 'NULL'));
    logMsg("Current Owner User ID: " . ($t2->current_owner_user_id ?? 'NULL'));
    logMsg("Assigned To User ID: " . ($t2->assigned_to_user_id ?? 'NULL'));
    logMsg("Status: {$t2->status}");
} else {
    logMsg("Task 2 not found!");
}

logMsg("\n--- SIMULATING QUERY SCOPE FOR USER 19 ---");
if ($u19) {
    Auth::login($u19);
    $q = \App\Filament\Resources\TaskResource::getPermissionScopedQuery();
    logMsg("Query SQL: " . $q->toSql());
    logMsg("Query Bindings: " . json_encode($q->getBindings()));
    
    $exists = (clone $q)->where('id', 2)->exists();
    logMsg("Does Task 2 exist in scoped query? " . ($exists ? 'YES' : 'NO'));
}
