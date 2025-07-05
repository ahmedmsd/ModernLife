<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $entities = ['employees', 'users', 'roles', 'permissions', 'reports', 'dashboard'];

        $actions = ['view', 'create', 'edit', 'delete'];

        foreach ($entities as $entity) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "{$action} {$entity}"]);
            }
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $viewer = Role::firstOrCreate(['name' => 'viewer']);

        $admin->syncPermissions(Permission::all());

        $editor->syncPermissions(Permission::whereIn('name', [
            'view employees',
            'edit employees',
            'create employees',
            'view dashboard',
            'view reports',
        ])->get());

        $viewer->syncPermissions(Permission::whereIn('name', [
            'view employees',
            'view dashboard',
            'view reports',
        ])->get());

        $this->command->info('✅ Roles and permissions created successfully.');
    }
}
