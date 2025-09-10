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
            Actions\DeleteAction::make(),
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
                    'password'          => null,
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

            if ($user) {
                $payload = ['email' => $userData['email']];
                if (!empty($userData['password'])) {
                    $payload['password'] = Hash::make($userData['password']);
                }
                $user->update($payload);
            } else {
                $user = User::create([
                    'name'     => $this->record->employee_name,
                    'email'    => $userData['email'],
                    'password' => Hash::make($userData['password']),
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
