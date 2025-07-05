<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function employee()
    {
        return $this->hasOne(Employee::class, 'user_id');
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_membership', 'user_id', 'group_id');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'user_permission', 'user_id', 'permission_id');
    }

    // Combine group + direct permissions
    public function getAllPermissionsAttribute()
    {
        $groupPermissions = $this->groups()->with('permissions')->get()
            ->pluck('permissions')->flatten();

        return $groupPermissions
            ->merge($this->directPermissions)
            ->unique('id')
            ->values();
    }

    // Check if user has a specific permission by name
    public function hasPermission(string $permissionName): bool
    {
        return $this->all_permissions->contains(fn($p) => $p->name === $permissionName);
    }
}
