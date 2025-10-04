<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use App\Models\User;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $employee = $this->record;
        $userData = $data['user'] ?? null;

        if (! empty($userData)) {
            if (! $employee->user) {
                // إنشاء مستخدم جديد
                $plain = data_get($userData, 'password') ?? Str::random(12);
                $password = Str::startsWith($plain, ['$2y$', '$argon2']) ? $plain : Hash::make($plain);

                /** @var User $user */
                $user = User::create([
                    'name'     => $employee->employee_name ?? (data_get($userData, 'email') ?? 'User'),
                    'email'    => data_get($userData, 'email') ?? ($employee->email ?? null),
                    'password' => $password,
                ]);

                $employee->user()->associate($user)->save();
            } else {
                // تحديث المستخدم الحالي
                $payload = [];

                if (filled(data_get($userData, 'email'))) {
                    $payload['email'] = data_get($userData, 'email');
                }

                $plain = data_get($userData, 'password'); // قد لا تكون موجودة
                if (filled($plain)) {
                    $payload['password'] = Str::startsWith($plain, ['$2y$', '$argon2'])
                        ? $plain
                        : Hash::make($plain);
                }

                if (! empty($payload)) {
                    $employee->user->update($payload);
                }
            }
        }

        // لا نكتب user/roles إلى جدول employees
        unset($data['user'], $data['roles_ids'], $data['roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        $employee = $this->record;
        $state    = $this->form->getRawState();

        DB::transaction(function () use ($employee, $state) {
            // مزامنة الأدوار على User فقط
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

            // تنظيف أي بقايا لأدوار على موديل Employee (إن وُجدت)
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
