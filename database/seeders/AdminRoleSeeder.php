<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class AdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Roles
            'view_any_role','view_roles', 'create_roles', 'edit_roles', 'delete_roles',

            // Permissions
            'view_permissions', 'create_permissions', 'edit_permissions', 'delete_permissions',

            // Users
            'view_users', 'create_users', 'edit_users', 'delete_users', 'manage_users',

            // Employees
            'view_employees', 'create_employees', 'edit_employees', 'delete_employees', 'manage_employees',
        ];

        // إنشاء الصلاحيات
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // إنشاء الدور admin
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // ربط الصلاحيات بالدور
        $adminRole->syncPermissions($permissions);

        // تعيين الدور للمستخدم رقم 1
        $user = User::find(1);
        if ($user) {
            $user->syncRoles($adminRole);
        }
    }
}
