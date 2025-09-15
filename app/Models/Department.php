<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dept_id';

    protected $fillable = [
        'dept_name',
        'dept_code',
        'parent_dept_id',
        'manager_id',
        'dept_type',
        'location',
        'phone_extension',
        'email',
        'is_active',
        'color_code'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(DepartmentCategories::class, 'dept_type', 'category_id');
    }

    public function parentDepartment(): BelongsTo
    {
    return $this->belongsTo(Department::class, 'parent_dept_id', 'dept_id');
    }

    public $incrementing = true;
    protected $keyType = 'int';

    public function employees(): Department|\Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(Employee::class, 'department_id', 'dept_id');
    }


    public function managerEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function managerUser(): ?User
    {
        if ($this->relationLoaded('manager') && $this->manager) {
            return $this->manager;
        }
        if (isset($this->manager_id) && $this->manager_id) {
            return User::find($this->manager_id);
        }
        if ($this->relationLoaded('managerEmployee') && $this->managerEmployee?->user) {
            return $this->managerEmployee->user;
        }
        if (isset($this->manager_employee_id) && $this->manager_employee_id) {
            return optional(Employee::with('user')->find($this->manager_employee_id))->user;
        }
        return null;
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Employee::class, 'manager_id', 'employee_id');
    }
}
