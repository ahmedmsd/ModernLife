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

logMsg("========================================");
logMsg("CHECKING USER 19");
logMsg("========================================");
$u19 = User::find(19);
if ($u19) {
    logMsg("Name: {$u19->name}");
    logMsg("Roles: " . $u19->roles->pluck('name')->implode(', '));
    logMsg("Employee Dept ID: " . ($u19->employee?->department_id ?? 'NULL'));
    $managed = $u19->managedDepartments->pluck('dept_id')->toArray();
    logMsg("Managed Dept IDs: " . implode(', ', $managed));
} else {
    logMsg("User 19 not found!");
}

logMsg("\n========================================");
logMsg("CHECKING TASK 2");
logMsg("========================================");
$t2 = ProductionTask::find(2);
if ($t2) {
    logMsg("Task ID: {$t2->id}");
    logMsg("Department ID: " . ($t2->department_id ?? 'NULL'));
    logMsg("Current Owner Role: " . ($t2->current_owner_role ?? 'NULL'));
    logMsg("Current Owner User ID: " . ($t2->current_owner_user_id ?? 'NULL'));
    logMsg("Assigned To User ID: " . ($t2->assigned_to_user_id ?? 'NULL'));
} else {
    logMsg("Task 2 not found!");
}

logMsg("\n========================================");
logMsg("QUERY SIMULATION");
logMsg("========================================");
if ($u19) {
    Auth::login($u19);
    $q = \App\Filament\Resources\TaskResource::getPermissionScopedQuery();
    
    $fullSql = $q->toSql();
    $bindings = $q->getBindings();
    
    logMsg("SQL: " . $fullSql);
    logMsg("Bindings: " . json_encode($bindings));
    
    $exists = (clone $q)->where('id', 2)->exists();
    logMsg("Task 2 in result set? " . ($exists ? "YES" : "NO"));
}
