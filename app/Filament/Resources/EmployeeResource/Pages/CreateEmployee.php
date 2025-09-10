<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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

        unset($data['user'], $data['roles_ids'], $data['roles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $employee = $this->record;

        if (! $employee->user && ! empty($this->data['user'])) {
            $employee->user()->create($this->data['user']);
            $employee->refresh();
        }

        if ($employee->user) {
            $state = $this->form->getRawState();

            $ids = $state['roles_ids'] ?? $state['roles'] ?? [];
            $ids = array_values(array_filter(array_map('intval', (array) $ids)));

            $guard = $employee->user->guard_name ?? config('auth.defaults.guard', 'web');

            $validIds = Role::query()
                ->where('guard_name', $guard)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            $employee->user->roles()->sync($validIds);

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
