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

logMsg("--- PERMISSIONS FOR department_manager ---");
$role = Role::where('name', 'department_manager')->where('guard_name', 'web')->first();
if ($role) {
    logMsg("Role found: department_manager");
    logMsg("Permissions: " . $role->permissions->pluck('name')->implode(', '));
} else {
    logMsg("Role department_manager (web) not found!");
}

logMsg("\n--- CHECKING USER 16 SPECIFICALLY ---");
$u16 = User::find(16);
if ($u16) {
    logMsg("User 16: {$u16->name} ({$u16->email})");
    logMsg("Roles: " . $u16->roles->pluck('name')->implode(', '));
    logMsg("Can view_task? " . ($u16->can('view_task') ? 'YES' : 'NO'));
    logMsg("Employee Dept ID: " . ($u16->employee?->department_id ?? 'NULL'));
    logMsg("Managed Dept IDs: " . $u16->managedDepartments->pluck('dept_id')->implode(', '));
} else {
    logMsg("User 16 not found!");
}

logMsg("\n--- CHECKING ROLE PERMISSIONS IN DB (RAW) ---");
$perms = DB::table('role_has_permissions')
    ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
    ->join('roles', 'roles.id', '=', 'role_has_permissions.role_id')
    ->where('roles.name', 'department_manager')
    ->select('permissions.name')
    ->get();
logMsg("Raw Permissions for dept manager: " . $perms->pluck('name')->implode(', '));
