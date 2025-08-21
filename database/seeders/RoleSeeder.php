<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $roles = [
            'super-admin',
            'admin',
            'sales',                 // موظف المبيعات
            'showroom_manager',      // مدير المعرض
            'factory_manager',       // مدير المصنع
            'department_manager',    // مدير القسم
            'purchasing_manager',    // مدير المشتريات
            'quality_manager',       // قسم الجودة
            'installation_manager',  // مسؤول/مدير التركيب
        ];

        foreach ($roles as $name) {
            Role::findOrCreate($name, $guard);
        }


    }
}
