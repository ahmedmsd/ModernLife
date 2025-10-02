<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\PermissionRegistrar;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected array $permissionsMap = [];

    public static function canAccess(array $parameters = []): bool
    {
        return auth()->user()?->can('create_role') ?? false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['guard_name'] = 'web';

        $this->permissionsMap = (array) ($data['permissions_map'] ?? []);
        unset($data['permissions_map']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncRolePermissionsFromCapturedMap();
    }

    protected function syncRolePermissionsFromCapturedMap(): void
    {
        $selectedIds = collect($this->permissionsMap)
            ->flatMap(fn($group) => is_array($group)
                ? collect($group)->flatMap(fn($ids) => (array) $ids)
                : (array) $group
            )
            ->filter()->unique()->values()->all();

        $names = Permission::whereIn('id', $selectedIds)->pluck('name')->all();

        $roleId = $this->getRecord()->getKey();
        $role   = SpatieRole::query()->findOrFail($roleId);

        $role->syncPermissions($names);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
