<?php


namespace App\Models;

use Spatie\Permission\Traits\HasRoles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable; // لكي يعمل كـ User في Laravel Auth
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Employee extends Authenticatable
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    use HasRoles, Notifiable;

    protected $guard_name = 'employee';

    protected $table = 'employees';

    protected $primaryKey = 'employee_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'hire_date' => 'date',
        'is_active' => 'boolean',
        'salary' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function userGroup()
    {
        return $this->belongsTo(UserGroup::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.permission'),
            'user_permission',
            'user_id',
            'permission_id'
        )->withTimestamps();
    }


    public function groups()
    {
        return $this->belongsToMany(
            UserGroup::class,
            'user_group_membership',
            'user_id',
            'group_id'
        )->orderBy('group_name');
    }

    public function getAllPermissions()
    {
        $permissions = $this->getDirectPermissions();

        foreach ($this->groups as $group) {
            $permissions = $permissions->merge($group->getAllPermissions());
        }

        foreach ($this->getRoles() as $role) {
            $permissions = $permissions->merge($role->permissions);
        }

        return $permissions->unique('id');
    }

    public function hasPermission($permission)
    {
        if ($this->hasDirectPermission($permission)) {
            return true;
        }

        foreach ($this->groups as $group) {
            if ($group->hasPermission($permission)) {
                return true;
            }
        }

        return $this->hasPermissionTo($permission);
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        // Add any sensitive fields here if needed
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}
