<?php

namespace App\Models;

use Filament\Panel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;

class User extends Authenticatable implements FilamentUser
{
    use HasRoles, HasFactory, Notifiable;
    protected string $guard_name = 'web';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(['super-admin','admin','sales','factory_manager','showroom_manager','installation_manager','department_manager','quality_manager','purchasing_manager']);

    }

    // Removed $appends = ['all_permissions'] to avoid N+1 queries
    // Use $user->getAllPermissions() or $user->can() instead

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(\App\Models\Employee::class, 'user_id', 'id');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'model_has_permissions',
            'model_id',
            'permission_id'
        )->withTimestamps();
    }

    /**
     * Check if user has a specific permission
     * Uses Spatie's built-in can() method which is properly cached
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->can($permissionName);
    }
}
