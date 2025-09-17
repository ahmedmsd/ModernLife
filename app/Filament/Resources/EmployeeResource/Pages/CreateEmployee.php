<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['user'])) {
            $userData = $data['user'];

            $password = $userData['password'] ?? null;

            if (filled($password) && ! Str::startsWith($password, ['$2y$', '$argon2'])) {
                $password = Hash::make($password);
            }

            $user = User::create([
                'name'     => $data['employee_name'] ?? ($userData['email'] ?? 'User'),
                'email'    => $userData['email'],
                'password' => $password,
            ]);

            $data['user_id'] = $user->id;
        }

        unset($data['user'], $data['roles_ids'], $data['roles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $employee = $this->record;

        $state = $this->form->getRawState();

        if (! $employee->user && ! empty($state['user'])) {
            $userData = $state['user'];

            $password = $userData['password'] ?? null;
            if (filled($password) && ! Str::startsWith($password, ['$2y$', '$argon2'])) {
                $password = Hash::make($password);
            }

            $employee->user()->create([
                'name'     => $employee->employee_name ?? ($userData['email'] ?? 'User'),
                'email'    => $userData['email'],
                'password' => $password,
            ]);
            $employee->refresh();
        }

        if ($employee->user) {
            $ids = $state['roles_ids'] ?? $state['roles'] ?? [];
            $ids = array_values(array_filter(array_map('intval', (array) $ids)));

            $guard = config('auth.defaults.guard', 'web');

            $roleNames = Role::query()
                ->where('guard_name', $guard)
                ->whereIn('id', $ids)
                ->pluck('name')
                ->all();

            $employee->user->syncRoles($roleNames);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
