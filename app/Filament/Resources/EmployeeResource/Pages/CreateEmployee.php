<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // إنشاء حساب المستخدم من البيانات المدخلة
        $user = User::create([
            'name' => $data['employee_name'],
            'email' => $data['user']['email'],
            'password' => $data['user']['password'], // مشفرة مسبقًا في الفورم
        ]);

        // ربط المستخدم بسجل الموظف
        $data['user_id'] = $user->id;

        // إزالة بيانات المستخدم المؤقتة
        unset($data['user']);

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
