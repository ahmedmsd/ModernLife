<?php


namespace App\Support\Filament;

use Illuminate\Support\Str;

trait HasShieldAccess
{
    public static function permissionName(): string
    {
        $slug = Str::of(static::getSlug())->replace(['/', '-'], '_')->toString();
        return 'access_' . $slug;
    }

    public static function canAccess(): bool
    {
        $u = auth()->user();

        if ($u?->hasRole(config('filament-shield.super_admin.role_name', 'super-admin'))) {
            return true;
        }

        return $u?->can(static::permissionName()) ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
