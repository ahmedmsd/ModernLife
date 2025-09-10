<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Authenticatable
{
    use HasRoles, Notifiable, softDeletes;

    protected $guard_name = 'web';

    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    protected $keyType = 'int'; // Explicitly define if employee_id is integer

    protected $fillable = [
        'national_id',
        'user_id',
        'employee_name',
        'gender',
        'birth_date',
        'email',
        'phone',
        'address',
        'department_id',
        'position',
        'hire_date',
        'salary',
        'employment_type',
        'is_active',
        'emergency_contact_name',
        'emergency_contact_phone',
        'notes'
    ];

    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
    public function getDefaultGuardName(): string
    {
        return 'web';
    }
    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id', 'dept_id');
    }

    protected static function booted(): void
    {
        static::deleting(function (Employee $employee) {
            if (method_exists($employee, 'isForceDeleting') && ! $employee->isForceDeleting()) {
                $employee->setAttribute('_skip_user_delete', true);
            }
        });

        static::deleted(function (Employee $employee) {
            if ($employee->getAttribute('_skip_user_delete') ?? false) {
                return;
            }

            if (! $employee->user_id) {
                return;
            }

            $hasAnother = self::query()
                ->where('user_id', $employee->user_id)
                ->exists();

            if (! $hasAnother) {
                $user = $employee->user()->first();
                if ($user) {

                    $user->delete();
                }
            }
        });
    }

    public function routeNotificationForMail($notification): ?string
    {
        return $this->user->email ?? null;
    }
    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->mobile ?? null;
    }
    public function getNameAttribute(): ?string
    {
        return $this->attributes['employee_name'] ?? $this->user->name ?? null;
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'user_permission',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }

    // Permission methods
    public function getAllPermissions()
    {
        $permissions = $this->getDirectPermissions();

        foreach ($this->getRoles() as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    public function hasPermission($permission): bool
    {
        if ($this->hasDirectPermission($permission)) {
            return true;
        }

        return $this->hasPermissionTo($permission);
    }
}
