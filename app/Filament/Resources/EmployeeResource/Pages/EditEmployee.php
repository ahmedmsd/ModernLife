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
        // حمّل user مع roles لتسريع/ضمان التهيئة
        return static::getModel()::with('user.roles')->findOrFail($key);
    }

    public function mount($record = null): void
    {
        parent::mount($record);

        // املأ النموذج بالقيم الحالية + بيانات المستخدم + IDs الأدوار الحالية
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
        // إنشاء/تحديث User المرتبط
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

        // لا تحفظ هذه الحقول على جدول الموظف
        unset($data['user'], $data['roles_ids'], $data['roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        $employee = $this->record;

        // لو أُنشئ المستخدم للتو عبر mutateFormDataBeforeSave
        if (! $employee->user && ! empty($this->data['user'])) {
            $employee->user()->create($this->data['user']);
            $employee->refresh();
        }

        if ($employee->user) {
            // نقرأ قيمة الحقول الخام؛ لأن roles_ids غير مُجفَّف
            $state = $this->form->getRawState();

            // ندعم إما roles_ids (الموصى به) أو roles لو عندك حقل قديم بهذا الاسم
            $ids = $state['roles_ids'] ?? $state['roles'] ?? [];
            $ids = array_values(array_filter(array_map('intval', (array) $ids)));

            // تحقّق الأدوار لحارس المستخدم (web افتراضًا)
            $guard = $employee->user->guard_name ?? config('auth.defaults.guard', 'web');

            $validIds = Role::query()
                ->where('guard_name', $guard)
                ->whereIn('id', $ids)
                ->pluck('id')
                ->all();

            // مزامنة مباشرة على Pivot (model_has_roles) لتفادي خطأ "There is no role named 'X'"
            $employee->user->roles()->sync($validIds);

            // مسح كاش الصلاحيات
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    protected function getRedirectUrl(): string
    {
        return EmployeeResource::getUrl('index');
    }
}
