<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

class Employee extends Authenticatable
{
    use HasRoles, Notifiable, SoftDeletes;

    /**
     * Filament / Spatie guard name
     */
    protected $guard_name = 'web';

    /**
     * Table & keys
     */
    protected $table = 'employees';
    protected $primaryKey = 'employee_id';
    protected $keyType = 'int';


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
        'notes',
        'password',
        'remember_token',
    ];


    protected $guarded = ['deleted_at'];

    /**
     * Casts
     */
    protected $casts = [
        'birth_date' => 'date',
        'hire_date'  => 'date',
        'is_active'  => 'boolean',
        'salary'     => 'decimal:2',
        'deleted_at' => 'datetime',
    ];

    /**
     * Hidden
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getDefaultGuardName(): string
    {
        return 'web';
    }

    // ---------------------------
    // Relationships
    // ---------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Department::class, 'department_id', 'dept_id');
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

    // ---------------------------
    // Accessors / Notifications
    // ---------------------------

    public function routeNotificationForMail($notification): ?string
    {
        return $this->user->email ?? $this->email ?? null;
    }

    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->mobile ?? $this->phone ?? null;
    }

    public function getNameAttribute(): ?string
    {
        return $this->attributes['employee_name'] ?? ($this->user->name ?? null);
    }

    // ---------------------------
    // Permission helpers
    // ---------------------------

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

    // ---------------------------
    // Model Events (Soft Delete aware)
    // ---------------------------

    protected static function booted(): void
    {

        static::deleting(function (Employee $employee) {
            // isForceDeleting متاحة بوجود SoftDeletes
            if (! $employee->isForceDeleting()) {
                // Soft delete: لا نحذف الـ User
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
                ->whereKeyNot($employee->getKey())
                ->exists();

            if (! $hasAnother) {
                if ($user = $employee->user()->first()) {
                    $user->delete();
                }
            }
        });
    }
}
