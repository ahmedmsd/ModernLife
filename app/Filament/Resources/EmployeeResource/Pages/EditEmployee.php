<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(), // Soft delete
        ];
    }

    protected function resolveRecord(int | string $key): \Illuminate\Database\Eloquent\Model
    {
        return static::getModel()::with('user.roles')->findOrFail($key);
    }

    public function mount($record = null): void
    {
        parent::mount($record);

        $this->form->fill(array_merge(
            $this->record->toArray(),
            [
                'user' => [
                    'email'             => $this->record->user?->email,
                    'password'          => null, // يُحدَّث فقط إذا أدخل المستخدم قيمة
                    'directPermissions' => $this->record->user?->directPermissions->pluck('id')->toArray(),
                ],
                'roles_ids' => $this->record->user?->roles()->pluck('id')->toArray(),
            ]
        ));
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $userData = $data['user'] ?? null;

        if ($userData) {
            $user = $this->record->user;

            $password = $userData['password'] ?? null;
            if (is_string($password) && $password !== '') {
                $info = password_get_info($password);
                if (($info['algo'] ?? 0) === 0) {
                    $password = Hash::make($password);
                }
            } else {
                $password = null;
            }

            if ($user) {
                $payload = ['email' => $userData['email']];
                if ($password !== null) {
                    $payload['password'] = $password;
                }
                $user->update($payload);
            } else {
                $user = User::create([
                    'name'     => $this->record->employee_name ?? ($userData['email'] ?? 'User'),
                    'email'    => $userData['email'],
                    'password' => $password ?? Hash::make(str()->random(16)),
                ]);
                $this->record->user()->associate($user)->save();
            }
        }

        unset($data['user'], $data['roles_ids'], $data['roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        $employee = $this->record;


        $state = $this->form->getRawState();

        if (! $employee->user && ! empty($state['user'])) {
            $userData = $state['user'];

            $password = $userData['password'] ?? null;
            if (is_string($password) && $password !== '') {
                $info = password_get_info($password);
                if (($info['algo'] ?? 0) === 0) {
                    $password = Hash::make($password);
                }
            } else {
                $password = Hash::make(str()->random(16));
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
