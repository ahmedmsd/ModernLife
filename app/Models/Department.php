<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Department extends Model
{
    protected $table = 'departments';
    protected $primaryKey = 'dept_id';
    public $incrementing = true;
    protected $keyType = 'int';

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

    public function employees(): Department|\Illuminate\Database\Eloquent\Relations\HasMany
    {

        return $this->hasMany(Employee::class, 'department_id', 'dept_id');
    }

    public function managerUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id', 'id');
    }

    public function manager(): BelongsTo
    {
        return $this->managerUser();
    }

    public function getManagerUserIdAttribute(): ?int
    {
        return $this->managerUser?->id;
    }

}
