<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['user'])) {
            $userData = $data['user'];
            $user = User::create([
                'name'     => $data['employee_name'] ?? ($userData['email'] ?? 'User'),
                'email'    => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);
            $data['user_id'] = $user->id;
        }

        unset($data['user']); // لا تحفظ مصفوفة user في جدول employees
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
