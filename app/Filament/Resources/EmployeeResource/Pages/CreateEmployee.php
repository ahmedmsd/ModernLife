<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $userData = $data['user'] ?? null;

        if (! empty($userData)) {
            $plainPassword = data_get($userData, 'password');

            $password = $plainPassword;
            if (filled($plainPassword) && ! Str::startsWith($plainPassword, ['$2y$', '$argon2'])) {
                $password = Hash::make($plainPassword);
            }

            $user = User::create([
                'name'     => $data['employee_name'] ?? (data_get($userData, 'email') ?? 'User'),
                'email'    => data_get($userData, 'email'),
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
        $state    = $this->form->getRawState();

        DB::transaction(function () use ($employee, $state) {
            if (! $employee->user && ! empty($state['user'])) {
                $userData      = $state['user'];
                $plainPassword = data_get($userData, 'password');

                $password = $plainPassword;
                if (filled($plainPassword) && ! Str::startsWith($plainPassword, ['$2y$', '$argon2'])) {
                    $password = Hash::make($plainPassword);
                }

                $employee->user()->create([
                    'name'     => $employee->employee_name ?? (data_get($userData, 'email') ?? 'User'),
                    'email'    => data_get($userData, 'email'),
                    'password' => $password,
                ]);

                $employee->refresh();
            }

            if ($employee->user) {
                $ids = (array) ($state['roles_ids'] ?? $state['roles'] ?? []);
                $ids = array_values(array_filter(array_map('intval', $ids)));

                $guard = config('auth.defaults.guard', 'web');

                $roleNames = Role::query()
                    ->where('guard_name', $guard)
                    ->whereIn('id', $ids)
                    ->pluck('name')
                    ->all();

                $employee->user->syncRoles($roleNames);
            }

            DB::table(config('permission.table_names.model_has_roles', 'model_has_roles'))
                ->where('model_type', \App\Models\Employee::class)
                ->where('model_id', $employee->getKey())
                ->delete();

            app(PermissionRegistrar::class)->forgetCachedPermissions();
        });
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
