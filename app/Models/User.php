<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
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

    protected $appends = ['all_permissions']; // Important for permission checks

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class, 'user_id');
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

    public function getAllPermissionsAttribute()
    {
        $permissions = $this->getDirectPermissions();

        foreach ($this->roles as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    public function roles(): BelongsToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    public function hasPermission(string $permissionName): bool
    {
        return $this->all_permissions->contains('name', $permissionName);
    }
}
